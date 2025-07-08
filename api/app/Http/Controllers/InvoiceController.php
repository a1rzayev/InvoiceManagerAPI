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

class InvoiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(Invoice::all());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'seller_id' => ['required', 'exists:users,id', Rule::exists('users')->where('role', UserRole::SELLER)],
            'client_id' => ['required', 'exists:users,id', Rule::exists('users')->where('role', UserRole::CLIENT)],
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

    public function show(string $id): JsonResponse
    {
        $invoice = Invoice::with(['seller', 'client', 'items.product'])->find($id);
        
        if (!$invoice) 
        {
            return response()->json(['message' => 'Invoice not found'], 404);
        }
        
        return response()->json($invoice);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $invoice = Invoice::find($id);
        if (!$invoice) 
        {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        $validated = $request->validate([
            'seller_id' => ['sometimes', 'exists:users,id', Rule::exists('users')->where('role', UserRole::SELLER)],
            'client_id' => ['sometimes', 'exists:users,id', Rule::exists('users')->where('role', UserRole::CLIENT)],
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

    public function getByStatus(string $status): JsonResponse
    {
        if (!in_array($status, InvoiceStatus::values())) 
        {
            return response()->json(['message' => 'Invalid status'], 400);
        }
        $invoices = Invoice::with(['seller', 'client', 'items.product'])->where('status', $status)->get();
            
        return response()->json($invoices);
    }

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
