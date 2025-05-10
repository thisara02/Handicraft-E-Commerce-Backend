<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AdminDashboardController extends Controller
{
    public function dashboard()
    {
    $username = session('admin_username');
    return view('admin.dashboard', ['username' => $username]);
    }
}
