<?php

namespace App\Http\Controllers\Business;

use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use App\SystemNotification;
use Log;

class SystemNotificationController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = auth()->user()->systemNotifications()->latest();
        $notifications = (clone $query)->whereNull('acknowledged_at')
            ->get();

        if ($request->expectsJson() && $request->input('json')) {
            return collection_only_values($notifications, ['id', 'title', 'message', 'created_at', '']);
        }

        $archived = (clone $query)->whereNotNull('acknowledged_at')
            ->orderBy('acknowledged_at', 'DESC')
            ->get();

        return view('business.notifications.index', compact('notifications', 'archived'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SystemNotification  $notification
     * @return \Illuminate\Http\Response
     * @throws AuthorizationException
     */
    public function show(SystemNotification $notification)
    {
        $this->authorize('read', $notification);
        
        return view('business.notifications.show', compact('notification'));
    }

    /**
     * Acknowledge the specific notification
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\SystemNotification $notification
     * @return \App\Responses\ErrorResponse|\App\Responses\SuccessResponse
     * @throws AuthorizationException
     */
    public function acknowledge(Request $request, SystemNotification $notification)
    {
        $this->authorize('update', $notification);

        if ($notification->acknowledge($request->input('notes', ''))) {
            return new SuccessResponse('You have successfully acknowlegded the notification.', [], route('business.notifications.index'));
        }

        return new ErrorResponse(500, 'Error updating notification.');
    }

    /**
     * Mark all unread notifications as acknowledged.
     *
     * @param Request $request
     * @return SuccessResponse
     */
    public function acknowledgeAll(Request $request)
    {
        auth()->user()->systemNotifications()
            ->whereNull('acknowledged_at')
            ->update(['acknowledged_at' => Carbon::now()]);

        return new SuccessResponse('All notifications have been marked as acknowledged.', [], '.');
    }

    /**
     * Mark unread notifications for chain as acknowledged.
     *
     * @param Request $request
     * @param $eventId
     * @return SuccessResponse
     */
    public function acknowledgeAllForChain(Request $request, $eventId)
    {
        SystemNotification::where('event_id', $eventId)
            ->whereNull('acknowledged_at')
            ->update(['acknowledged_at' => Carbon::now()]);

        return new SuccessResponse('All notifications have been marked as acknowledged.', [], route('business.notifications.index'));
    }
}
