<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (\App\Models\User::all() as $u) {
    echo $u->email . ' | role=' . $u->getRoleNames()->implode(',') . PHP_EOL;
}
echo 'OK' . PHP_EOL;