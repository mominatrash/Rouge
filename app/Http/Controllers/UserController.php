<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Address;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'phone'    => 'required|numeric|unique:users',
            'governorate' => 'required',
            'address' => 'required',
            'password' => 'required|min:8'

        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'validation error',
                'code' => 400,
                'errors' => $validator->errors(),
            ], 400);
        }


        $user = new User();

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->password = bcrypt($request->password);
        $user->save();

        $address = new Address();
        $address->user_id = $user->id;
        $address->governorate = $request->governorate;
        $address->address = $request->address;
        $address->selected = 1;
        $address->save();



        $user['token'] = $user->createToken('accessToken')->accessToken;


        return response()->json([
            'message' => 'data fetched successfully',
            'code' => 200,
            'data' => $user,
            'address' => $address,
        ]);
    }

    public function login(Request $request)
    {

        $loginData = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required',
        ]);

        if ($loginData->fails()) {
            $errors = $loginData->errors();

            return response([
                'status' => false,
                'message' => 'Make sure that the information is correct and fill in all fields',
                'errors' => $errors,
                'code' => 422
            ]);
        }


        $user = User::where('phone', $request->phone)->first();





        if ($user) {

            if (!Hash::check($request->password, $user->password)) {

                return response()->json(
                    [
                        "errors" => [
                            "password" => [
                                "Invalid Password!"
                            ]
                        ],
                        "status" => false,
                        'code' => 404,
                    ]
                );
            } else {




                $accessToken = $user->createToken('authToken')->accessToken;

                return response([
                    'code' => 200,
                    'status' => true,
                    'message' => 'login Successfully',
                    'user' => $user,
                    'access_token' => $accessToken
                ]);
            }
        } else {

            return response()->json(
                [
                    "errors" => [
                        "phone" => [
                            "No Account Assigned To This phone!"
                        ]
                    ],
                    "status" => false,
                    'code' => 404,
                ]
            );
        }
    }


    public function logout()
    {

        $user = Auth::guard('api')->user()->token();
        $user->revoke();
        return response()->json([
            'code' => 200,
            'status' => true,
            'message' => 'logout Successfully',
        ]);
    }

    public function forgot_password(Request $request)
    {

        $user = User::where('phone', $request->phone)->first();

        if($user){

        $randomCode = Str::random(6);


        $user->code = $randomCode;
        $user->save();

        return response()->json([
            'code' => 200,
            'status' => true,
            'message' => 'a change password request code has been sent to your email',
            'data' => $randomCode
        ]);
    }else{

        return response()->json(
            [
                "errors" => [
                    "phone" => [
                        "No Account Assigned To This phone!"
                    ]
                ],
                "status" => false,
                'code' => 404,
            ]
        );


    }
    }

    public function change_forgotten_password(Request $request)
    {
        $user = User::where('phone', $request->phone)->first();
        if($user){

        if ($user->code == $request->code) {
            $password1 = bcrypt($request->new_password1);
            $password2 = bcrypt($request->new_password2);
            if($password1 == $password2){
            $user->password = bcrypt($request->new_password);

           
            $user->code = null;
            $user->save();
            }else{

                
            return response()->json(
                [
                    "errors" => [
                        "phone" => [
                            "Passwords dont match!!"
                        ]
                    ],
                    "status" => false,
                    'code' => 404,
                ]
            );
            }

           
          

            return response()->json([
                'code' => 200,
                'status' => true,
                'message' => 'Password updated successfully',

            ]);
        }else{

            return response()->json(
                [
                    "errors" => [
                        "phone" => [
                            "Code is invalid!"
                        ]
                    ],
                    "status" => false,
                    'code' => 404,
                ]
            );
        }
    }else{

        return response()->json(
            [
                "errors" => [
                    "phone" => [
                        "No Account Assigned To This phone!"
                    ]
                ],
                "status" => false,
                'code' => 404,
            ]
        );
    }
   
}
}