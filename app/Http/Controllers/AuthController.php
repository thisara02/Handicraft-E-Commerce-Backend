<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Vendor;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // Show login form (optional)
    public function showLoginForm()
    {
        return view('auth.vendor-login');
    }

    // Handle login logic
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $vendor = Vendor::where('email', $request->email)->first();

        if ($vendor && Hash::check($request->password, $vendor->password)) {
            // Generate token or session for authenticated vendor
            // $token = $vendor->createToken('vendor-token')->plainTextToken;

            $token = JWTAuth::fromUser($vendor);

            return response()->json([
                'message' => 'Login successful',
                'vendor' => $vendor,
                'token'=>$token
            ], 200);
        }

        
        return response()->json([
            'message' => 'Invalid credentials',
        ], 401);
    }
}
