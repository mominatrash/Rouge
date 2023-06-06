<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Address;
use App\Models\Welcome;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

class UserController extends Controller
{

    public function send_notification($fcm_token, $title, $body, $order_id)
    {
        $from = "AAAAm1meYS0:APA91bGCYPtLMEdVt2KLetGH7mAp9zwzEOEkcZzAwQoVqpRJU8eJecCopajsuPmPnI4vMvAVCJybx-R9CKx8fbtexzbJeoP5JGVehvo8TEp12kOp1XrlDtl;kjafsd;lkafjalskdf";
        $to = $fcm_token;

        $msg = array(
            'title' => $title,
            'body' => $body,

        );

        $fields = array(
            'to' => $fcm_token,
            'notification' => $msg,
            'data' => [
                'bookingId' => $order_id,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                "screen" =>  "POST_SCREEN",

            ]
        );


        $headers = array(
            'Authorization: key=' . $from,
            'Content-Type: application/json'
        );
        //#Send Reponse To FireBase Server
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }



    public function send_notification_to_person(Request $request)
    {

        $usernotification = Notification::create([
            'user_id' => $request->sendnotifi,
            'title' => $request->title,
            'body' =>  $request->body,
        ]);

        $user = User::findOrFail($request->user_id);

        $order_id = 5;

        $this->send_notification($user->fcm_token, $request->title, $request->body, 60);



        return response()->json([
            'message3'            => 'تم ارسال اشعار',
        ]);
    }





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

        if ($user) {

            $randomCode = Str::random(6);


            $user->code = $randomCode;
            $user->save();

            return response()->json([
                'code' => 200,
                'status' => true,
                'message' => 'a change password request code has been sent to your email',
                'data' => $randomCode
            ]);
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

    public function verify_pass_code(Request $request)
    {
        $user = User::where('phone', $request->phone)->first();
        if ($user) {
            if ($user->code == $request->code) {



                return response()->json([
                    'code' => 200,
                    'status' => true,

                ]);
            } else {

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


    public function change_forgotten_password(Request $request)
    {
        $user = User::where('phone', $request->phone)->first();
        if ($user) {
            if ($user->code == $request->code) {

                $user->code = null;
                $user->password = bcrypt($request->new_password);
                $user->save();


                return response()->json([
                    'code' => 200,
                    'status' => true,
                    'message' => 'Password updated successfully',

                ]);
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
    }



    public function addresses()
    {
        $addresses = Address::where('user_id', Auth::guard('api')->user()->id)->get();

        return response()->json([
            'message' => 'data fetched successfully',
            'code' => 200,
            'address' => $addresses,
        ]);
    }

    public function new_address(Request $request)
    {
        $address = new Address();
        $address->user_id = Auth::guard('api')->user()->id;
        $address->name = $request->nickname;
        $address->governorate = $request->governorate;
        $address->address = $request->address;
        $address->nearby_place = $request->nearby_place;
        $address->save();


        return response()->json([
            'message' => 'data fetched successfully',
            'code' => 200,
            'address' => $address,
        ]);
    }

    public function update_address(Request $request)
    {
        $update_address = Address::where('id', $request->id)->first();

        $update_address->name = $request->nickname;
        $update_address->governorate = $request->governorate;
        $update_address->address = $request->address;
        $update_address->nearby_place = $request->nearby_place;
        $update_address->save();

        return response()->json([
            'message' => 'address updated successfully',
            'code' => 200,
            'address' => $update_address,
        ]);
    }

    public function change_address(Request $request)
    {
        $oldAddress = Address::where('id', Auth::guard('api')->user()->id)->where('selected', 1)->where('id', '!=', $request->id)->first();
        if ($oldAddress) {
            $oldAddress->selected = 0;
            $oldAddress->save();

            $address = Address::where('id', $request->id)->first();
            $address->selected = 1;
            $address->save();


            return response()->json([
                'message' => 'address changed successfully',
                'code' => 200,
                'address' => $address,
            ]);
        } else {
            $address = Address::where('id', $request->id)->first();
            return response()->json([
                'message' => 'address already selected',
                'code' => 200,
                'address' => $address,
            ]);
        }
    }

    public function delete_address(Request $request)
    {
        $address = Address::where('id', $request->id)->first();

        if ($address->selected == 1) {
            $nextAddress = Address::where('id', '!=', $request->id)->first();

            if ($nextAddress) {
                $nextAddress->selected = 1;
                $nextAddress->save();
                $address->delete();

                return response()->json([
                    'message' => 'Address deleted successfully',
                    'code' => 200,
                ]);
            } else {
                return response()->json([
                    'message' => 'Cant delete your last address',
                    'code' => 200,
                ]);
            }
        } else {

            $address->delete();

            return response()->json([
                'message' => 'Address deleted successfully',
                'code' => 200,
            ]);
        }
    }
}
