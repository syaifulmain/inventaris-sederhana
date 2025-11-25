<?php

namespace App\Services;

use App\Models\StockTransaction;

class StockTransactionService extends BaseService
{
    public function __construct(StockTransaction $stockTransaction)
    {
        $this->model = $stockTransaction;
    }
}
