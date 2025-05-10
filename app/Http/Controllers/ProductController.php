<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
     
    public function show($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json($product);
    }

    public function approveProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['status' => 'approved']);
        return response()->json(['message' => 'Product approved successfully']);
    }

    public function rejectProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['status' => 'rejected']);
        return response()->json(['message' => 'Product rejected successfully']);
    }

    public function getPendingProducts()
    {
        $pendingProducts = Product::where('status', 'pending')->get();
        return response()->json($pendingProducts);
    }

    public function getPendingProductsCount()
    {
        $pendingProductsCount = Product::where('status', 'pending')->count();
        return response()->json(['pendingProductsCount' => $pendingProductsCount]);
    }

    public function index()
    {
        $products = Product::where('status', 'approved')->get();
        return response()->json($products);

    
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'price' => 'required|numeric',
            'images' => 'nullable|array',
            'images.*' => 'string',
        ]);

        $imagePaths = [];

        if ($request->has('images')) {
            foreach ($request->images as $imageBase64) {
                if (!preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $imageBase64)) {
                    return response()->json(['success' => false, 'message' => 'Invalid image format'], 422);
                }

                $image = str_replace('data:image/png;base64,', '', $imageBase64);
                $image = str_replace('data:image/jpeg;base64,', '', $image);
                $image = str_replace('data:image/jpg;base64,', '', $image);
                $image = str_replace(' ', '+', $image);

                $imageName = uniqid() . '.png';
                File::put(public_path('uploads/products/' . $imageName), base64_decode($image));
                $imagePaths[] = 'uploads/products/' . $imageName;
            }
        }

        $product = Product::create([
            'vendor_id' => $request->vendor_id,
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'price' => $request->price,
            'images' => json_encode($imagePaths),
        ]);

        return response()->json(['success' => true, 'message' => 'Product added successfully', 'product' => $product]);
    }

    public function getVendorProducts($vendorId)
    {
        try {
            $products = Product::where('vendor_id', $vendorId)
                ->where('status', 'approved')
                ->get();

            return response()->json(['success' => true, 'products' => $products]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error fetching vendor products', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateProduct(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'price' => 'required|numeric',
            'images' => 'nullable|array',
            'images.*' => 'file|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $product = Product::find($request->id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found']);
        }

        $newImages = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/products'), $imageName);
                $newImages[] = 'uploads/products/' . $imageName;
            }
        }

        $existingImages = $request->input('existing_images', []);
        if (!is_array($existingImages)) {
            $existingImages = [];
        }

        $allImages = array_merge($existingImages, $newImages);

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'price' => $request->price,
            'images' => json_encode($allImages),
        ]);

        return response()->json(['success' => true, 'message' => 'Product updated successfully', 'product' => $product]);
    }

    public function searchProducts(Request $request)
{
    try {
        // Extract query parameters
        $query = $request->input('query'); // Search term
        $category = $request->input('category'); // Filter by category
        $minPrice = $request->input('min_price'); // Minimum price
        $maxPrice = $request->input('max_price'); // Maximum price

        // Start building the query
        $products = Product::query();

        // Apply search query (name or description)
        if ($query) {
            $products->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('category', 'like', "%{$query}%");;
            });
        }

        // Apply category filter
        if ($category) {
            $products->where('category', $category);
        }

        // Apply price range filter
        if ($minPrice !== null && $maxPrice !== null) {
            $products->whereBetween('price', [(float) $minPrice, (float) $maxPrice]);
        } elseif ($minPrice !== null) {
            $products->where('price', '>=', (float) $minPrice);
        } elseif ($maxPrice !== null) {
            $products->where('price', '<=', (float) $maxPrice);
        }

        // Fetch results
        $results = $products->get();

        return response()->json(['success' => true, 'products' => $results], 200);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Failed to fetch products: ' . $e->getMessage()], 500);
    }
}
public function getUniqueCategories()
{
    try {
        // Fetch unique categories from the products table
        $categories = Product::distinct()->pluck('category')->filter()->values();

        return response()->json(['success' => true, 'categories' => $categories], 200);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Failed to fetch categories: ' . $e->getMessage()], 500);
    }
}
public function getSuggestedProducts()
{
    try {
        // Fetch 4 random products (or use any other logic for suggestions)
        $suggestedProducts = Product::inRandomOrder()->limit(4)->get();

        return response()->json(['success' => true, 'products' => $suggestedProducts], 200);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Failed to fetch suggested products: ' . $e->getMessage()], 500);
    }
}
}