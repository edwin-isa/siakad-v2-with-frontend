<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Builder;

class StudentPageController extends Controller
{
    // menampilkan halaman tugas murid ini
    function studentTask()
    {
        $task = Task::with('teacher.subject')->where('class_room_id', Auth::user()->class_room_id)
            ->orderBy('teacher_id')->paginate(10);

        // topboxes
        $scoreAvg = Score::where('student_id', Auth::user()->id)->avg('score'); //getting average score of this student
        $taskCount = Task::where('class_room_id', Auth::user()->class_room_id)->count();

        return view('student.task.index', ['tasks' => $task, 'taskCount' => $taskCount, 'scoreAvg' => $scoreAvg]);
    }


    // menampilkan halaman nilai murid ini
    function studentScore()
    {

        $score = Score::with('task.teacher.subject')->where('student_id', Auth::user()->id)->orderBy(function (Builder $q) {
            $q->select('teacher_id')->from('tasks')->whereColumn('task_id', 'tasks.id');
        })->paginate(10);

        // topboxes
        $scoreAvg = Score::where('student_id', Auth::user()->id)->avg('score'); //getting average score of this student
        $taskCount = Task::where('class_room_id', Auth::user()->class_room_id)->count();

        return view('student.score.index', ['scores' => $score, 'taskCount' => $taskCount, 'scoreAvg' => $scoreAvg]);
    }
}