<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Address;
use App\Models\OrderReview;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        $check = [];
        $my_cart = Cart::where('user_id', Auth::guard('api')->user()->id)->where('status', 0)->get();
        if ($my_cart->count() > 0) {


            $user = User::where('id', Auth::guard('api')->user()->id)->first();

            $address = Address::where('user_id', Auth::guard('api')->user()->id)->where('selected', 1)->first();

            if ($my_cart->count() > 0) {
                $subtotal = $my_cart->sum(function ($cart) {
                    return $cart->product->price * $cart->quantity;
                });

                $total = $my_cart->sum('total');
                $delivery_fee = $my_cart->sum('delivery_fee');

                $check['Receiver'] = $user->name;
                $check['Phone'] = $user->phone;
                $check['Governorate'] = $address->governorate;
                $check['Address'] = $address->address;
                $check['Nearby'] = $address->nearby;
                $check['Payment Method'] = $request->payment_method;
                $check['Coupon'] = $request->coupone;
                $check['Sub Total'] = $subtotal;
                $check['Delivery Fee'] = $delivery_fee;
                $check['Total'] = $total;


                return response()->json([
                    'message' => 'Data fetched successfully',
                    'code' => 200,
                    'status' => true,
                    'data' => $check,

                ]);
            }
        }
    }


    public function place_order(Request $request)
    {
        try {
            DB::beginTransaction();

            $address = Address::where('user_id', Auth::guard('api')->user()->id)->where('selected', 1)->first();
            $my_cart = Cart::where('user_id', Auth::guard('api')->user()->id)->where('status', 0)->get();
            if ($my_cart->count() > 0) {
                $total = $my_cart->sum('total');

                $order = new Order();
                $order->reciever = Auth::guard('api')->user()->name;
                $order->phone = Auth::guard('api')->user()->phone;
                $order->governorate = $address->governorate;
                $order->address = $address->address;
                $order->nearby_place = $address->nearby_place;

                $maxAttempts = 100;
                $attempt = 1;

                do {
                    $orderNo = rand(100000, 999999);
                    $existingOrder = Order::where('order_no', $orderNo)->exists();

                    if (!$existingOrder) {
                        $order->order_no = $orderNo;
                        break;
                    }

                    $attempt++;
                } while ($attempt <= $maxAttempts);

                $order->order_price = $total;
                $order->items_count = $my_cart->sum('quantity');
                $order->save();

                foreach ($my_cart as $c) {
                    $c->order_id = $order->id;
                    $c->status = 1;
                    $c->save();
                }

                DB::commit();

                return response()->json([
                    'message' => 'Order placed successfully',
                    'code' => 200,
                    'status' => true,
                ]);
            } else {

                return response()->json([
                    'message' => 'Failed to place the order',
                    'code' => 500,
                    'status' => false,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Failed to place the order',
                'code' => 500,
                'status' => false,
            ]);
        }
    }


    public function OnProgressOrders()
    {
        $onprogress = Order::where('order_status', '!=', 'completed')->get();
        if ($onprogress->count() > 0) {

            return response()->json([
                'message' => 'data fetched successfully',
                'code' => 200,
                'status' => true,
                'orders' => $onprogress
            ]);
        } else {
            return response()->json([
                'message' => 'No orders found!',
                'code' => 500,
                'status' => false,
            ]);
        }
    }


    public function CompletedOrders()
    {
        $completed = Order::where('order_status', '=', 'completed')->get();
        if ($completed->count() > 0) {

            return response()->json([
                'message' => 'data fetched successfully',
                'code' => 200,
                'status' => true,
                'orders' => $completed
            ]);
        } else {
            return response()->json([
                'message' => 'No orders found!',
                'code' => 500,
                'status' => false,
            ]);
        }
    }


    public function Order_by_id(Request $request)
    {
        $order = Order::where('id', $request->id)->with('cart.product', 'cart.color')->get();

        if ($order->count() > 0) {

            return response()->json([
                'message' => 'data fetched successfully',
                'code' => 200,
                'status' => true,
                'orders' => $order
            ]);
        } else {
            return response()->json([
                'message' => 'No orders found!',
                'code' => 500,
                'status' => false,
            ]);
        }
    }


    public function order_review(Request $request)
    {
        $order = Order::where('id', $request->id)->with('cart')->first();


        $order->delivery_service = $request->delivery_service;
        $order->feedback = $request->feedback;
        $order->save();

        $my_cart = Cart::where('order_id', $request->id)->get();

        if ($my_cart->count() > 0) {
            $d = [];

            $productReviews = json_decode($request->product_reviews, true);

            foreach ($productReviews as $item) {
                $cartId = $item['cart_id'];
                $productReview = $item['product_review'];


                $cartItem = $my_cart->where('id', $cartId)->first();

                if ($cartItem) {

                    $cartItem->product_review = $productReview;
                    $cartItem->save();
                }
            }
            
            // Retrieve the updated cart items
            $my_cart = Cart::where('order_id', $request->id)->get();
            
            // Build the response data
            foreach ($my_cart as $cart) {
                $d[] = [
                    'order_id' => $cart->order_id,
                    'product_id' => $cart->product_id,
                    'delivery_fee' => $cart->delivery_fee,
                    'product_review' => $cart->product_review,
                ];
            }
            
            return response()->json([
                'message' => 'Review submitted successfully',
                'code' => 200,
                'status' => true,
                'orders' => $d
            ]);
        } else {
            return response()->json([
                'message' => 'Failed to submit review!',
                'code' => 500,
                'status' => false,
            ]);
        }
    }
}

// foreach ($my_cart as $index => $cart) {
//     if (isset($request->product_reviews[$index])) {
//         $cart->product_review = $request->product_reviews[$index];
//         $cart->save();
//         $d[] = [
//             'order_id' => $cart->order_id,
//             'product_id' => $cart->product_id,
//             'delivery_fee' => $cart->delivery_fee,
//             'product_review' => $cart->product_review,
//         ];
//     }
// }