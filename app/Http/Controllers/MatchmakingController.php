<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Talent;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class MatchmakingController extends Controller
{
    #[OA\Get(path: "/events/{event_id}/recommendations", summary: "Get Talent Recommendations", security: [["bearerAuth" => []]], tags: ["Matchmaking"])]
    #[OA\Parameter(name: "event_id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "OK")]
    #[OA\Response(response: 401, description: "Unauthorized")]
    #[OA\Response(response: 403, description: "Forbidden")]
    #[OA\Response(response: 404, description: "Not Found")]
    public function getRecommendations($event_id)
    {
        $event = Event::with('genres')->find($event_id);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event tidak ditemukan'
            ], 404);
        }

        if (Auth::user()->role !== 'eo' || $event->organizer_id != Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        $eventGenreIds = $event->genres->pluck('id')->toArray();
        $eventBudget = $event->budget;
        $eventCity = strtolower(trim($event->city)); // Simplified location matching by city

        $talents = Talent::with(['user', 'genres'])->where('verified', true)->get();

        $recommendations = [];

        foreach ($talents as $talent) {
            $score = 0;
            $genreScore = 0;
            $budgetScore = 0;
            $locationScore = 0;

            // 1. Genre Match (+50)
            $talentGenreIds = $talent->genres->pluck('id')->toArray();
            $intersect = array_intersect($eventGenreIds, $talentGenreIds);
            if (count($intersect) > 0) {
                // If they have at least one genre requested by Event
                $genreScore = 50;
            }

            // 2. Budget Match (+30)
            // Event budget should be >= talent's minimum price
            if ($eventBudget >= $talent->price_min) {
                $budgetScore = 30;
            }

            // 3. Location Match (+20)
            // Basic matching by same city for this version 
            if (strtolower(trim($talent->city)) === $eventCity) {
                $locationScore = 20;
            }

            $score = $genreScore + $budgetScore + $locationScore;

            if ($score > 0) {
                $recommendations[] = [
                    'rank' => 0, // will be sorted later
                    'score' => $score,
                    'score_breakdown' => [
                        'genre_score' => $genreScore,
                        'budget_score' => $budgetScore,
                        'location_score' => $locationScore
                    ],
                    'talent' => [
                        'id' => $talent->user_id,
                        'stage_name' => $talent->stage_name,
                        'genre' => $talent->genres->pluck('name'),
                        'price_min' => $talent->price_min,
                        'price_max' => $talent->price_max,
                        'city' => $talent->city,
                        'verified' => $talent->verified,
                        'average_rating' => $talent->average_rating
                    ]
                ];
            }
        }

        // Sort descending based on score
        usort($recommendations, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // Limit to top 5 and assign rank
        $topRecommendations = array_slice($recommendations, 0, 5);
        foreach ($topRecommendations as $index => &$rec) {
            $rec['rank'] = $index + 1;
        }

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'recommendations' => $topRecommendations
            ]
        ], 200);
    }
}
