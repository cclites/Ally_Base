<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTaskRequest;
use App\Task;
use App\Responses\SuccessResponse;
use App\Responses\ErrorResponse;
use Carbon\Carbon;
use App\OfficeUser;

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
            $tasks = activeBusiness()->tasks()
                ->createdBy(request()->created ? auth()->user()->id : null)
                ->assignedTo(request()->assigned ? auth()->user()->id : null);

            if (request()->pending == 1) {
                $tasks->whereNull('completed_at');
            } elseif (request()->overdue == 1) {
                $tasks->where('due_date', '<', Carbon::now()->toDateTimeString())
                    ->whereNull('completed_at');
            }

            return response()->json($tasks->latest()->get());
        }

        $users = activeBusiness()->officeUserList(false, true);

        return view('business.tasks', compact('users'));
    }

    /**
     * Create a new task
     *
     * @param CreateTaskRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateTaskRequest $request)
    {
        $data = $request->validated();
        $data['due_date'] = isset($data['due_date']) ? Carbon::parse($data['due_date']) : null;

        if ($task = activeBusiness()->tasks()->create($data)) {
            return new SuccessResponse('Task has been created.', $task->fresh());
        }

        return new ErrorResponse(500, 'An unexpected error occurred while trying to create a Task.  Please try again.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        if ($task->business_id != activeBusiness()->id) {
            return new ErrorResponse(403, 'You do not have access to this task.');
        }
        
        if (request()->wantsJson()) {
            return response()->json($task);
        }
    }

    /**
     * Update the given Task.
     *
     * @param CreateTaskRequest $request
     * @param Task $task
     * @return \Illuminate\Http\Response
     */
    public function update(CreateTaskRequest $request, Task $task)
    {
        if ($task->business_id != activeBusiness()->id) {
            return new ErrorResponse(403, 'You do not have access to this task.');
        }
        
        $data = $request->validated();
        $data['due_date'] = isset($data['due_date']) ? Carbon::parse($data['due_date']) : null;

        if ($task->update($data)) {
            return new SuccessResponse('Task has been updated.', $task->fresh());
        }

        return new ErrorResponse(500, 'An unexpected error occurred while trying to update the Task.  Please try again.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        if ($task->business_id != activeBusiness()->id) {
            return new ErrorResponse(403, 'You do not have access to this task.');
        }
        
        if ($task->delete()) {
            return new SuccessResponse('Task has been deleted.');
        }

        return new ErrorResponse(500, 'An unexpected error occurred while trying to delete the Task.  Please try again.');
    }
}
