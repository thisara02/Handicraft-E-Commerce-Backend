<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;
use App\Mail\OtpMail;

class ForgotPasswordController extends Controller
{
    // Send OTP to email
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Check if the email exists in the customers table
        $customer = Customer::where('email', $request->email)->first();

        if (!$customer) {
            return response()->json(['message' => 'The email address is not registered.'], 404);
        }

        // Generate OTP
        $otp = rand(100000, 999999);

        // Save OTP to the customer record
        $customer->update(['otp' => $otp]);

        // Send OTP via email
        Mail::to($request->email)->send(new OtpMail($otp));

        return response()->json(['message' => 'OTP sent to your email.'], 200);
    }

    // Verify OTP
    public function verifyOtp(Request $request){
    try {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation failed:', $e->errors());
        return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
    }
        $customer = Customer::where('email', $request->email)->first();

        if (!$customer || $customer->otp !== $request->otp) {
            return response()->json(['message' => 'Invalid OTP.'], 400);
        }

        // Clear OTP after verification
        $customer->update(['otp' => null]);

        return response()->json(['message' => 'OTP verified successfully.'], 200);
    }

    // Reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer) {
            return response()->json(['message' => 'Customer not found.'], 404);
        }

        // Update password
        $customer->update(['password' => Hash::make($request->password)]);

        return response()->json(['message' => 'Password updated successfully.'], 200);
    }
}