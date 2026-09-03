<?php

declare(strict_types=1);

use App\Auth\Auth;

require_once dirname(__DIR__) . '/src/bootstrap.php';

redirect(Auth::check() ? '/dashboard.php' : '/login.php');
