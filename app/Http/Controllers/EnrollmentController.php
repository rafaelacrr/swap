<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Illuminate\Http\Request;
use App\Judite\Models\Course;
use App\Exceptions\UserIsAlreadyEnrolledInCourseException;

class EnrollmentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('can.enroll');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $course = DB::transaction(function () use ($request) {
                $this->validate($request, [
                    'course_id' => 'exists:courses,id',
                ]);

                $student = Auth::user()->student;
                $course = Course::find($request->input('course_id'));
                $enrollment = $student->enroll($course);

                return $course;
            });

            flash("You have successfully enrolled in {$course->name}.")->success();
        } catch (UserIsAlreadyEnrolledInCourseException $e) {
            $course = $e->getCourse();
            flash("You are already enrolled in {$course->name}.")->error();
        }

        return redirect()->route('courses.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $course = DB::transaction(function () use ($request) {
            $this->validate($request, [
                'course_id' => 'exists:courses,id',
            ]);

            $student = Auth::user()->student;
            $course = Course::find($request->input('course_id'));
            $student->removeEnrollmentInCourse($course);

            return $course;
        });

        flash("You have successfully deleted the enrollment in {$course->name}.")->success();

        return redirect()->route('courses.index');
    }
}
