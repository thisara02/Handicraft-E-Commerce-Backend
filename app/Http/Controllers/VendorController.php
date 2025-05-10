<?php

namespace App\Http\Controllers;

use App\Mail\VenderEmailVerify;
use App\Mail\VendorEmailVerify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Models\Vendor;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Product;

class VendorController extends Controller
{

    public function deleteProduct($id)
{
    try {
        // Find the product by ID
        $product = Product::findOrFail($id);

        // Validate that the product belongs to the logged-in vendor
        $vendorId = request()->input('vendor_id');
        if ($product->vendor_id !== $vendorId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        // Delete the product
        $product->delete();

        return response()->json(['success' => true, 'message' => 'Product deleted successfully']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Failed to delete product: ' . $e->getMessage()], 500);
    }
}

 // Get total approved vendor count
 public function getApprovedVendorsCount()
 {
     $approvedVendorsCount = Vendor::where('status', 'approved')->count();
     return response()->json(['approvedVendorsCount' => $approvedVendorsCount]);
 }

  // Get count of pending vendors
  public function getPendingVendorsCount()
  {
      $pendingVendorsCount = Vendor::where('status', 'pending')->count();
      return response()->json(['pendingVendorsCount' => $pendingVendorsCount]);
  }

 public function pending()
 {
     $vendors = Vendor::where('status', 'pending')->get();
     return response()->json($vendors);
 }

 public function approve(Request $request, $id)
 {
     $vendor = Vendor::findOrFail($id);
     $vendor->update(['status' => 'approved']);
     return response()->json(['message' => 'Vendor approved successfully']);
 }

 public function reject(Request $request, $id)
 {
     $vendor = Vendor::findOrFail($id);
     $vendor->update(['status' => 'rejected']);
     return response()->json(['message' => 'Vendor rejected successfully']);
 }

    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'mobile_number' => 'required|string|max:15',
            'address' => 'required|string',
            'nic' => 'required|string|max:20',
            'email' => 'required|email|unique:vendors,email',
            'product_description' => 'required|string',
            'product_types' => 'required|array',
            'password' => 'required|min:8|same:confirmPassword',
            'confirmPassword' => 'required',
            'profile_picture' => 'nullable|string',
        ], [
            'email.unique' => 'This email is already registered.',
            'password.same' => 'Passwords do not match.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $otp = rand(100000, 999999);
        $email = $request->email;

        Cache::put("pending_vendor_$email", $request->all(), now()->addMinutes(5));
        Cache::put("otp_vendor_$email", $otp, now()->addMinutes(5));

        // Send OTP email
        Mail::to($email)->send(new VenderEmailVerify($otp));

        return response()->json(['success' => true, 'message' => 'OTP sent to vendor email']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required'
        ]);

        $cachedOtp = Cache::get("otp_vendor_{$request->email}");
        $vendorData = Cache::get("pending_vendor_{$request->email}");

        if ($cachedOtp && $cachedOtp == $request->otp && $vendorData) {
            Cache::forget("otp_vendor_{$request->email}");
            Cache::forget("pending_vendor_{$request->email}");

            $vendor = new Vendor();
            $vendor->full_name = $vendorData['full_name'];
            $vendor->business_name = $vendorData['business_name'];
            $vendor->mobile_number = $vendorData['mobile_number'];
            $vendor->address = $vendorData['address'];
            $vendor->nic = $vendorData['nic'];
            $vendor->email = $vendorData['email'];
            $vendor->product_description = $vendorData['product_description'];
            $vendor->product_types = json_encode($vendorData['product_types']);
            $vendor->password = bcrypt($vendorData['password']);

            if (!empty($vendorData['profile_picture'])) {
                $image = $vendorData['profile_picture'];

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

                    $vendor->profile_picture = 'uploads/vendors/' . $imageName;
                } else {
                    return response()->json(['success' => false, 'message' => 'Invalid image format.'], 422);
                }
            }


            //$vendor->verifyCode = $cachedOtp; // optional
            $vendor->save();

            return response()->json(['success' => true, 'message' => 'Vendor registered successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid or expired OTP.'], 422);
    }

    public function vendorLogin(Request $request)
    {
        $messages = [
            'email.required' => 'Email is required.',
            'password.required' => 'Password is required.',
        ];

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $vendor = Vendor::where('email', $request->email)->first();

        if (!$vendor || !Hash::check($request->password, $vendor->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.'
            ], 401);
        }

        Session::put('vendor', $vendor);
        $token = JWTAuth::fromUser($vendor);


        return response()->json([
            'success' => true,
            'message' => 'Login successful!',
            'token' => $token,
            'vendor' => $vendor,
            'profile_picture' => url($vendor->profile_picture)
        ]);
    }

    //Update
    public function update(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'id' => 'required|exists:vendors,id',
            'business_name' => 'required|string|max:255',
            'mobile_number' => 'nullable|string|max:20',
            'product_description' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            // Find vendor by ID
            $vendor = Vendor::find($request->id);

            // Update vendor details
            $vendor->business_name = $request->business_name;
            $vendor->mobile_number = $request->mobile_number;
            $vendor->product_description = $request->product_description;

            // If a new profile picture is uploaded
            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/vendors/'), $filename);
                $vendor->profile_picture = 'uploads/vendors/' . $filename;
            }

            // Save changes
            $vendor->save();

            return response()->json([
                'success' => true,
                'message' => 'Vendor profile updated successfully.',
                'vendor' => $vendor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update vendor profile.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
}
