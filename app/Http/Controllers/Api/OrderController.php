<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class OrderController extends Controller
{
    public function store(Request $request)
{
    // Validate input
    $request->validate([
        'transaction_time' => 'required',
        'kasir_id' => 'required|exists:users,id',
        'total_price' => 'required|numeric',
        'total_item' => 'required|numeric',
        'order_items' => 'required|array',
        'order_items.*.product_id' => 'required|exists:products,id',
        'order_items.*.quantity' => 'required|numeric|min:1',
        'order_items.*.total_price' => 'required|numeric',
    ]);

    // Begin database transaction
    DB::beginTransaction();

    try {
        // Create the order
        $order = \App\Models\Order::create([
            'transaction_time' => $request->transaction_time,
            'kasir_id' => $request->kasir_id,
            'total_price' => $request->total_price,
            'total_item' => $request->total_item,
            'payment_method' => $request->payment_method,
        ]);

        // Prepare for stock validation
        $errorMessages = [];

        // Process each order item
        foreach ($request->order_items as $item) {
            // Find the product
            $product = Product::find($item['product_id']);

            // Check stock
            if ($product->stock < $item['quantity']) {
                $errorMessages[] = "Product '{$product->name}' (ID: {$product->id}) has insufficient stock. Available: {$product->stock}, Requested: {$item['quantity']}";
            }
        }

        // If any stock is insufficient, abort transaction
        if (count($errorMessages) > 0) {
            DB::rollBack(); // Rollback transaction
            return response()->json([
                'success' => false,
                'message' => 'Order creation failed due to insufficient stock',
                'errors' => $errorMessages
            ], 400);
        }

        // Create order items and update stock
        foreach ($request->order_items as $item) {
            $product = Product::find($item['product_id']);

            // Create order item
            \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'total_price' => $item['total_price'],
            ]);

            // Deduct stock
            $product->update([
                'stock' => $product->stock - $item['quantity']
            ]);
        }

        // Commit transaction
        DB::commit();

        // Response success
        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'order' => $order
        ], 201);

    } catch (\Exception $e) {
        // Rollback transaction in case of error
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Order creation failed',
            'error' => $e->getMessage()
        ], 500);
    }
}

}
