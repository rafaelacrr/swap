<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Judite\Models\User;
use App\Judite\Models\Course;
use App\Judite\Models\Student;
use App\Judite\Models\Enrollment;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class EnrollmentTest extends TestCase
{
    use DatabaseTransactions;

    public function testStudentEnrollCourse()
    {
        // Prepare
        $student = factory(Student::class)->create();
        $course = factory(Course::class)->create();

        // Execute
        $actualReturn = $student->enroll($course);

        // Assert
        $this->assertEquals(Enrollment::class, get_class($actualReturn));
        $this->assertEquals(1, Enrollment::count());
        $enrollment = Enrollment::first();
        $this->assertEquals($student->id, $enrollment->student_id);
        $this->assertEquals($course->id, $enrollment->course_id);
    }

    /**
     * @expectedException App\Exceptions\UserIsAlreadyEnrolledInCourseException
     */
    public function testThrowsExceptionWhenStudentIsAlreadyEnrolledInCourse()
    {
        // Prepare
        $student = factory(Student::class)->create();
        $course = factory(Course::class)->create();
        factory(Enrollment::class)->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);

        // Execute
        $student->enroll($course);
    }

    public function testUserIsAdmin()
    {
        // Prepare
        $user = factory(User::class)->states('admin')->create();

        // Assert
        $this->assertTrue($user->isAdmin());
    }

    public function testUserIsNotAdmin()
    {
        // Prepare
        $student = factory(Student::class)->create();

        // Assert
        $this->assertFalse($student->user->isAdmin());
    }
}
