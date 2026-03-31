<?php

namespace App\Http\Controllers;

class AuthController extends Controller
{
    public function me(Request $request)
    {
        return response()->json(['message' => 'Placeholder']);
    }
}
