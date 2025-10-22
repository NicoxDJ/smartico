<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
	public function store(Request $request)
	{
		$validated = $request->validate([
			'name'  => 'required|string|max:100',
			'email' => 'required|email|unique:students,email',
		]);

		$student = Student::create($validated);

		return response()->json([
			'status' => 'success',
			'message' => 'Student created successfully',
			'data' => $student->only(['id', 'name', 'email', 'created_at']),
		], 201);
		
	}
	
	public function show($student_id)
	{
		$student = Student::find($student_id);
		if (!$student) {
			return response()->json([
				'status' => 'error',
				'message' => 'Student not found',
			], 404);
		}
		
		$student->load('courses');
		return response()->json(['status'=>'success', 'data' => $student]);
	}
}

?>