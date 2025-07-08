<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Controllers\Base\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(Product::all());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:products,name',
            'description' => 'nullable|string|max:1000',
            'unit_price' => 'required|numeric|min:0'
        ]);
        $product = Product::create($validated);

        return response()->json($product, 201);
    }


    public function show(string $id): JsonResponse
    {
        $product = Product::find($id);
        if (!$product) 
        {
            return response()->json(['message' => 'Product not found'], 404);
        }
        
        return response()->json($product);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $product = Product::find($id);
        if (!$product) 
        {
            return response()->json(['message' => 'Product not found'], 404);
        }
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('products')->ignore($product->id)],
            'description' => 'nullable|string|max:1000',
            'unit_price' => 'sometimes|numeric|min:0'
        ]);
        $product->update($validated);
        
        return response()->json($product);
    }

    public function destroy(string $id): JsonResponse
    {
        $product = Product::find($id);
        if (!$product) 
        {
            return response()->json(['message' => 'Product not found'], 404);
        }
        $invoiceItemsCount = $product->invoiceItems()->count();
        if ($invoiceItemsCount > 0) {
            return response()->json([
                'message' => 'Cannot delete product. It is used in ' . $invoiceItemsCount . ' invoice item(s).',
                'invoice_items_count' => $invoiceItemsCount
            ], 409);
        }
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'required|string|min:2|max:255'
        ]);
        $products = Product::where('name', 'like', '%' . $validated['query'] . '%')
            ->orWhere('description', 'like', '%' . $validated['query'] . '%')
            ->get();
            
        return response()->json($products);
    }

    public function getByPriceRange(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'min_price' => 'required|numeric|min:0',
            'max_price' => 'required|numeric|min:0|gte:min_price'
        ]);
        $products = Product::whereBetween('unit_price', [
            $validated['min_price'], 
            $validated['max_price']
        ])->get();
            
        return response()->json($products);
    }
}
