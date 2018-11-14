<?php

namespace App\Http\Controllers\Caregivers;

use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Task;

class TasksController extends Controller
{
    /**
     * Display a list of Tasks.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (request()->wantsJson()) {
            $tasks = auth()->user()->tasks();

            if (request()->pending == 1) {
                $tasks->whereNull('completed_at');
            } elseif (request()->overdue == 1) {
                $tasks->where('due_date', '<', Carbon::now()->toDateTimeString())
                    ->whereNull('completed_at');
            } elseif (request()->complete == 1) {
                $tasks->whereNotNull('completed_at');
            }

            return response()->json($tasks->latest()->get());
        }

        return view('caregivers.tasks');
    }

    /**
     * Display the specified resource.
     *
     * @param  Task  $task
     * @return \Illuminate\Http\Response|ErrorResponse
     */
    public function show(Task $task)
    {
        if ($task->assigned_user_id != auth()->id()) {
            return new ErrorResponse(403, 'You do not have access to this task.');
        }

        if (request()->wantsJson()) {
            return response()->json($task);
        }
    }

    /**
     * Update the given Task to completed.
     *
     * @param Task $task
     * @return SuccessResponse|ErrorResponse
     */
    public function update(Task $task)
    {
        if (request()->has('complete')) {
            if (request()->complete) {
                $task->update(['completed_at' => Carbon::now()]);
            } else {
                $task->update(['completed_at' => null]);
            }

            return new SuccessResponse('Task has been updated.', $task->fresh());
        }

        return new ErrorResponse(500, 'An unexpected error occurred while trying to update the Task.  Please try again.');
    }
}
