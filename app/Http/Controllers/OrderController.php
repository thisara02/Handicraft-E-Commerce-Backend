<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function getTotalOrderCount()
{
    try {
        // Fetch the total number of orders
        $totalCount = Order::count();

        return response()->json(['success' => true, 'total_count' => $totalCount], 200);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Failed to fetch total order count: ' . $e->getMessage()], 500);
    }
}
    public function getAllOrders()
    {
        try {
            // Fetch all orders with vendor and customer details
            $orders = Order::with(['vendor', 'customer'])->get();
    
            // Transform the data for the frontend
            $formattedOrders = $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'vendor_id' => $order->vendor_id,
                    'vendor_name' => $order->vendor?->business_name ?? 'N/A',
                    'customer_name' => $order->full_name ?? 'N/A',
                    'email_address' => $order->email_address ?? 'N/A',
                    'phone_number' => $order->phone_number ?? 'N/A',
                    'total_amount' => (float) $order->total_amount,
                    'payment_status' => $order->payment_status,
                    'shipping_status' => $order->shipping_status,
                    'created_at' => $order->created_at->format('Y-m-d'),
                ];
            });
    
            return response()->json(['success' => true, 'orders' => $formattedOrders], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch orders: ' . $e->getMessage()], 500);
        }
    }
    public function updateOrderStatus(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'order_id' => 'required|exists:orders,id',
                'shipping_status' => 'nullable|string',
            ]);
    
            // Find and update the order
            $order = Order::findOrFail($validated['order_id']);
            if ($validated['shipping_status']) {
                $order->shipping_status = $validated['shipping_status'];
            }
            $order->save();
    
            // Return the updated order details
            return response()->json([
                'success' => true,
                'message' => 'Shipping status updated successfully',
                'updated_order' => [
                    'id' => $order->id,
                    'shipping_status' => $order->shipping_status,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating shipping status:', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to update shipping status'], 500);
        }
    }

    public function getVendorOrders(Request $request)
    {
        try {
            // Retrieve the vendor ID from the query parameters
            $vendorId = $request->input('vendor_id');

            if (!$vendorId) {
                return response()->json(['success' => false, 'message' => 'Vendor ID is required'], 400);
            }

            // Fetch orders for the vendor
            $orders = Order::where('vendor_id', $vendorId)
                
                ->get();

            // Transform the data for the frontend
            $formattedOrders = $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'date' => $order->created_at->format('Y-m-d'),
                    'status' => $order->payment_status,
                    'total' =>(float) $order->total_amount,
                    
                        'full_name' => $order->full_name,
                        'email_address' => $order->email_address,
                        'phone_number' => $order->phone_number,
                        'shippingaddress' => $order->street_address . ', ' . $order->town_city . ', ' . $order->country,
                  
                    'items' => json_decode($order->order_summary, true), // Decode JSON order summary
               'shipping_status' => $order->shipping_status ?? 'processing',
                ];
            });

            return response()->json(['success' => true, 'orders' => $formattedOrders], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch orders: ' . $e->getMessage()], 500);
        }
    }
    public function store(Request $request)
    {
        try {
            // Validate incoming request data
            $validatedData = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'vendor_id' => 'required|exists:vendors,id',
                'full_name' => 'required|string|max:255',
                'email_address' => 'required|email|max:255',
                'phone_number' => 'required|string|max:20',
                'country' => 'required|string|max:255',
                'street_address' => 'required|string|max:255',
                'town_city' => 'required|string|max:255',
                'total_amount' => 'required|numeric|min:0',
                'order_summary' => 'required|array', // Array of products
            ]);

            // Create the order
            $order = Order::create([
                'customer_id' => $validatedData['customer_id'],
                'vendor_id' => $validatedData['vendor_id'],
                'full_name' => $validatedData['full_name'],
                'email_address' => $validatedData['email_address'],
                'phone_number' => $validatedData['phone_number'],
                'country' => $validatedData['country'],
                'street_address' => $validatedData['street_address'],
                'town_city' => $validatedData['town_city'],
                'total_amount' => $validatedData['total_amount'],
                'shipping_status' => 'processing',
                'payment_status' => 'successful', // Initial status
                'order_summary' => json_encode($validatedData['order_summary']), // JSON-encode product details
            ]);

            return response()->json(['success' => true, 'message' => 'Order created successfully', 'order_id' => $order->id], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to create order: ' . $e->getMessage()], 500);
        }
    }
}