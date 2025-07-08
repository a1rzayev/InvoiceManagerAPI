<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Controllers\Base\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="Product",
 *     required={"name", "unit_price"},
 *     @OA\Property(property="id", type="string", format="uuid", description="Product unique identifier"),
 *     @OA\Property(property="name", type="string", maxLength=255, description="Product name"),
 *     @OA\Property(property="description", type="string", maxLength=1000, nullable=true, description="Product description"),
 *     @OA\Property(property="unit_price", type="number", format="float", minimum=0, description="Product unit price"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Product creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Product last update timestamp")
 * )
 * 
 * @OA\Schema(
 *     schema="ProductCreateRequest",
 *     required={"name", "unit_price"},
 *     @OA\Property(property="name", type="string", maxLength=255, description="Product name"),
 *     @OA\Property(property="description", type="string", maxLength=1000, nullable=true, description="Product description"),
 *     @OA\Property(property="unit_price", type="number", format="float", minimum=0, description="Product unit price")
 * )
 * 
 * @OA\Schema(
 *     schema="ProductUpdateRequest",
 *     @OA\Property(property="name", type="string", maxLength=255, description="Product name"),
 *     @OA\Property(property="description", type="string", maxLength=1000, nullable=true, description="Product description"),
 *     @OA\Property(property="unit_price", type="number", format="float", minimum=0, description="Product unit price")
 * )
 * 
 * @OA\Schema(
 *     schema="ProductSearchRequest",
 *     required={"query"},
 *     @OA\Property(property="query", type="string", minLength=2, maxLength=255, description="Search query")
 * )
 * 
 * @OA\Schema(
 *     schema="ProductPriceRangeRequest",
 *     required={"min_price", "max_price"},
 *     @OA\Property(property="min_price", type="number", format="float", minimum=0, description="Minimum price"),
 *     @OA\Property(property="max_price", type="number", format="float", minimum=0, description="Maximum price")
 * )
 */
class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Get all products",
     *     description="Retrieve a list of all products in the system",
     *     tags={"Products"},
     *     @OA\Response(
     *         response=200,
     *         description="List of products retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Product")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json(Product::all());
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Create a new product",
     *     description="Create a new product with the provided information",
     *     tags={"Products"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ProductCreateRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Get product by ID",
     *     description="Retrieve a specific product by its ID",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product not found")
     *         )
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        $product = Product::find($id);
        if (!$product) 
        {
            return response()->json(['message' => 'Product not found'], 404);
        }
        
        return response()->json($product);
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     summary="Update product",
     *     description="Update an existing product's information",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ProductUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Delete product",
     *     description="Delete a product from the system. Cannot delete if used in invoice items.",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Product cannot be deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cannot delete product. It is used in 5 invoice item(s)."),
     *             @OA\Property(property="invoice_items_count", type="integer", example=5)
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/products/search/query",
     *     summary="Search products",
     *     description="Search products by name or description",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         required=true,
     *         description="Search query (minimum 2 characters)",
     *         @OA\Schema(type="string", minLength=2, maxLength=255)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Products found successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/products/price-range/filter",
     *     summary="Get products by price range",
     *     description="Retrieve products within a specified price range",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="min_price",
     *         in="query",
     *         required=true,
     *         description="Minimum price",
     *         @OA\Schema(type="number", format="float", minimum=0)
     *     ),
     *     @OA\Parameter(
     *         name="max_price",
     *         in="query",
     *         required=true,
     *         description="Maximum price (must be greater than or equal to min_price)",
     *         @OA\Schema(type="number", format="float", minimum=0)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Products found successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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
