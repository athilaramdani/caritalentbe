<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'siti.ndewi@gmail.com')->first();
$app->make('auth')->login($user);

echo "1. Testing myApplications:\n";
try {
    $request = Illuminate\Http\Request::create('/api/v1/applications/my', 'GET');
    $controller = new \App\Http\Controllers\ApplicationController();
    $response = $controller->myApplications();
    echo "Success\n";
} catch (\Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }

echo "2. Testing myInvitations:\n";
try {
    $request = Illuminate\Http\Request::create('/api/v1/invitations/my', 'GET');
    $controller = new \App\Http\Controllers\InvitationController();
    $response = $controller->myInvitations();
    echo "Success\n";
} catch (\Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }

echo "3. Testing respond to invitation:\n";
try {
    $request = Illuminate\Http\Request::create('/api/v1/invitations/12/respond', 'PUT', ['status' => 'accepted']);
    $formRequest = \App\Http\Requests\RespondInvitationRequest::createFrom($request);
    $formRequest->setContainer($app);
    $formRequest->merge(['status' => 'accepted']);
    $controller = new \App\Http\Controllers\InvitationController();
    $response = $controller->respond($formRequest, 12);
    echo "Success\n";
} catch (\Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }

echo "4. Testing getTalentReviews (using user ID 7):\n";
try {
    $controller = new \App\Http\Controllers\ReviewController();
    $response = $controller->getTalentReviews(7);
    echo "Success\n";
    $data = $response->getData(true);
    echo "Name returned: " . $data['data']['stage_name'] . "\n";
} catch (\Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }
