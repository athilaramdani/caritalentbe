<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(title: "CariTalent API Specification", version: "1.0", description: "Dokumentasi API untuk platform Direktori & Booking Talent Ekonomi Kreatif (CariTalent)")]
#[OA\Server(url: "http://localhost:8000/api/v1", description: "Local API Server")]
#[OA\SecurityScheme(securityScheme: "bearerAuth", type: "http", scheme: "bearer", bearerFormat: "JWT")]
class SwaggerController extends Controller
{
    #[OA\Get(path: "/api/placeholder", summary: "Test Placeholder API")]
    #[OA\Response(response: 200, description: "Success")]
    public function index()
    {
        return response()->json(['status' => 'ok']);
    }
}
