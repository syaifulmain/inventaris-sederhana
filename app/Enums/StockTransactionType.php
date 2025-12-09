<?php
namespace App\Enums;

enum StockTransactionType: string
{
    case IN = 'IN';
    case OUT = 'OUT';
}
