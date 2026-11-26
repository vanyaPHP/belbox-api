<?php

declare(strict_types=1);

namespace Controllers\V1\HR;

use App\Enums\HR\DocumentType;
use App\Models\HR\Employee;
use App\Models\HR\EmployeeDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCaseWithAuthorizedHrManager;

class EmployeeDocumentControllerTest extends TestCaseWithAuthorizedHrManager
{
    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }


    public function test_it_shows_documents_for_employee_profile(): void
    {
        $employee = Employee::factory()
            ->withManager($this->currentHrManager)
            ->create();
        $fakeFile = UploadedFile::fake()->create('passport.pdf', 100, 'application/pdf');
        $path = $fakeFile->store("employee-documents/$employee->id", 'public');

        $fakeDocument = EmployeeDocument::factory()->create([
            'employee_id' => $employee->id,
            'path' => $path,
        ]);

        $response = $this->getJson(route('api.v1.hr.employees.documents.index', [
            'id' => $employee->id,
        ]));
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $fakeDocument->id,
            'document_type' => $fakeDocument->document_type,
            'path' => $path,
        ]);
    }

    public function test_it_uploads_document(): void
    {
        $employee = Employee::factory()
            ->withManager($this->currentHrManager)
            ->create();
        $fakeFile = UploadedFile::fake()->create('passport.pdf', 100, 'application/pdf');
        $documentType = DocumentType::Passport;

        $response = $this->postJson(route('api.v1.hr.employees.documents.upload', [
            'id' => $employee->id,
        ]), [
            'document_type' => $documentType,
            'file' => $fakeFile,
        ]);
        $response->assertStatus(201);
        $expectedPath = "employee-documents/$employee->id/{$fakeFile->hashName()}";
        Storage::disk('public')->assertExists($expectedPath);
        $this->assertDatabaseHas('employee_documents', [
            'employee_id' => $employee->id,
            'document_type' => $documentType,
            'path' => $expectedPath,
        ]);
    }

    public function test_it_downloads_document(): void
    {
        $employee = Employee::factory()
            ->withManager($this->currentHrManager)
            ->create();
        $fakeFile = UploadedFile::fake()->create('passport.pdf', 100, 'application/pdf');
        $path = $fakeFile->store("employee-documents/$employee->id", 'public');

        $fakeDocument = EmployeeDocument::factory()->create([
            'employee_id' => $employee->id,
            'path' => $path,
        ]);

        $response = $this->get(
            route('api.v1.hr.employees.documents.download', [
                'id' => $employee->id,
                'documentId' => $fakeDocument->id,
            ])
        );

        $response->assertOk();
        $response->assertDownload($fakeFile->hashName());
    }

    public function test_it_deletes_document(): void
    {
        $employee = Employee::factory()
            ->withManager($this->currentHrManager)
            ->create();
        $fakeFile = UploadedFile::fake()->create('passport.pdf', 100, 'application/pdf');
        $path = $fakeFile->store("employee-documents/$employee->id", 'public');

        $fakeDocument = EmployeeDocument::factory()->create([
            'employee_id' => $employee->id,
            'path' => $path,
        ]);

        $response = $this->deleteJson(
            route('api.v1.hr.employees.documents.destroy', [
                'id' => $employee->id,
                'documentId' => $fakeDocument->id,
            ])
        );

        $response->assertNoContent();

        Storage::disk('public')->assertMissing($path);
        $this->assertDatabaseMissing('employee_documents', [
            'id' => $fakeDocument->id,
            'deleted_at' => null,
        ]);
    }
}
