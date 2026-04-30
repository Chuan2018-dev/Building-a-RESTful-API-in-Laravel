<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Student::latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:students,email'],
            'course' => ['required', 'string', 'max:255'],
        ]);

        $student = Student::create($validated);

        return response()->json($student, 201);
    }

    public function show(int $id): JsonResponse
    {
        $student = Student::findOrFail($id);

        return response()->json($student);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('students', 'email')->ignore($student->id)],
            'course' => ['sometimes', 'required', 'string', 'max:255'],
        ]);

        $student->update($validated);

        return response()->json($student);
    }

    public function destroy(int $id): JsonResponse
    {
        $student = Student::findOrFail($id);

        $student->delete();

        return response()->json([
            'message' => 'Deleted successfully',
        ]);
    }
}
