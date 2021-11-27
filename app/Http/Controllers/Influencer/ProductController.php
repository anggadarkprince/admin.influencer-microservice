<?php

namespace App\Http\Controllers\Influencer;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Cache::remember('products', 60 * 30, function () use ($request) {
            return Product::all();
        });

        if (!empty($search = $request->input('q'))) {
            $products = $products->filter(function (Product $product) use ($search) {
                return Str::contains($product->title, $search) || Str::contains($product->description, $search);
            });
        }

        return ProductResource::collection($products);
    }
}
