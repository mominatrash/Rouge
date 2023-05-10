<?php

namespace App\Http\Controllers;

use App\Models\Welcome;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function welcome()
    {
        $welcome = Welcome::where('type', '=', 'welcome')->get();
        if ($welcome->count() > 0) {

            return response()->json([
                'message' => 'data fetched successfully',
                'code' => 200,
                'status' => true,
                'orders' => $welcome
            ]);
        } else {
            return response()->json([
                'message' => 'No Welcome data found!',
                'code' => 500,
                'status' => false,
            ]);
        }
    }

    public function banner()
    {
        $banner = Welcome::where('type', '=', 'banner')->get();
        if ($banner->count() > 0) {

            return response()->json([
                'message' => 'data fetched successfully',
                'code' => 200,
                'status' => true,
                'orders' => $banner
            ]);
        } else {
            return response()->json([
                'message' => 'No orders found!',
                'code' => 500,
                'status' => false,
            ]);
        }
    }


    public function privacy()
    {
        $privacy = Welcome::where('type', '=', 'privacy')->get();
        if ($privacy->count() > 0) {

            return response()->json([
                'message' => 'data fetched successfully',
                'code' => 200,
                'status' => true,
                'orders' => $privacy
            ]);
        } else {
            return response()->json([
                'message' => 'No orders found!',
                'code' => 500,
                'status' => false,
            ]);
        }
    }

    public function about_us()
    {
        $about_us = Welcome::where('type', '=', 'aboutus')->get();
        if ($about_us->count() > 0) {

            return response()->json([
                'message' => 'data fetched successfully',
                'code' => 200,
                'status' => true,
                'orders' => $about_us
            ]);
        } else {
            return response()->json([
                'message' => 'No orders found!',
                'code' => 500,
                'status' => false,
            ]);
        }
    }
}
