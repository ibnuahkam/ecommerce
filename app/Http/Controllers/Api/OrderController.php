<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Midtrans\Snap;
use Midtrans\Config;


class OrderController extends Controller
{
    public function buy(Request $request)
    {
        $request->validate([
            'product_uuid' => 'required|exists:products,uuid',
            'quantity' => 'required|integer|min:1'
        ]);

        $user = auth()->user();

        if ($user->role->code !== 'buyer') {
            return response()->json([
                'status' => false,
                'message' => 'Only buyer can purchase products'
            ], 403);
        }

        $product = Product::where('uuid', $request->product_uuid)->first();

        if ($product->user_id === $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'You cannot purchase your own product'
            ], 403);
        }

        if ($product->stock < $request->quantity) {
            return response()->json([
                'status' => false,
                'message' => 'Stock not enough'
            ], 400);
        }

        DB::beginTransaction();

        try {

            $subtotal = $product->price * $request->quantity;

            $order = Order::create([
                'buyer_id' => $user->id,
                'total_amount' => $subtotal,
                'status' => 'pending'
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'price' => $product->price,
                'quantity' => $request->quantity,
                'subtotal' => $subtotal
            ]);

            Config::$serverKey = env('SB-Mid-server-wnqUdfRZ7q0ZuIILkMJt6_8K');
            Config::$isProduction = false;
            Config::$isSanitized = true;
            Config::$is3ds = true;

            $params = [
                'transaction_details' => [
                    'order_id' => $order->uuid,
                    'gross_amount' => (int) $subtotal,
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            $order->update([
                'snap_token' => $snapToken
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'order_uuid' => $order->uuid,
                    'snap_token' => $snapToken
                ]
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function callback(Request $request)
    {
        $order = Order::where('uuid', $request->order_id)->first();

        if ($request->transaction_status == 'settlement') {
            $order->update([
                'status' => 'paid'
            ]);
        }

        if ($request->transaction_status == 'expire') {
            $order->update([
                'status' => 'expired'
            ]);
        }
    }

}
