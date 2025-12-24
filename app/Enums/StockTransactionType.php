<?php
namespace App\Enums;

enum StockTransactionType: string
{
    case in = 'in';
    case out = 'out';
}
