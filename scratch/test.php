<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// create a dummy user
$user = App\Models\User::first() ?? App\Models\User::factory()->create();

// mock sanctum authentication
$app->make('auth')->guard('sanctum')->setUser($user);

$request = Illuminate\Http\Request::create('/api/v1/events/my', 'GET');
$request->headers->set('Authorization', 'Bearer dummy');
// Actually, setting the user on the guard might be enough for sanctum if we mock it, or we can use actingAs

$response = $kernel->handle($request);
echo $response->getContent();
