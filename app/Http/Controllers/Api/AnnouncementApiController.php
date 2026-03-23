<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnnouncementAlert;
use App\Models\AnnouncementAlertRead;
use Illuminate\Http\Request;

class AnnouncementApiController extends Controller
{
    /**
     * Get all announcements for the authenticated user
     * Filters by user role (responder or citizen)
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Determine user role and filter announcements
            $query = AnnouncementAlert::query();

            if ($user->role === 'responder') {
                $query->where('for_responders', true);
            } elseif ($user->role === 'citizen') {
                $query->where('for_citizens', true);
            }

            // Get announcements with creator info and reader count
            $announcements = $query
                ->with('creator:id,full_name')
                ->withCount('readers') // Count how many users read it
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($announcement) use ($user) {
                    return [
                        'id' => $announcement->id,
                        'announcement_title' => $announcement->announcement_title,
                        'announcement_message' => $announcement->announcement_message,
                        'created_at' => $announcement->created_at,
                        'created_by_name' => $announcement->creator?->full_name ?? 'Unknown',
                        'readers_count' => $announcement->readers_count,
                        'is_read' => $announcement->readers()
                            ->where('user_id', $user->id)
                            ->exists()
                    ];
                });

            return response()->json([
                'success' => true,
                'announcements' => $announcements
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch announcements: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark an announcement as read by the authenticated user
     */
    public function markAsRead($id, Request $request)
    {
        try {
            $user = $request->user();
            $announcement = AnnouncementAlert::find($id);

            if (!$announcement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Announcement not found'
                ], 404);
            }

            // Create or update the read record
            AnnouncementAlertRead::firstOrCreate(
                [
                    'announcement_alert_id' => $id,
                    'user_id' => $user->id
                ],
                [
                    'read_at' => now()
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Announcement marked as read'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark announcement: ' . $e->getMessage()
            ], 500);
        }
    }
}