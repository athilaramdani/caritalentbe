<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test getting my applications
$request = Illuminate\Http\Request::create('/api/v1/applications/my', 'GET');
$user = \App\Models\User::where('email', 'siti.ndewi@gmail.com')->first();
$app->make('auth')->login($user);

$controller = new \App\Http\Controllers\ApplicationController();
$response = $controller->myApplications();
echo "My Applications Response:\n";
echo json_encode($response->getData(), JSON_PRETTY_PRINT) . "\n";
