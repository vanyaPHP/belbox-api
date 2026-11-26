<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\HR;

use App\Enums\HR\DocumentType;
use App\Http\Controllers\Controller;
use App\Http\Policies\EmployeePolicy;
use App\Http\Requests\HR\UploadDocumentData;
use App\Http\Resources\HR\EmployeeDocumentResource;
use App\Models\HR\Employee;
use App\Models\HR\EmployeeDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeDocumentController extends Controller
{
    public function index(string $id): JsonResponse
    {
        $employee = Employee::query()->with(['documents'])->find($id);
        if (! $employee) {
            return response()->json([
                'error' => 'Employee not found.',
            ], 404);
        }

        if (! EmployeePolicy::canView($employee)) {
            return response()->json([
                'error' => 'You are not allowed to view this employee.',
            ], 403);
        }

        return response()->json(EmployeeDocumentResource::collection($employee->documents));
    }

    public function upload(string $id, UploadDocumentData $data): JsonResponse
    {
        $employee = Employee::query()->with(['documents'])->find($id);
        if (! $employee) {
            return response()->json([
                'error' => 'Employee not found.',
            ], 404);
        }

        if (! EmployeePolicy::canWrite($employee)) {
            return response()->json([
                'error' => 'You are not allowed to upload documents for this employee.',
            ], 403);
        }

        $file = $data->file('file');
        $path = $file->store("employee-documents/$id", 'public');

        $document = $employee->documents()->create([
            'document_type' => DocumentType::from($data->input('document_type')),
            'path' => $path,
        ]);

        return response()->json(new EmployeeDocumentResource($document), 201);
    }

    public function download(string $id, string $documentId): StreamedResponse|JsonResponse
    {
        $employee = Employee::query()->with(['documents'])->find($id);
        if (! $employee) {
            return response()->json([
                'error' => 'Employee not found.',
            ], 404);
        }

        if (! EmployeePolicy::canView($employee)) {
            return response()->json([
                'error' => 'You are not allowed to download documents for this employee.',
            ], 403);
        }

        /** @var EmployeeDocument $document */
        $document = $employee->documents()->find($documentId);
        if (! $document) {
            return response()->json([
                'error' => 'Document not found.',
            ], 404);
        }

        return Storage::disk('public')->download($document->path);
    }

    public function destroy(string $id, string $documentId): Response|JsonResponse
    {
        $employee = Employee::query()->with(['documents'])->find($id);
        if (! $employee) {
            return response()->json([
                'error' => 'Employee not found.',
            ], 404);
        }

        if (! EmployeePolicy::canWrite($employee)) {
            return response()->json([
                'error' => 'You are not allowed to destroy documents for this employee.',
            ], 403);
        }

        /** @var EmployeeDocument|null $document */
        $document = $employee->documents()->find($documentId);
        if (! $document) {
            return response()->json([
                'error' => 'Document not found.',
            ], 404);
        }

        Storage::disk('public')->delete($document->path);
        $document->delete();

        return response()->noContent();
    }
}
