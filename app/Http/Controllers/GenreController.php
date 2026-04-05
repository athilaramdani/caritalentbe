<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Traits\ApiResponse;
use OpenApi\Attributes as OA;

class GenreController extends Controller
{
    use ApiResponse;

    #[OA\Get(path: "/genres", summary: "Get list of genres", tags: ["Genre"])]
    #[OA\Response(response: 200, description: "Returns list of genres")]
    public function index()
    {
        $genres = Genre::all();
        return $this->successResponse('OK', ['genres' => $genres]);
    }
}
