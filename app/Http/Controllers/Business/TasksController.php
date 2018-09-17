<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTaskRequest;
use App\Task;
use App\Responses\SuccessResponse;
use App\Responses\ErrorResponse;
use Carbon\Carbon;

class TasksController extends Controller
{
    /**
     * Display a list of Tasks.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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

        if ($task = Task::create($data)) {
            return new SuccessResponse('Task has been created.', $task);
        }

        return new ErrorResponse(500, 'An unexpected error occurred while trying to create a Task.  Please try again.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    public function destroy($id)
    {
        //
    }
}
