<?php

namespace App\Models;

use App\Enums\StockTransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StockTransaction extends Model
{
    /** @use HasFactory<\Database\Factories\StockTransactionFactory> */
    use HasFactory;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($stockTransaction) {
            if (empty($stockTransaction->transaction_code)) {
                $stockTransaction->transaction_code = self::generateTransactionCode();
            }
        });
    }

    public static function generateTransactionCode()
    {
        $prefix = 'TR';
        $date = now()->format('Ymd');
        
        // Get the last transaction code for today
        $lastTransaction = self::whereDate('created_at', now()->toDateString())
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastTransaction && preg_match('/TR' . $date . '(\d{4})/', $lastTransaction->transaction_code, $matches)) {
            $sequence = intval($matches[1]) + 1;
        } else {
            $sequence = 1;
        }
        
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    protected $casts = [
        'type' => StockTransactionType::class,
        'transaction_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
