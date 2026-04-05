<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Booking;
use App\Models\Talent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class ReviewController extends Controller
{
    #[OA\Post(path: "/reviews", summary: "Create Review (EO)", security: [["bearerAuth" => []]], tags: ["Review"])]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "booking_id", type: "integer"),
                new OA\Property(property: "rating", type: "integer", example: 5),
                new OA\Property(property: "comment", type: "string")
            ]
        )
    )]
    #[OA\Response(response: 201, description: "Created")]
    #[OA\Response(response: 401, description: "Unauthorized")]
    #[OA\Response(response: 403, description: "Forbidden")]
    #[OA\Response(response: 422, description: "Unprocessable Entity")]
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        if (Auth::user()->role !== 'eo') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya EO yang bisa memberikan ulasan'
            ], 403);
        }

        $booking = Booking::with('application.event')->find($request->booking_id);

        if ($booking->application->event->organizer_id != Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        if ($booking->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Review hanya bisa diberikan setelah event selesai'
            ], 422);
        }

        $existingReview = Review::where('booking_id', $request->booking_id)->first();
        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu sudah memberikan review untuk booking ini'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $review = Review::create([
                'booking_id' => $request->booking_id,
                'rating' => $request->rating,
                'comment' => $request->comment
            ]);

            $talentId = $booking->application->talent_id;
            $talentProfile = Talent::where('user_id', $talentId)->first();

            if ($talentProfile) {
                // Query all reviews for this talent
                $allReviews = Review::whereHas('booking.application', function($q) use ($talentId) {
                    $q->where('talent_id', $talentId);
                })->get();
                
                $totalReviews = $allReviews->count();
                $averageRating = $allReviews->avg('rating');
                
                $talentProfile->update([
                    'total_reviews' => $totalReviews,
                    'average_rating' => round($averageRating, 1)
                ]);
            }

            // Create notification for talent
            \App\Models\Notification::create([
                'user_id' => $booking->application->talent_id,
                'title' => 'Review Baru',
                'body' => 'EO '. Auth::user()->name .' telah memberikan ulasan untuk performa kamu.',
                'type' => 'review',
                'reference_id' => $review->id
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Review berhasil dikirim',
                'data' => [
                    'id' => $review->id,
                    'booking_id' => $review->booking_id,
                    'talent' => [
                        'id' => $talentId,
                        'stage_name' => $talentProfile ? $talentProfile->stage_name : $booking->application->talent->name
                    ],
                    'event' => [
                        'id' => $booking->application->event_id,
                        'title' => $booking->application->event->title
                    ],
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at
                ]
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    #[OA\Get(path: "/talents/{talent_id}/reviews", summary: "Get Reviews by Talent", tags: ["Review"])]
    #[OA\Parameter(name: "talent_id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "OK")]
    #[OA\Response(response: 404, description: "Not Found")]
    public function getTalentReviews($talent_id)
    {
        $talentProfile = Talent::where('user_id', $talent_id)->first();
        $user = \App\Models\User::find($talent_id);

        if (!$user || $user->role !== 'talent') {
            return response()->json([
                'success' => false,
                'message' => 'Talent tidak ditemukan'
            ], 404);
        }

        $reviews = Review::with('booking.application.event')
            ->whereHas('booking.application', function($q) use ($talent_id) {
                $q->where('talent_id', $talent_id);
            })
            ->latest()
            ->paginate(15);

        $mappedReviews = $reviews->map(function($review) {
            return [
                'id' => $review->id,
                'organizer_name' => $review->booking->application->event->organizer->name ?? null,
                'event_title' => $review->booking->application->event->title ?? null,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'talent_id' => $talent_id,
                'stage_name' => $talentProfile ? $talentProfile->stage_name : $user->name,
                'average_rating' => $talentProfile ? $talentProfile->average_rating : 0,
                'total_reviews' => $talentProfile ? $talentProfile->total_reviews : 0,
                'reviews' => $mappedReviews,
                'pagination' => [
                    'current_page' => $reviews->currentPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
                    'last_page' => $reviews->lastPage()
                ]
            ]
        ], 200);
    }
}
