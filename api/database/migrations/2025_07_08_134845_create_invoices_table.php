<?php

use App\Enums\InvoiceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('seller_id')->nullable();
            $table->uuid('client_id')->nullable();
            $table->enum('status', InvoiceStatus::values())->default(InvoiceStatus::DRAFT->value);
            $table->timestamps();

            $table->foreign('seller_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('client_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
