<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Student;
use Illuminate\Http\Request;

class CourseController extends Controller
{
	public function store(Request $request)
	{
		$validated = $request->validate([
			'title' => 'required|string|max:100|unique:courses,title',
		],
		[
			'title.unique' => 'The desired course title has already been taken.',
		]);

		$course = Course::create($validated);

		return response()->json([
			'status' => 'success',
			'message' => 'Course created successfully',
			'data' => $course->only(['id', 'title', 'created_at']),
		], 201);
	}

	public function assign(Request $request, $course_id)
	{
		$course = Course::find($course_id);
		if (!$course) {
			return response()->json([
				'status' => 'error',
				'message' => 'Course not found',
			], 404);
		}
		
		$validated = $request->validate([
			'student_id' => 'required|exists:students,id',
		],
		[
			'student_id.exists' => 'The selected student is not existing.',
		]);
		
		$alreadyAssigned = $course->students()->where('students.id', $validated['student_id'])->exists();
		if ($alreadyAssigned) {
			return response()->json([
				'status' => 'error',
				'message' => 'Student is already assigned to this course.',
			], 409);
		}

		$course->students()->attach($validated['student_id']);
		
		$student = Student::find($validated['student_id']);

		return response()->json([
			'status' => 'success',
			'message' => 'Student added to course successfully',
			'data' => [
				'course' => $course->title,
				'student' => $student->name,
			],
		], 201);
	}

	public function show($course_id)
	{
		$course = Course::find($course_id);
		if (!$course) {
			return response()->json([
				'status' => 'error',
				'message' => 'Course not found',
			], 404);
		}
		
		$course->load('students');
		return response()->json(['status'=>'success', 'data' => $course]);
	}
}

?>