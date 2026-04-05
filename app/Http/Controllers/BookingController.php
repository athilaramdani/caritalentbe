<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Talent;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class BookingController extends Controller
{
    #[OA\Get(path: "/bookings/{id}", summary: "Get Booking by ID", security: [["bearerAuth" => []]], tags: ["Booking"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "OK")]
    #[OA\Response(response: 401, description: "Unauthorized")]
    #[OA\Response(response: 403, description: "Forbidden")]
    #[OA\Response(response: 404, description: "Not Found")]
    public function show($id)
    {
        $booking = Booking::with(['application.event', 'application.talent'])->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking tidak ditemukan'
            ], 404);
        }

        $user = Auth::user();
        $isOrganizer = $booking->application->event->organizer_id == $user->id;
        $isTalent = $booking->application->talent_id == $user->id;

        if (!$isOrganizer && !$isTalent && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        $talentProfile = Talent::where('user_id', $booking->application->talent_id)->first();

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'id' => $booking->id,
                'application_id' => $booking->application_id,
                'source' => $booking->application->source,
                'event' => [
                    'id' => $booking->application->event->id,
                    'title' => $booking->application->event->title,
                    'event_date' => $booking->application->event->event_date,
                    'venue_name' => $booking->application->event->venue_name,
                    'latitude' => $booking->application->event->latitude,
                    'longitude' => $booking->application->event->longitude
                ],
                'talent' => [
                    'id' => $booking->application->talent_id,
                    'stage_name' => $talentProfile ? $talentProfile->stage_name : $booking->application->talent->name
                ],
                'agreed_price' => $booking->agreed_price,
                'status' => $booking->status,
                'created_at' => $booking->created_at
            ]
        ], 200);
    }

    #[OA\Get(path: "/bookings/my", summary: "Get My Bookings", security: [["bearerAuth" => []]], tags: ["Booking"])]
    #[OA\Parameter(name: "status", in: "query", required: false, schema: new OA\Schema(type: "string"))]
    #[OA\Response(response: 200, description: "OK")]
    #[OA\Response(response: 401, description: "Unauthorized")]
    public function getMyBookings(Request $request)
    {
        $user = Auth::user();
        $query = Booking::with(['application.event', 'application.talent']);

        if ($user->role === 'eo') {
            $query->whereHas('application.event', function($q) use ($user) {
                $q->where('organizer_id', $user->id);
            });
        } elseif ($user->role === 'talent') {
            $query->whereHas('application', function($q) use ($user) {
                $q->where('talent_id', $user->id);
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->latest()->get()->map(function ($booking) {
            $talentProfile = Talent::where('user_id', $booking->application->talent_id)->first();
            return [
                'id' => $booking->id,
                'application_id' => $booking->application_id,
                'source' => $booking->application->source,
                'event' => [
                    'id' => $booking->application->event->id ?? null,
                    'title' => $booking->application->event->title ?? null,
                    'event_date' => $booking->application->event->event_date ?? null,
                    'venue_name' => $booking->application->event->venue_name ?? null,
                ],
                'talent' => [
                    'id' => $booking->application->talent_id,
                    'stage_name' => $talentProfile ? $talentProfile->stage_name : ($booking->application->talent->name ?? null),
                ],
                'agreed_price' => $booking->agreed_price,
                'status' => $booking->status,
                'created_at' => $booking->created_at
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'bookings' => $bookings
            ]
        ], 200);
    }

    #[OA\Put(path: "/bookings/{id}/complete", summary: "Complete Booking (EO)", security: [["bearerAuth" => []]], tags: ["Booking"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "OK")]
    #[OA\Response(response: 401, description: "Unauthorized")]
    #[OA\Response(response: 403, description: "Forbidden")]
    #[OA\Response(response: 404, description: "Not Found")]
    public function complete($id)
    {
        $booking = Booking::with('application.event')->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking tidak ditemukan'
            ], 404);
        }

        if (Auth::user()->role !== 'eo' || $booking->application->event->organizer_id != Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        if ($booking->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya booking dengan status confirmed yang dapat diselesaikan'
            ], 400);
        }

        $booking->update(['status' => 'completed']);

        // Create notification for talent
        \App\Models\Notification::create([
            'user_id' => $booking->application->talent_id,
            'title' => 'Event Selesai',
            'body' => 'Event ' . $booking->application->event->title . ' telah ditandai selesai oleh eo.',
            'type' => 'booking',
            'reference_id' => $booking->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking telah ditandai selesai',
            'data' => [
                'id' => $booking->id,
                'status' => 'completed'
            ]
        ], 200);
    }

    #[OA\Put(path: "/bookings/{id}/cancel", summary: "Cancel Booking", security: [["bearerAuth" => []]], tags: ["Booking"])]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "OK")]
    #[OA\Response(response: 401, description: "Unauthorized")]
    #[OA\Response(response: 403, description: "Forbidden")]
    #[OA\Response(response: 404, description: "Not Found")]
    public function cancel($id)
    {
        $booking = Booking::with('application.event')->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking tidak ditemukan'
            ], 404);
        }

        $user = Auth::user();
        if ($user->role !== 'admin' && ($user->role !== 'eo' || $booking->application->event->organizer_id != $user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        if ($booking->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya booking dengan status confirmed yang dapat dibatalkan'
            ], 400);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil dibatalkan'
        ], 200);
    }
}
