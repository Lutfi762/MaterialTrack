<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class WelcomeController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Auth/Login');
    }
}
