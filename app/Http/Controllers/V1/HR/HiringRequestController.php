<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\HR;

use App\Enums\HR\EmployeeStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\HR\EmployeeResource;
use App\Models\HR\Employee;
use Illuminate\Http\JsonResponse;

final class HiringRequestController extends Controller
{
    public function index(): JsonResponse
    {
        $hiringRequests = Employee::query()
            ->where('status', EmployeeStatus::Pending)
            ->with(['user'])
            ->paginate(15);

        return response()->json(EmployeeResource::collection($hiringRequests));
    }

    public function approve(string $id): JsonResponse
    {
        $hiringRequest = Employee::query()->find($id);
        if (! $hiringRequest) {
            return response()->json([
                'error' => 'Employee not found.',
            ], 404);
        }

        if ($hiringRequest->start_date->isToday()) {
            $status = EmployeeStatus::Active;
        } else {
            $status = EmployeeStatus::Hired;
        }

        $hiringRequest->update(['status' => $status]);

        return response()->json([
            'message' => 'Employee hired successfully.',
        ]);
    }

    public function reject(string $id): JsonResponse
    {
        $hiringRequest = Employee::query()->find($id);
        if (! $hiringRequest) {
            return response()->json([
                'error' => 'Employee not found.',
            ], 404);
        }

        $hiringRequest->update(['status' => EmployeeStatus::Rejected]);

        return response()->json([
            'message' => 'Employee rejected successfully.',
        ]);
    }
}
