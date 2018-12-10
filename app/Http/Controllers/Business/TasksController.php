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
        if (request()->wantsJson()) {
            $query = Task::forRequestedBusinesses()->ordered()
                ->createdBy(request()->created ? auth()->user()->id : null)
                ->assignedTo(request()->assigned ? auth()->user()->id : null);

            if (request()->pending == 1) {
                $query->whereNull('completed_at');
            } elseif (request()->overdue == 1) {
                $query->where('due_date', '<', Carbon::now()->toDateTimeString())
                    ->whereNull('completed_at');
            } elseif (request()->complete == 1) {
                $query->whereNotNull('completed_at');
            }

            $tasks = $query->get();

            return $tasks;
        }

        $users = activeBusiness()->officeUserList(true, true);
        $caregivers = activeBusiness()->caregiverList(true, true);
        return view('business.tasks', compact('users', 'caregivers'));
    }

    /**
     * Create a new task
     *
     * @param CreateTaskRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateTaskRequest $request)
    {
        $data = $request->filtered();
        $this->authorize('create', [Task::class, $data]);

        if ($task = Task::create($data)) {
            return new SuccessResponse('Task has been created.', $task->fresh());
        }

        return new ErrorResponse(500, 'An unexpected error occurred while trying to create a Task.  Please try again.');
    }

    /**
     * Display the specified resource.
     *
     * @param Task $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        $this->authorize('read', $task);

        return response()->json($task);
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
        $this->authorize('update', $task);
        $data = $request->filtered();

        if ($task->update($data)) {
            return new SuccessResponse('Task has been updated.', $task->fresh());
        }

        return new ErrorResponse(500, 'An unexpected error occurred while trying to update the Task.  Please try again.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Task $task
     * @return ErrorResponse|SuccessResponse
     * @throws \Exception
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        if ($task->delete()) {
            return new SuccessResponse('Task has been deleted.');
        }

        return new ErrorResponse(500, 'An unexpected error occurred while trying to delete the Task.  Please try again.');
    }
}
