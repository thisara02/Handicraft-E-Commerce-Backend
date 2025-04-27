<?php

namespace App\Http\Controllers;

use App\Mail\CusEmailVerify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Models\Customer;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Tymon\JWTAuth\Facades\JWTAuth;

class CustomerAuthController extends Controller
{

    
    // Get total customer count
    public function getTotalCustomers()
    {
        $totalCustomers = Customer::count();
        return response()->json(['totalCustomers' => $totalCustomers]);
    }

    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullName' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'address' => 'required|string',
            'password' => 'required|min:8|same:confirmPassword',
            'confirmPassword' => 'required',
            'phone' => 'required|string',
            'profilePic' => 'nullable|string',
        ], [
            'fullName.required' => 'Full name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Enter a valid email.',
            'email.unique' => 'This email is already registered.',
            'address.required' => 'Address is required.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.same' => 'Passwords do not match.',
            'confirmPassword.required' => 'Confirm password is required.',
            'phone.required' => 'Phone number is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $otp = rand(100000, 999999);
        $email = $request->email;

        // Store OTP in cache for 5 minutes
        Cache::put("pending_user_$email", $request->all(), now()->addMinutes(5));
        Cache::put("otp_$email", $otp, now()->addMinutes(5));

        // ✅ Send OTP using Mailable
        Mail::to($email)->send(new CusEmailVerify($otp));

        //return response()->json(['success' => true, 'message' => 'OTP sent successfully']);
        return response()->json(['success' => true, 'message' => 'OTP sent successfully']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required'
        ]);

        $cachedOtp = Cache::get("otp_{$request->email}");
        $userData = Cache::get("pending_user_{$request->email}");

        if ($cachedOtp && $cachedOtp == $request->otp && $userData) {
            Cache::forget("otp_{$request->email}");
            Cache::forget("pending_user_{$request->email}");

            $customer = new Customer();
            $customer->name = $userData['fullName'];
            $customer->email = $userData['email'];
            $customer->address = $userData['address'];
            $customer->phone = $userData['phone'];
            $customer->password = bcrypt($userData['password']);

            if (!empty($userData['profilePic'])) {
                $image = $userData['profilePic'];

                // Detect and extract image extension
                if (preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
                    $image = substr($image, strpos($image, ',') + 1);
                    $type = strtolower($type[1]); // jpg, jpeg, png

                    // Check allowed types
                    if (!in_array($type, ['jpg', 'jpeg', 'png'])) {
                        return response()->json(['success' => false, 'message' => 'Invalid image type. Only JPG, JPEG, and PNG are allowed.'], 422);
                    }

                    $image = str_replace(' ', '+', $image);
                    $imageName = uniqid() . '.' . $type;

                    File::put(public_path('uploads/vendors/' . $imageName), base64_decode($image));

                    $customer->profile_picture = 'uploads/' . $imageName;
                } else {
                    return response()->json(['success' => false, 'message' => 'Invalid image format.'], 422);
                }
            }


            //$customer->profile_picture = $userData['profilePic'] ?? null;
            $customer->verifyCode = $cachedOtp; // optional
            $customer->save();

            return response()->json(['success' => true, 'message' => 'Email verified and customer registered.']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid or expired OTP.'], 422);
    }

    public function customerLogin(Request $request)
    {
        // Custom validation messages (optional)
        $messages = [
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
        ];

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], $messages);

        // If validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 422);
        }

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer || !Hash::check($request->password, $customer->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password. Please try again.'
            ], 401);
        }

        // Store in session
        Session::put('customer', $customer);
        $token = JWTAuth::fromUser($customer);
        return response()->json([
            'success' => true,
            'message' => 'Login successful!',
            'token' => $token,
            'customer' => $customer,
            'profile_picture' => url($customer->profile_picture) // full URL
        ]);
    }

    public function update(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'id' => 'required|exists:customers,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        try {
            // Find customer by ID
            $customer = Customer::find($request->id);

            // Update customer details
            $customer->name = $request->name;
            $customer->phone = $request->phone;
            $customer->address = $request->address;

            // Save changes
            $customer->save();

            return response()->json([
                'success' => true,
                'message' => 'Customer profile updated successfully.',
                'customer' => $customer
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update customer profile.',
                'error' => $e->getMessage()
            ], 500);
        }
    }




    // public function registerCustomer(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'fullName' => 'required|string|max:255',
    //         'email' => 'required|email|unique:customers,email',
    //         'address' => 'required|string',
    //         'password' => 'required|min:8',
    //         'verifyCode' => 'nullable|string',
    //         'profilePic' => 'nullable|string', // base64 or image URL
    //         'confirmPassword' => 'required|same:password',
    //         'phone' => 'required|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    //     }

    //     $customer = new Customer();
    //     $customer->name = $request->fullName;
    //     $customer->email = $request->email;
    //     $customer->address = $request->address;
    //     $customer->phone = $request->phone;
    //     $customer->password = bcrypt($request->password);
    //     $customer->verifyCode = $request->verifyCode ?? null;
    //     $customer->profile_picture = $request->profilePic ?? null;
    //     $customer->save();

    //     return response()->json(['success' => true, 'message' => 'Customer registered successfully']);
    // }
}
