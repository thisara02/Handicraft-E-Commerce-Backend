<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WishlistController extends Controller
{
    public function getWishlistItems(Request $request)
    {
        try {
            // Validate incoming data
            $request->validate([
                'customer_id' => 'required|exists:customers,id',
            ]);

            $customerId = $request->input('customer_id');

            // Fetch cart items for the customer
            $WishlistItems = Wishlist::where('customer_id', $customerId)->get();

            // Optionally, include related product details
            $wishlistItemsWithDetails = $WishlistItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
            
                    'product' => $item->product, // Assuming you have a relationship defined in the Cart model
                ];
            });

            return response()->json(['wishlist_items' => $wishlistItemsWithDetails]);
        } catch (\Exception $e) {
            Log::error('Error fetching wishlist items: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch wishlist items'], 500);
        }
    }
    
    // Add a product to the wishlist
    public function addToWishlist(Request $request)
    {
        try {
            // Validate incoming data
            $request->validate([
                'customer_id' => 'required|exists:customers,id', 
                'product_id' => 'required|exists:products,id',
    
            ]);

            $customerId = $request->input('customer_id');
            $productId = $request->input('product_id');
            
            // Check if the product is already in the cart
            $existingWishlistItem = Wishlist::where('customer_id', $customerId)
                            ->where('product_id', $productId)
                            ->first();

                            if ($existingWishlistItem) {
                                return response()->json(['message' => 'Product already in wishlist'], 400);
                            
            } else {
                Wishlist::create([
                    'customer_id' => $customerId,
                    'product_id' => $request->input('product_id'),
                ]);
        
                return response()->json(['message' => 'Product added to wishlist'], 201);
            }

            return response()->json(['message' => 'Product added to wishlist successfully']);
        } catch (\Exception $e) {
            Log::error('Error adding product to wishlist: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to add product to wishlist'], 500);
        }
    }

     // Remove a product from the wishlist
    public function removeWishlistItem(Request $request)
    {
        try {
            // Validate incoming data
            $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'product_id' => 'required|exists:products,id',
            ]);

            $customerId = $request->input('customer_id');
            $productId = $request->input('product_id');

            // Find the wishlist item to delete
            $wishlistItem = Wishlist::where('customer_id', $customerId)
                ->where('product_id', $productId)
                ->first();
    if ($wishlistItem) {
        $wishlistItem->delete();
        return response()->json(['message' => 'wishlist item deleted successfully']);
    }

    return response()->json(['error' => 'wishlist item not found'], 404);
} catch (\Exception $e) {
    Log::error('Error deleting wishlist item: ' . $e->getMessage());
    return response()->json(['message' => 'Failed to delete wishlist item'], 500);
}
}

}