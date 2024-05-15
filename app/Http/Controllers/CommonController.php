<?php

namespace App\Http\Controllers;


class CommonController extends Controller
{
    /**
     * Get all unread notifications for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse JSON response containing unread notifications.
     */
    public function allNotifications()
    {
        return response()->json(['saved' => true, 'notification' => authUser()->unreadNotifications()->take(300)->latest()->get()]);
    }

    /**
     * Paginate unread notifications for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse Paginated JSON response containing unread notifications.
     */
    public function paginationNotifications()
    {
        return response()->json(authUser()->unreadNotifications()->paginate(10));
    }

    /**
     * Mark a specific notification as read.
     *
     * @param int $id The ID of the notification to mark as read.
     * @return \Illuminate\Http\JsonResponse JSON response indicating success.
     */
    public function makeReadNotification($id)
    {
        $notification = authUser()->notifications()->where('id', $id)->first();
        $notification->markAsRead();
        return response()->json(['saved' => true]);
    }

    /**
     * Mark all unread notifications as read for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse JSON response indicating success.
     */
    public function markAllRead()
    {
        authUser()->unreadNotifications->markAsRead();
        return response()->json(['saved' => true]);
    }

    /**
     * Delete a specific notification.
     *
     * @param int $id The ID of the notification to delete.
     * @return \Illuminate\Http\JsonResponse JSON response indicating success.
     */
    public function deleteNotification($id)
    {
        $notification = authUser()->notifications()->where('id', $id)->first();
        $notification->delete();
        return response()->json(['saved' => true]);
    }
}
