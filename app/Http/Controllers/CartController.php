<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{

 // Update a cart item's quantity
 public function updateCartItem(Request $request)
 {
     try {
         $request->validate([
             'customer_id' => 'required|exists:customers,id',
             'product_id' => 'required|exists:products,id',
             'quantity' => 'required|integer|min:1',
         ]);

         $customerId = $request->input('customer_id');
         $productId = $request->input('product_id');
         $quantity = $request->input('quantity');

         // Find the cart item
         $cartItem = Cart::where('customer_id', $customerId)
                         ->where('product_id', $productId)
                         ->first();

         if ($cartItem) {
             $cartItem->update(['quantity' => $quantity]);
             return response()->json(['message' => 'Cart item updated successfully']);
         }

         return response()->json(['error' => 'Cart item not found'], 404);
     } catch (\Exception $e) {
         Log::error('Error updating cart item: ' . $e->getMessage());
         return response()->json(['message' => 'Failed to update cart item'], 500);
     }
 }

     // Delete a cart item
     public function deleteCartItem(Request $request)
     {
         try {
             $request->validate([
                 'customer_id' => 'required|exists:customers,id',
                 'product_id' => 'required|exists:products,id',
             ]);
 
             $customerId = $request->input('customer_id');
             $productId = $request->input('product_id');
 
             // Find the cart item
             $cartItem = Cart::where('customer_id', $customerId)
                             ->where('product_id', $productId)
                             ->first();
 
             if ($cartItem) {
                 $cartItem->delete();
                 return response()->json(['message' => 'Cart item deleted successfully']);
             }
 
             return response()->json(['error' => 'Cart item not found'], 404);
         } catch (\Exception $e) {
             Log::error('Error deleting cart item: ' . $e->getMessage());
             return response()->json(['message' => 'Failed to delete cart item'], 500);
         }
     }

     // Fetch cart items for a specific customer
     public function getCartItems(Request $request)
     {
         try {
             // Validate incoming data
             $request->validate([
                 'customer_id' => 'required|exists:customers,id',
             ]);
 
             $customerId = $request->input('customer_id');
 
             // Fetch cart items for the customer
             $cartItems = Cart::where('customer_id', $customerId)->get();
 
             // Optionally, include related product details
             $cartItemsWithDetails = $cartItems->map(function ($item) {
                 return [
                     'id' => $item->id,
                     'product_id' => $item->product_id,
                     'quantity' => $item->quantity,
                     'product' => $item->product, // Assuming you have a relationship defined in the Cart model
                 ];
             });
 
             return response()->json(['cart_items' => $cartItemsWithDetails]);
         } catch (\Exception $e) {
             Log::error('Error fetching cart items: ' . $e->getMessage());
             return response()->json(['message' => 'Failed to fetch cart items'], 500);
         }
     }

    public function addToCart(Request $request)
    {
        try {
            // Validate incoming data
            $request->validate([
                'customer_id' => 'required|exists:customers,id', 
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $customerId = $request->input('customer_id');
            $productId = $request->input('product_id');
            $quantity = $request->input('quantity');

            // Check if the product is already in the cart
            $cartItem = Cart::where('customer_id', $customerId)
                            ->where('product_id', $productId)
                            ->first();

            if ($cartItem) {
                // Update the quantity if the product is already in the cart
                $cartItem->update(['quantity' => $cartItem->quantity + $quantity]);
            } else {
                // Add a new item to the cart
                Cart::create([
                    'customer_id' => $customerId,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                ]);
            }

            return response()->json(['message' => 'Product added to cart successfully']);
        } catch (\Exception $e) {
            Log::error('Error adding product to cart: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to add product to cart'], 500);
        }
    }
}