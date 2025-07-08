<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\User;
use App\Enums\InvoiceStatus;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

use App\Http\Controllers\Base\Controller;

/**
 * @OA\Schema(
 *     schema="InvoiceItem",
 *     required={"product_id", "quantity", "total_price"},
 *     @OA\Property(property="id", type="string", format="uuid", description="Invoice item unique identifier"),
 *     @OA\Property(property="invoice_id", type="string", format="uuid", description="Invoice ID"),
 *     @OA\Property(property="product_id", type="string", format="uuid", description="Product ID"),
 *     @OA\Property(property="quantity", type="integer", minimum=1, description="Quantity of the product"),
 *     @OA\Property(property="total_price", type="number", format="float", minimum=0, description="Total price for this item"),
 *     @OA\Property(property="product", ref="#/components/schemas/Product", description="Product details"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Invoice item creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Invoice item last update timestamp")
 * )
 * 
 * @OA\Schema(
 *     schema="Invoice",
 *     required={"seller_id", "client_id", "status"},
 *     @OA\Property(property="id", type="string", format="uuid", description="Invoice unique identifier"),
 *     @OA\Property(property="seller_id", type="string", format="uuid", description="Seller user ID"),
 *     @OA\Property(property="client_id", type="string", format="uuid", description="Client user ID"),
 *     @OA\Property(property="status", type="string", enum={"draft", "sent", "paid", "overdue"}, description="Invoice status"),
 *     @OA\Property(property="seller", ref="#/components/schemas/User", description="Seller user details"),
 *     @OA\Property(property="client", ref="#/components/schemas/User", description="Client user details"),
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/InvoiceItem"), description="Invoice items"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Invoice creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Invoice last update timestamp")
 * )
 * 
 * @OA\Schema(
 *     schema="InvoiceCreateRequest",
 *     required={"seller_id", "client_id", "items"},
 *     @OA\Property(property="seller_id", type="string", format="uuid", description="Seller user ID (must be a seller)"),
 *     @OA\Property(property="client_id", type="string", format="uuid", description="Client user ID (must be a client)"),
 *     @OA\Property(property="status", type="string", enum={"draft", "sent", "paid", "overdue"}, description="Invoice status (defaults to draft)"),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         minItems=1,
 *         @OA\Items(
 *             type="object",
 *             required={"product_id", "quantity", "total_price"},
 *             @OA\Property(property="product_id", type="string", format="uuid", description="Product ID"),
 *             @OA\Property(property="quantity", type="integer", minimum=1, description="Quantity of the product"),
 *             @OA\Property(property="total_price", type="number", format="float", minimum=0, description="Total price for this item")
 *         ),
 *         description="Invoice items"
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="InvoiceUpdateRequest",
 *     @OA\Property(property="seller_id", type="string", format="uuid", description="Seller user ID (must be a seller)"),
 *     @OA\Property(property="client_id", type="string", format="uuid", description="Client user ID (must be a client)"),
 *     @OA\Property(property="status", type="string", enum={"draft", "sent", "paid", "overdue"}, description="Invoice status"),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         minItems=1,
 *         @OA\Items(
 *             type="object",
 *             required={"product_id", "quantity", "total_price"},
 *             @OA\Property(property="product_id", type="string", format="uuid", description="Product ID"),
 *             @OA\Property(property="quantity", type="integer", minimum=1, description="Quantity of the product"),
 *             @OA\Property(property="total_price", type="number", format="float", minimum=0, description="Total price for this item")
 *         ),
 *         description="Invoice items"
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="InvoiceStatusUpdateRequest",
 *     required={"status"},
 *     @OA\Property(property="status", type="string", enum={"draft", "sent", "paid", "overdue"}, description="New invoice status")
 * )
 */
class InvoiceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/invoices",
     *     summary="Get all invoices",
     *     description="Retrieve a list of all invoices in the system",
     *     tags={"Invoices"},
     *     @OA\Response(
     *         response=200,
     *         description="List of invoices retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Invoice")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json(Invoice::all());
    }

    /**
     * @OA\Post(
     *     path="/api/invoices",
     *     summary="Create a new invoice",
     *     description="Create a new invoice with seller, client, and items",
     *     tags={"Invoices"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/InvoiceCreateRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Invoice created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Invoice")
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
            'seller_id' => ['required', Rule::exists('users', 'id')->where('role', UserRole::SELLER)],
            'client_id' => ['required', Rule::exists('users', 'id')->where('role', UserRole::CLIENT)],
            'status' => ['sometimes', Rule::in(InvoiceStatus::values())],
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.total_price' => 'required|numeric|min:0'
        ]);

        $validated['status'] = $validated['status'] ?? InvoiceStatus::DRAFT;

        $invoice = Invoice::create([
            'seller_id' => $validated['seller_id'],
            'client_id' => $validated['client_id'],
            'status' => $validated['status']
        ]);

        foreach ($validated['items'] as $item) {
            $invoice->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'total_price' => $item['total_price']
            ]);
        }

        $invoice->load(['seller', 'client', 'items.product']);
        return response()->json($invoice, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/invoices/{id}",
     *     summary="Get invoice by ID",
     *     description="Retrieve a specific invoice by its ID with seller, client, and items details",
     *     tags={"Invoices"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Invoice ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invoice retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Invoice")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invoice not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invoice not found")
     *         )
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        $invoice = Invoice::with(['seller', 'client', 'items.product'])->find($id);
        
        if (!$invoice) 
        {
            return response()->json(['message' => 'Invoice not found'], 404);
        }
        
        return response()->json($invoice);
    }

    /**
     * @OA\Put(
     *     path="/api/invoices/{id}",
     *     summary="Update invoice",
     *     description="Update an existing invoice's information including seller, client, status, and items",
     *     tags={"Invoices"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Invoice ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/InvoiceUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invoice updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Invoice")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invoice not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invoice not found")
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
        $invoice = Invoice::find($id);
        if (!$invoice) 
        {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        $validated = $request->validate([
            'seller_id' => ['sometimes', Rule::exists('users', 'id')->where('role', UserRole::SELLER)],
            'client_id' => ['sometimes', Rule::exists('users', 'id')->where('role', UserRole::CLIENT)],
            'status' => ['sometimes', Rule::in(InvoiceStatus::values())],
            'items' => 'sometimes|array|min:1',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.total_price' => 'required_with:items|numeric|min:0'
        ]);

        $invoice->update(array_filter($validated, function($key) {
            return in_array($key, ['seller_id', 'client_id', 'status']);
        }, ARRAY_FILTER_USE_KEY));

        if (isset($validated['items'])) {
            $invoice->items()->delete();
            foreach ($validated['items'] as $item) {
                $invoice->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'total_price' => $item['total_price']
                ]);
            }
        }

        $invoice->load(['seller', 'client', 'items.product']);
        return response()->json($invoice);
    }

    /**
     * @OA\Delete(
     *     path="/api/invoices/{id}",
     *     summary="Delete invoice",
     *     description="Delete an invoice and all its items from the system",
     *     tags={"Invoices"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Invoice ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invoice deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invoice deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invoice not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invoice not found")
     *         )
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $invoice = Invoice::find($id);
        if (!$invoice) 
        {
            return response()->json(['message' => 'Invoice not found'], 404);
        }
        $invoice->items()->delete();
        $invoice->delete();
        
        return response()->json(['message' => 'Invoice deleted successfully']);
    }

    /**
     * @OA\Get(
     *     path="/api/invoices/status/{status}",
     *     summary="Get invoices by status",
     *     description="Retrieve all invoices with a specific status",
     *     tags={"Invoices"},
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         required=true,
     *         description="Invoice status",
     *         @OA\Schema(type="string", enum={"draft", "sent", "paid", "overdue"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invoices found successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Invoice")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid status",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid status")
     *         )
     *     )
     * )
     */
    public function getByStatus(string $status): JsonResponse
    {
        if (!in_array($status, InvoiceStatus::values())) 
        {
            return response()->json(['message' => 'Invalid status'], 400);
        }
        $invoices = Invoice::with(['seller', 'client', 'items.product'])->where('status', $status)->get();
            
        return response()->json($invoices);
    }

    /**
     * @OA\Get(
     *     path="/api/invoices/seller/{sellerId}",
     *     summary="Get invoices by seller",
     *     description="Retrieve all invoices created by a specific seller",
     *     tags={"Invoices"},
     *     @OA\Parameter(
     *         name="sellerId",
     *         in="path",
     *         required=true,
     *         description="Seller user ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invoices found successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Invoice")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Seller not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Seller not found")
     *         )
     *     )
     * )
     */
    public function getBySeller(string $sellerId): JsonResponse
    {
        $seller = User::where('id', $sellerId)->where('role', UserRole::SELLER)->first();
        if (!$seller) 
        {
            return response()->json(['message' => 'Seller not found'], 404);
        }
        $invoices = Invoice::with(['seller', 'client', 'items.product'])->where('seller_id', $sellerId)->get();
            
        return response()->json($invoices);
    }

    /**
     * @OA\Get(
     *     path="/api/invoices/client/{clientId}",
     *     summary="Get invoices by client",
     *     description="Retrieve all invoices for a specific client",
     *     tags={"Invoices"},
     *     @OA\Parameter(
     *         name="clientId",
     *         in="path",
     *         required=true,
     *         description="Client user ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invoices found successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Invoice")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Client not found")
     *         )
     *     )
     * )
     */
    public function getByClient(string $clientId): JsonResponse
    {
        $client = User::where('id', $clientId)->where('role', UserRole::CLIENT)->first();
        if (!$client) 
        {
            return response()->json(['message' => 'Client not found'], 404);
        }
        $invoices = Invoice::with(['seller', 'client', 'items.product'])->where('client_id', $clientId)->get();
            
        return response()->json($invoices);
    }

    /**
     * @OA\Patch(
     *     path="/api/invoices/{id}/status",
     *     summary="Update invoice status",
     *     description="Update only the status of an existing invoice",
     *     tags={"Invoices"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Invoice ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/InvoiceStatusUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invoice status updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Invoice")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invoice not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invoice not found")
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
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $invoice = Invoice::find($id);
        if (!$invoice) 
        {
            return response()->json(['message' => 'Invoice not found'], 404);
        }
        $validated = $request->validate([
            'status' => ['required', Rule::in(InvoiceStatus::values())]
        ]);
        $invoice->update(['status' => $validated['status']]);
        $invoice->load(['seller', 'client', 'items.product']);

        return response()->json($invoice);
    }
}
