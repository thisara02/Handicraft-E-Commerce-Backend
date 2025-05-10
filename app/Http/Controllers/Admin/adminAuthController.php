<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class adminAuthController extends Controller
{
    public function login(Request $request)
    {

        
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find the admin by username
        $admin = Admin::where('username', $credentials['username'])->first();

        if ($admin && $admin->password === $credentials['password']) {
           
            Session::put('admin_username', $admin->username);
            return response()->json([
                'message' => 'Login successful',
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid username or password',
        ], 401);
    }
    public function getAdminUsername()
    {
        // Retrieve username from session
        $username = Session::get('admin_username');

        if ($username) {
            return response()->json([
                'username' => $username,
            ], 200);
        }

        return response()->json([
            'message' => 'No admin logged in',
        ], 401);
    }
}
