<?php

use App\Http\Controllers\AjaxRequestController;
use App\Models\Task;
use App\Models\Score;
use App\Models\Teacher;
use App\Models\ClassRoom;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassRoomController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClassRoomTaskController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [AuthController::class, 'login'])->name('login');
// page accessible for guest
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'processLogin']);
});

// page accessible for admin
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/dashboard-admin', [DashboardController::class, 'admin']);
    route::resource('/teachers', TeacherController::class);
    route::resource('/students', StudentController::class);
    route::resource('/schedule', ClassRoomController::class);
    route::get('/students/major/{major}', [StudentController::class, 'studentByMajor']); // get classroom by jurusan
    route::resource('/subjects', SubjectController::class);
    Route::view('undefined-fitur', 'admin.blank');
    route::view('/admin-profile', 'admin.my-profile');

    // route::view('//uploadClassRoomImage', );

    // import
    route::post('/teacher-import', [TeacherController::class, 'import'])->name('teacher.import');
    route::post('/student-import', [StudentController::class, 'import'])->name('student.import');
});

// page accessible for teacher
Route::middleware(['auth:teacher'])->group(function () {
    Route::get('/dashboard-teacher', [DashboardController::class, 'teacher']);
    Route::resource('score', ScoreController::class);
    Route::get('/score/class_room/{id}', [ScoreController::class, 'classRoomScore']);
    Route::get('/score/{id}/create-one', [ScoreController::class, 'createOne']);
    Route::get('/score-choose-edit', [ScoreController::class, 'chooseEdit']);
    Route::get('/score-choose-one/{id}', [ScoreController::class, 'editOne']);
    Route::resource('task', TaskController::class);
    Route::view('undefined-fitur', 'teacher.blank');

    route::view('/teacher-schedule', 'teacher.schedule.index');
    route::view('/teacher-profile', 'teacher.my-profile');

    // route for ajax requests
    Route::get('getClassRoom', [ScoreController::class, 'getClassRoom'])->name('getClassRoom');
    Route::get('getTasks', [ScoreController::class, 'getTasks'])->name('getTasks');
    Route::get('getStudents', [ScoreController::class, 'getStudents'])->name('getStudents');

    // new ajax request with select 2
    Route::get('selectClassRoom', [AjaxRequestController::class, 'classRoom'])->name('classRoom.ajaxrequest');
    Route::get('selectTask/{id}', [AjaxRequestController::class, 'task'])->name('task.ajaxrequest');
});


// page accessible for student
Route::middleware(['auth:student'])->group(function () {
    Route::get('/dashboard-student', [DashboardController::class, 'student']);

    Route::get('/student-task', function () {
        $task = Task::with('teacher.subject')->where('class_room_id', Auth::user()->class_room_id)
            ->orderBy('teacher_id')->paginate(10);

        // topboxes
        $scoreAvg = Score::where('student_id', Auth::user()->id)->avg('score'); //getting average score of this student
        $taskCount = Task::where('class_room_id', Auth::user()->class_room_id)->count();


        // $tasks = Task::with('teacher.subject')->where('class_room_id', $student->class_room_id)->get(); //getting tasks data of this student
        return view('student.task.index', ['tasks' => $task, 'taskCount' => $taskCount, 'scoreAvg' => $scoreAvg]);
    });

    Route::get('/student-score', function () {
        // dd(Auth::user()->class_room_id);
        $score = Score::with('task.teacher.subject')->where('student_id', Auth::user()->id)->orderBy('task_id')->paginate(10);
        // $scores = Score::with('task.teacher.subject')->where('student_id', $student_id)->get(); //getting score data of this student

        // topboxes
        $scoreAvg = Score::where('student_id', Auth::user()->id)->avg('score'); //getting average score of this student
        $taskCount = Task::where('class_room_id', Auth::user()->class_room_id)->count();

        return view('student.score.index', ['scores' => $score, 'taskCount' => $taskCount, 'scoreAvg' => $scoreAvg]);
    });

    route::view('/student-schedule', 'student.schedule.index');
    route::view('/student-profile', 'student.my-profile');
});

// page accessible for all
Route::get('/home', function () {
    return 'page for all';
})->middleware('auth');


// logout
Route::get('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
