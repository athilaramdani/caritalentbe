<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::enableQueryLog();

$talent_id = 4; // any id
$reviews = App\Models\Review::with('booking.application.event.organizer')
    ->whereHas('booking.application', function($q) use ($talent_id) {
        $q->where('talent_id', $talent_id);
    })
    ->get();

print_r(DB::getQueryLog());
