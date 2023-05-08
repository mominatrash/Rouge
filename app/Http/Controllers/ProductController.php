<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Color;
use App\Models\Image;
use App\Models\Product;
use App\Models\Whitelist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function categories()
    {
        $categories = Category::get(['image', 'category_name']);
        if ($categories) {
            return response()->json([
                'message' => 'data fetched successfully',
                'code' => 200,
                'data' => $categories,
            ]);
        } else {

            return response()->json(
                [
                    "errors" => [
                        "data" => [
                            "No categories found!"
                        ]
                    ],
                    "status" => false,
                    'code' => 404,
                ]
            );
        }
    }

    public function category_by_id(Request $request)
    {
        $categories = Category::where('id', $request->id)->first();;

        if ($categories) {



            $products = Product::where('category_id', $request->id)->get();
            $categories->products = $products;

            return response()->json([
                'message' => 'Data fetched successfully',
                'code' => 200,
                'data' => $categories,
            ]);
        } else {
            return response()->json([
                'errors' => [
                    'data' => [
                        'No categories found!',
                    ],
                ],
                'status' => false,
                'code' => 404,
            ]);
        }
    }




    public function categoriesWithproducts()
    {
        $categories = Category::get();

        if ($categories->count() > 0) {
            $data = [];

            foreach ($categories as $category) {
                $products = Product::where('category_id', $category->id)->get(['id', 'image', 'product_name', 'price']);
                $category->products = $products;
                $data[] = $category;
            }

            return response()->json([
                'message' => 'Data fetched successfully',
                'code' => 200,
                'data' => $data,
            ]);
        } else {
            return response()->json([
                'errors' => [
                    'data' => [
                        'No categories found!',
                    ],
                ],
                'status' => false,
                'code' => 404,
            ]);
        }
    }











    public function get_product_by_id(Request $request)
    {
        $product = Product::with('category:id,category_name')->find($request->id);
        if ($product) {
            $colors = Color::where('product_id', $request->id)->get('color_name');
            $simillar = Product::where('category_id', $product->category_id)->where('id', '!=', $request->id)->get(['product_name', 'image', 'price']);
            $images = Image::where('product_id', $product->id)->get('image');


            $product['category_name'] = $product->category->category_name;
            unset($product->category);

            $product['colors'] = $colors;
            $product['simillar products'] = $simillar;
            $product['images'] = $images;

            return response()->json([
                'message' => 'Data fetched successfully',
                'code' => 200,
                'product' => $product,
            ]);
        } else {
            return response()->json([
                'message' => 'Product not found',
                'code' => 404,
            ]);
        }
    }





    public function add_whitelist(Request $request)
    {


        $isfav = Whitelist::where('user_id', Auth::guard('api')->user()->id)->where('product_id', $request->product_id)->first();
        //$game = Game::where('user_id',Auth::guard('api')->user()->id)->where('game_id', $request->game_id)->first(['name']);
        if ($isfav) {
            $isfav->delete();

            return response([
                'status' => true,
                'message' => 'Game ' . $request->product_id . ' has been removed from favourites',
            ]);
        } else {

            // if($request->game_id == Favourite::where('user_id', Auth::guard('api')->user()->id));

            $fv = new Whitelist();
            $fv->user_id = Auth::guard('api')->user()->id;
            $fv->product_id = $request->product_id;
            $fv->save();

            return response()->json([
                'message' => 'game added to favourites successfully',
                'code' => 200,
                'game' => $fv,
            ]);
        }
    }

    public function myWhiteList()
    {
        $fp = Whitelist::where('user_id', Auth::guard('api')->user()->id)->get();

        if ($fp->count() > 0) {
            $favs = [];
            foreach ($fp as $item) {
                $fav = Product::where('id', $item->product_id)->first();
                $favs[] = $fav;
            }

            return response()->json([
                'message' => 'Data fetched successfully',
                'code' => 200,
                'product' => $favs,
            ]);
        } else {
            return response()->json([
                "errors" => [
                    "message" => [
                        "Your whitelist is empty!"
                    ]
                ],
                "status" => false,
                'code' => 404,
            ]);
        }
    }

    public function search_in_category(Request $request)
    {
        $search = Product::where('category_id',$request->category_id)->where('product_name', 'like', '%' . $request->product_name . '%')->get();
        
        if ($search->count() > 0) {
            return response()->json([
                'message' => 'Data fetched successfully',
                'code' => 200,
                'data' => $search
            ]);
        } else {
            return response()->json([
                'message' => 'No data found',
                'code' => 404
            ]);
        }
    }


    public function search(Request $request)
    {
        $search = Product::where('product_name', 'like', '%' . $request->product_name . '%')->get();
        
        if ($search->count() > 0) {
            return response()->json([
                'message' => 'Data fetched successfully',
                'code' => 200,
                'data' => $search
            ]);
        } else {
            return response()->json([
                'message' => 'No data found',
                'code' => 404
            ]);
        }
    }
    
}
