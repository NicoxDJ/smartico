<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CourseStudentApiTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	public function it_can_create_a_student()
	{
		$payload = [
			'name' => 'Ivan Ivanov',
			'email' => 'ivan@example.com',
		];

		$response = $this->postJson('/api/students', $payload);

		$response->assertStatus(201)
				->assertJson([
					'status' => 'success',
					'data' => [
						'name' => 'Ivan Ivanov',
						'email' => 'ivan@example.com',
					],
				]);

		$this->assertDatabaseHas('students', ['email' => 'ivan@example.com']);
	}
	
	/** @test */
	public function it_cannot_duplicate_a_student()
	{
		$student = Student::create(['name' => 'Ivan Ivanov', 'email' => 'ivan@example.com']);
		
		$payload = [
			'name' => 'Petar Ivanov',
			'email' => 'ivan@example.com',
		];
	
		$response = $this->postJson('/api/students', $payload);
	
		$response->assertStatus(422)
				->assertJson([
					'status' => 'error',
					'errors' => [
						'email' => ['The email has already been taken.'],
					],
				]);
	}

	/** @test */
	public function it_can_create_a_course()
	{
		$payload = [
			'title' => 'Software Development',
		];

		$response = $this->postJson('/api/courses', $payload);

		$response->assertStatus(201)
				->assertJson([
					'status' => 'success',
					'data' => [
						'title' => 'Software Development',
					],
				]);

		$this->assertDatabaseHas('courses', ['title' => 'Software Development']);
	}
	
	/** @test */
	public function it_cannot_duplicate_a_course()
	{
		$course = Course::create(['title' => 'Software Development']);
		
		$payload = [
			'title' => 'Software Development',
		];

		$response = $this->postJson('/api/courses', $payload);

		$response->assertStatus(422)
		 		->assertJson([
				 	'status' => 'error',
					'errors' => [
						'title' => ['The desired course title has already been taken.'],
					],
				]);
	}

	/** @test */
	public function it_can_assign_student_to_course_only_once()
	{
		$course = Course::create(['title' => 'Software Development']);
		$student = Student::create(['name' => 'Maria', 'email' => 'maria@example.com']);

		// Първи път
		$response1 = $this->postJson("/api/courses/{$course->id}/assign", [
			'student_id' => $student->id,
		]);

		$response1->assertStatus(201)
				->assertJson([
					'status' => 'success',
					'data' => [
						'course' => 'Software Development',
						'student' => 'Maria',
					],
				]);

		$this->assertDatabaseHas('course_student', [
			'course_id' => $course->id,
			'student_id' => $student->id,
		]);

		$pivot1 = $course->students()->where('students.id', $student->id)->first();

		$this->assertNotNull($pivot1->created_at);
		$this->assertNotNull($pivot1->updated_at);

		// Опит за добавяне втори път
		$response2 = $this->postJson("/api/courses/{$course->id}/assign", [
			'student_id' => $student->id,
		]);

		$response2->assertStatus(409)
				->assertJson([
					'status' => 'error',
					'message' => 'Student is already assigned to this course.',
				]);

		$this->assertCount(1, $course->students()->where('students.id', $student->id)->get());
	}

	/** @test */
	public function it_returns_students_in_course_with_pivot_id()
	{
		$course = Course::create(['title' => 'Hardware Basics']);
		$student1 = Student::create(['name' => 'Ivan', 'email' => 'ivan@example.com']);
		$student2 = Student::create(['name' => 'Stoyan', 'email' => 'stoyan@example.com']);

		$course->students()->attach([$student1->id, $student2->id]);

		$response = $this->getJson("/api/courses/{$course->id}");

		$response->assertStatus(200)
				->assertJson([
					'status' => 'success',
				])
				->assertJsonCount(2, 'data.students');

		// Проверка за pivot id и timestamps
		$students = $response->json('data.students');
		foreach ($students as $s) {
			$this->assertArrayHasKey('created_at', $s);
			$this->assertArrayHasKey('updated_at', $s);
			$this->assertArrayHasKey('id', $s['pivot']);
		}
	}

	/** @test */
	public function it_returns_404_if_course_not_found()
	{
		$response = $this->postJson('/api/courses/999/assign', [
			'student_id' => 1,
		]);

		$response->assertStatus(404)
				->assertJson([
					'status' => 'error',
					'message' => 'Course not found',
				]);
	}

	/** @test */
	public function it_returns_422_for_invalid_student_id()
	{
		$course = Course::create(['title' => 'Chemistry']);

		$response = $this->postJson("/api/courses/{$course->id}/assign", [
			'student_id' => 999,
		]);

		$response->assertStatus(422)
				->assertJson([
					'status' => 'error',
					'message' => 'The given data was invalid.',
				]);
	}
}

?>
