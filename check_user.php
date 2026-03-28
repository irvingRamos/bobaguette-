<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'admin@bobaguette.com')->first();
if ($user) {
    echo "User exists: " . $user->name . " (ID: " . $user->id . ")\n";
} else {
    echo "User NOT found\n";
}
