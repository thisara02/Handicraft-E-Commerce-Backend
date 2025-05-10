<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{

    public function getReviewsByProduct($productId)
    {
        try {
            $reviews = Review::where('product_id', $productId)->get();

            return response()->json(['success' => true, 'reviews' => $reviews]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch reviews'], 500);
        }
    }


    public function getReviews($productId)
    {
        try {
            // Fetch reviews for the given product ID
            $reviews = Review::with('customer') // Eager load the customer relationship
                             ->where('product_id', $productId)
                             ->get();

            return response()->json($reviews);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch reviews'], 500);
        }
    }

    public function addReview(Request $request)
    {
        
        try {
            // Validate the request
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'product_id' => 'required|exists:products,id',
                'review' => 'required|string',
                'rating' => 'required|integer|min:1|max:5',
            ]);

            // Save the review
            $review = Review::create([
                'customer_id' => $validated['customer_id'],
                'product_id' => $validated['product_id'],
                'review' => $validated['review'],
                'rating' => $validated['rating'],
            ]);

            // Log success
            Log::info('Review added successfully:', ['review' => $review]);

            return response()->json(['message' => 'Review added successfully'], 201);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error adding review:', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Failed to add review'], 500);
        }
    }
}