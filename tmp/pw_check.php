<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$u = App\Models\User::where('email', 'admin@materialtrack.com')->first();
echo $u ? (Illuminate\Support\Facades\Hash::check('password', $u->password) ? "password matches\n" : "password MISMATCH\n") : "no user\n";
$jar = __DIR__ . '/cookies.txt';
if (file_exists($jar)) {
    foreach (file($jar) as $line) {
        if (str_starts_with($line, '#') || trim($line) === '') continue;
        $p = explode("\t", $line);
        echo "cookie: " . trim($p[5]) . " = " . substr(trim($p[6] ?? ''), 0, 20) . "\n";
    }
} else {
    echo "no cookie jar\n";
}