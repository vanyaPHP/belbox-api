<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\HR;

use App\Enums\HR\EmployeeStatus;
use App\Enums\Identity\Role;
use App\Http\Controllers\Controller;
use App\Http\Policies\EmployeePolicy;
use App\Http\Requests\HR\CreateEmployeeData;
use App\Http\Requests\HR\UpdateEmployeeData;
use App\Http\Resources\HR\EmployeeResource;
use App\Models\HR\Employee;
use App\Services\HR\EmployeeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class EmployeeController extends Controller
{
    public function __construct(private readonly EmployeeService $employeeService) {}

    public function index(): JsonResponse
    {
        $currentUser = auth()->user()->load(['employee']);
        if ($currentUser->hasRoles([Role::SuperAdmin->value, Role::HrHead->value])) {
            $query = Employee::query()
                ->with(['user.roles'])
                ->whereNot('user_id', $currentUser->id)
                ->where(function (Builder $query) use ($currentUser) {
                    $query->where('status', EmployeeStatus::Hired)
                        ->orWhere('status', EmployeeStatus::Active);
                });
        } else if ($currentUser->hasRole(Role::HrManager->value)) {
            $query = Employee::query()
                ->with(['user.roles'])
                ->where(function (Builder $query) use ($currentUser) {
                    $query->where('status', EmployeeStatus::Hired)
                        ->orWhere('status', EmployeeStatus::Active);
                })
                ->where('manager_id', $currentUser->employee->id);
        } else {
            $query = $currentUser->employee
                ->getDownlineQuery()
                ->with(['user.roles']);
        }

        return response()->json(EmployeeResource::collection($query->paginate(15)));
    }

    public function show(string $id): JsonResponse
    {
        $employee = Employee::query()
            ->with(['user.roles', 'head.user', 'manager.user', 'documents'])
            ->find($id);
        if (! $employee) {
            return response()->json([
                'error' => 'Employee not found.'
            ], 404);
        }

        if (EmployeePolicy::canView($employee)) {
            return response()->json(new EmployeeResource($employee));
        } else {
            return response()->json([
                'error' => 'You are not allowed to view this employee.'
            ], 403);
        }
    }

    public function store(CreateEmployeeData $data): JsonResponse
    {
        if (! EmployeePolicy::canWrite()) {
            return response()->json([
                'error' => 'You are not allowed to create employees.'
            ], 403);
        }

        $employee = $this->employeeService->store($data);
        if (! $employee) {
            return response()->json([
                'error' => 'Something went wrong. Try again.',
            ], 500);
        }

        return response()->json(new EmployeeResource($employee->load('user.roles')), 201);
    }

    public function update(string $id, UpdateEmployeeData $data): JsonResponse|Response
    {
        $employee = Employee::query()->with(['user.roles'])->find($id);
        if (! $employee) {
            return response()->json([
                'error' => 'Employee not found.'
            ]);
        }

        if (! EmployeePolicy::canWrite($employee)) {
            return response()->json([
                'error' => 'You are not allowed to update this employee.',
            ], 403);
        }

        $result = $this->employeeService->update($employee, $data);
        if (! $result) {
            return response()->json([
                'error' => 'Something went wrong. Try again.',
            ], 500);
        }

        return response()->noContent();
    }

    public function fire(string $id): JsonResponse|Response
    {
        $employee = Employee::query()->with(['user'])->find($id);
        if (! $employee) {
            return response()->json([
                'error' => 'Employee not found.'
            ]);
        }

        if (! EmployeePolicy::canWrite($employee)) {
            return response()->json([
                'error' => 'You are not allowed to fire this employee.',
            ], 403);
        }

        $result = $this->employeeService->fire($employee);
        if (! $result) {
            return response()->json([
                'error' => 'Something went wrong. Try again.',
            ], 500);
        }

        return response()->noContent();
    }
}
