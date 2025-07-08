<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Invoice extends Model
{
    use HasFactory, HasUuids;

    protected $fillable =
    [
        'seller_id',
        'client_id',
        'status'
    ];

    protected $casts = 
    [
        'status' => InvoiceStatus::class,
    ];

    public function setStatus(InvoiceStatus $status): void 
    {
        $this->status = $status;
    }

    public function getStatus(): InvoiceStatus
    {
        return $this->status;
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
