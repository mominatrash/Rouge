<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\User;
use App\Models\Address;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\GiftPackaging;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $iscart = Cart::where('user_id', Auth::guard('api')->user()->id)->where('product_id', $request->product_id)->where('color_id', $request->color_id)->where('status',0)->first();

        if ($iscart) {
            $iscart->quantity = $iscart->quantity + $request->quantity;
            $iscart->save();
            $iscart->total = ($iscart->price * $iscart->quantity) + $iscart->delivery_fee;
            $iscart->save();

            return response()->json([
                'message' => 'Added to cart',
                'code' => 200,
                'status' => true,
                'data' => $iscart

            ]);
        } else {
            $cart = new Cart();
            $cart->user_id = Auth::guard('api')->user()->id;
            $cart->product_id = $request->product_id;
            $cart->quantity = $request->quantity;
            $cart->color_id = $request->color_id;
            $cart->price = Product::where('id', $request->product_id)->value('price');
            $cart->status = 0;
            $cart->save();
            $cart->refresh();
            $cart->total = ($cart->price * $cart->quantity) + $cart->delivery_fee;
            $cart->save();

            return response()->json([
                'message' => 'Added to cart',
                'code' => 200,
                'status' => true,
                'data' => $cart

            ]);
        }
    }


    public function my_cart(Request $request)
    {
        $data = [];

        Cart::where('user_id', Auth::guard('api')->user()->id)->where('quantity', '<=', 0)->delete();

        $my_cart = Cart::where('user_id', Auth::guard('api')->user()->id)->where('status',0)->with('product')->with('color')->get()->makeHidden(['created_at', 'updated_at']);

        if ($my_cart->count() > 0) {

            $subtotals = $my_cart->sum(function ($cart) {
                return $cart->product->price * $cart->quantity;
            });
            if ($request->has('gift_packaging')){

                $gift = GiftPackaging::first();
                

            $total = $my_cart->sum('total') + $gift->price;
            $check['Gift Packiging'] = $gift->price;
        }else{

            $total = $my_cart->sum('total');
        }

           
            $delivery_fee = $my_cart->sum('delivery_fee');

            $data['cart'] = $my_cart;
            $data['SubTotal'] = $subtotals;
            $data['Delivery Fee'] = $delivery_fee;
            $data['Total'] = $total;
            



            return response()->json([
                'message' => 'Data fetched successfully',
                'code' => 200,
                'status' => true,
                'Order' => $data,
            ]);
        } else {

            return response()->json([
                'message' => 'Your cart is empty!',
                'code' => 404,
                'status' => false,
            ]);
        }
    }
}



    // public function my_cart()
    // {
    //     $carts = [];
    //     $cartItems = Cart::where('user_id', Auth::guard('api')->user()->id)
    //         ->get()
    //         ->makeHidden(['created_at', 'updated_at']);

    //     $total = 0;

    //     foreach ($cartItems as $item) {
    //         $cart = Product::where('id', $item->product_id)->first();
    //         $carts[] = $cart;
    //     }

    //     return response()->json([
    //         'message' => 'Data fetched successfully',
    //         'code' => 200,
    //         'status' => true,
    //         'data' => $carts
    //     ]);
    // }
