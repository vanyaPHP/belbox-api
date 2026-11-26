<?php

use App\Enums\Identity\Permission;
use App\Http\Controllers\V1\HR\EmployeeController;
use App\Http\Controllers\V1\HR\EmployeeDocumentController;
use App\Http\Controllers\V1\HR\HiringRequestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('/hiring-requests')
        ->name('hiring-requests.')
        ->controller(HiringRequestController::class)->group(function () {
            Route::get('/', 'index')
                ->middleware(['has_permission:' . Permission::ReadHirings->value])
                ->name('index');
            Route::post('/{id}/approve', 'approve')
                ->middleware(['has_permission:' . Permission::ApproveHirings->value])
                ->name('approve');
            Route::post('/{id}/reject', 'reject')
                ->middleware(['has_permission:' . Permission::RejectHirings->value])
                ->name('reject');
        });

    Route::prefix('/employees')
        ->name('employees.')
        ->controller(EmployeeController::class)->group(function () {
            Route::get('/', 'index')
                ->middleware(['has_permission:' . Permission::ReadEmployees->value])
                ->name("index");

            Route::get('/{id}', 'show')
                ->middleware(['has_permission:' . Permission::ReadEmployees->value])
                ->name("show");

            Route::post('/', 'store')
                ->middleware(['has_permission:' . Permission::WriteEmployees->value])
                ->name("store");

            Route::put('/{id}', 'update')
                ->middleware(['has_permission:' . Permission::WriteEmployees->value])
                ->name("update");

            Route::post('/{id}', 'fire')
                ->middleware(['has_permission:' . Permission::WriteEmployees->value])
                ->name("fire");
        });

    Route::prefix('/employees/{id}/documents')
        ->name('employees.documents.')
        ->controller(EmployeeDocumentController::class)->group(function () {
            Route::get('/', 'index')
                ->middleware(['has_permission:' . Permission::ReadEmployees->value])
                ->name('index');
            Route::post('/', 'upload')
                ->middleware(['has_permission:' . Permission::WriteEmployees->value])
                ->name("upload");
            Route::get('/{documentId}', 'download')
                ->middleware(['has_permission:' . Permission::ReadEmployees->value])
                ->name("download");
            Route::delete('/{documentId}', 'destroy')
                ->middleware(['has_permission:' . Permission::WriteEmployees->value])
                ->name("destroy");;
        });
});
