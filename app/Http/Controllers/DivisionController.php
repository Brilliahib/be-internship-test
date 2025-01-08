<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $name = $request->query('name', '');

        $divisions = Division::when($name, function ($query, $name) {
            $query->where('name', 'like', '%' . $name . '%');
        })->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Data divisions retrieved successfully',
            'data' => [
                'divisions' => $divisions->items(), 
            ],
            'pagination' => [
                'current_page' => $divisions->currentPage(),
                'per_page' => $divisions->perPage(),
                'total' => $divisions->total(),
                'last_page' => $divisions->lastPage(),
            ],
        ]);
    }
}
