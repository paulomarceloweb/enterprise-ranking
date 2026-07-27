<?php

namespace App\Controllers;

use App\Core\Auth;

class DashboardController
{
    public function index(): void
    {
        Auth::exigirLogin();
        $titulo = 'Dashboard';
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/dashboard/index.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }
}