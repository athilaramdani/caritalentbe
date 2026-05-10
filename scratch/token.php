<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
if (!$user) {
    $user = App\Models\User::create([
        'name' => 'EO',
        'email' => 'eo@example.com',
        'password' => bcrypt('password'),
        'role' => 'eo'
    ]);
} else {
    $user->update(['role' => 'eo']);
}

echo $user->createToken('test')->plainTextToken;
