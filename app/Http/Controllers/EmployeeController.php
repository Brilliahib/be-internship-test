<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $name = $request->query('name', '');
        $divisionId = $request->query('division_id', '');

        $employees = Employee::with('division')
            ->when($name, function ($query, $name) {
                $query->where('name', 'like', '%' . $name . '%');
            })
            ->when($divisionId, function ($query, $divisionId) {
                $query->where('division_id', $divisionId);
            })
            ->paginate(10);

        $formattedEmployees = $employees->map(function ($employee) {
            return [
                'id' => $employee->id,
                'image' => $employee->image ?? '',
                'name' => $employee->name,
                'phone' => $employee->phone,
                'division' => [
                    'id' => $employee->division->id ?? null,
                    'name' => $employee->division->name ?? null,
                ],
                'position' => $employee->position,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Data employees retrieved successfully',
            'data' => [
                'employees' => $formattedEmployees,
            ],
            'pagination' => [
                'current_page' => $employees->currentPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
                'last_page' => $employees->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => 'required|file|mimes:jpg,jpeg,png|max:2048',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|unique:employees,phone',
            'division' => 'required|exists:divisions,id',
            'position' => 'required|string|max:255',
        ]);

        $imagePath = $request->file('image')->store('employees', 'public');

        $employee = Employee::create([
            'image' => Storage::url($imagePath),
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'division_id' => $validated['division'],
            'position' => $validated['position'],
        ]);

        return response()->json(
            [
                'status' => 'success',
                'message' => 'Employee created successfully',
                'data' => [
                    'employee' => [
                        'id' => $employee->id,
                        'image' => $employee->image,
                        'name' => $employee->name,
                        'phone' => $employee->phone,
                        'division' => [
                            'id' => $employee->division->id,
                            'name' => $employee->division->name,
                        ],
                        'position' => $employee->position,
                    ],
                ],
            ],
            201,
        );
    }

    public function update(Request $request, $id): JsonResponse
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Employee not found',
                ],
                404,
            );
        }

        $validationRules = [
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:15|unique:employees,phone,' . $id,
            'division' => 'nullable|exists:divisions,id',
            'position' => 'nullable|string|max:255',
        ];

        $validated = $request->validate($validationRules);

        Log::info('Validated data:', $validated);

        if ($request->has('name')) {
            $employee->name = $validated['name'];
        }
        if ($request->has('phone')) {
            $employee->phone = $validated['phone'];
        }
        if ($request->has('division')) {
            $employee->division_id = $validated['division'];
        }
        if ($request->has('position')) {
            $employee->position = $validated['position'];
        }

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('employees', 'public');
            $employee->image = Storage::url($imagePath);
        }

        $employee->save();

        Log::info('Updated employee:', ['employee' => $employee]);

        return response()->json([
            'status' => 'success',
            'message' => 'Employee updated successfully',
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Employee not found',
                ],
                404,
            );
        }

        $employee->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Employee deleted successfully',
        ]);
    }
}
