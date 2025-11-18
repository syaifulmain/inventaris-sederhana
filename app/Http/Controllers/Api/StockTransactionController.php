<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StockTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = StockTransaction::with(['product', 'supplier', 'user']);

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        // Optional pagination
        $transactions = $query->latest()->paginate(
            $request->get('per_page', 20)
        );

        return response()->json($transactions, 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id'        => ['required', 'exists:products,id'],
            'supplier_id'       => ['nullable', 'exists:suppliers,id'],
            'type'              => ['required', Rule::in(['in', 'out'])],
            'quantity'          => ['required', 'integer', 'min:1'],
            'description'       => ['required', 'string'],
            'transaction_date'  => ['required', 'date'],
            'user_id'           => ['required', 'exists:users,id'],
        ]);

        $transaction = StockTransaction::create($validated);

        return response()->json($transaction->load(['product', 'supplier', 'user']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(StockTransaction $stockTransaction)
    {
        return response()->json($stockTransaction->load(['product', 'supplier', 'user']), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StockTransaction $stockTransaction)
    {
        $validated = $request->validate([
            'product_id'        => ['sometimes', 'exists:products,id'],
            'supplier_id'       => ['nullable', 'exists:suppliers,id'],
            'type'              => ['sometimes', Rule::in(['in', 'out'])],
            'quantity'          => ['sometimes', 'integer', 'min:1'],
            'description'       => ['sometimes', 'string'],
            'transaction_date'  => ['sometimes', 'date'],
            'user_id'           => ['sometimes', 'exists:users,id'],
        ]);

        $stockTransaction->update($validated);

        return response()->json($stockTransaction->load(['product', 'supplier', 'user']), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockTransaction $stockTransaction)
    {
        $stockTransaction->delete();

        return response()->json([
            'message' => 'Deleted successfully'
        ], 200);
    }
}
