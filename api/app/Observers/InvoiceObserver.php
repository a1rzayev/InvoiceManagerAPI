<?php

namespace App\Observers;

use App\Models\Invoice;
use Illuminate\Support\Facades\Log;

class InvoiceObserver
{
    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        Log::info("Invoice(seller_id: $invoice->seller_id, client_id: $invoice->client_id) has been created");
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        Log::info("Invoice(seller_id: $invoice->seller_id, client_id: $invoice->client_id) has been updated");
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        Log::info("Invoice(seller_id: $invoice->seller_id, client_id: $invoice->client_id) has been deleted");
    }

    /**
     * Handle the Invoice "restored" event.
     */
    public function restored(Invoice $invoice): void
    {
        Log::info("Invoice(seller_id: $invoice->seller_id, client_id: $invoice->client_id) has been restored");
    }

    /**
     * Handle the Invoice "force deleted" event.
     */
    public function forceDeleted(Invoice $invoice): void
    {
        Log::info("Invoice(seller_id: $invoice->seller_id, client_id: $invoice->client_id) has been deleted pernamently");
    }
}
