<?php

namespace App\Http\Controllers\Business;

use App\Http\Requests\PaginatedResourceRequest;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use App\SystemNotification;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SystemNotificationController extends BaseController
{
    /**
     * Get a preview of the user's notifications for
     * the notifications icon in the page header.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function preview(Request $request)
    {
        $query = auth()->user()->systemNotifications()
            ->latest()
            ->whereNull('acknowledged_at');

        $total = $query->count();

        $notifications = $query->limit(20)->get();

        return response()->json(compact('total', 'notifications'));
    }

    /**
     * Display a listing of the resource.
     *
     * @param PaginatedResourceRequest $request
     * @return \Illuminate\Http\Response
     */
    public function index(PaginatedResourceRequest $request)
    {
        if ($request->expectsJson() && $request->input('json')) {
            $query = auth()->user()->systemNotifications()
                ->latest();

            if ($request->acknowledged == '1') {
                $query->whereNotNull('acknowledged_at');
            } else {
                $query->whereNull('acknowledged_at');
            }

            $total = $query->count();

            $results = $query->offset($request->getOffset())
                ->limit($request->getPerPage(25))
                ->get();

            return response()->json(compact('total', 'results'));
        }

        return view_component('system-notifications-page', 'System Notifications', [], [
            'Home' => route('home'),
        ]);
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
        
        return view_component('system-notification', 'System Notification: '.$notification->title, [
            'notification' => $notification,
            'acknowledger' => $notification->acknowledger ?: null,
        ], [
            'Home' => route('home'),
            'Notifications' => route('business.notifications.index'),
        ]);
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

        if ($notification->acknowledge()) {
            return new SuccessResponse('You have successfully acknowledged the notification.', [], route('business.notifications.index'));
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
        $chain = optional(\Auth::user()->officeUser)->businessChain;
        if (! $chain) {
            throw new AccessDeniedHttpException('A business chain was not found.');
        }

        SystemNotification::where('event_id', $eventId)
            ->whereNull('acknowledged_at')
            ->whereIn('user_id', $chain->users->pluck('id'))
            ->update([
                'acknowledged_at' => Carbon::now(),
                'notes' => $request->input('notes', ''),
            ]);

        return new SuccessResponse('All notifications have been marked as acknowledged.', [], route('business.notifications.index'));
    }
}
