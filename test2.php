<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/api/v1/invitations/12/respond', 'PUT', ['status' => 'accepted']);
$user = \App\Models\User::where('email', 'siti.ndewi@gmail.com')->first();
$app->make('auth')->login($user);

$controller = new \App\Http\Controllers\InvitationController();
// Provide a mock request
$formRequest = \App\Http\Requests\RespondInvitationRequest::createFrom($request);
$formRequest->setContainer($app);
// bypass validation
$formRequest->merge(['status' => 'accepted']);
$response = $controller->respond($formRequest, 12);
echo "Respond Invitation Response:\n";
echo json_encode($response->getData(), JSON_PRETTY_PRINT) . "\n";
