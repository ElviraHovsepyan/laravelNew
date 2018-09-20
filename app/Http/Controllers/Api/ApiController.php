<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    public $response1 = [
        'success'=>'false',
        'message'=> 'Please check that you have entered your email correctly.',
        'code'=>1001
    ];
    public $response2 = [
        'success'=>'false',
        'message'=> 'Your password does not match.',
        'code'=>1002
        ];

    public function login(Request $request){
        $email = $request->email;
        $password = $request->password;
        $check = User::where('email',$email)->first();
        if($check){
            $hashedPassword = $check->password;
            if(Hash::check($password, $hashedPassword)){
                $token = $check->token;
                if(empty($token)){
                   $token = $this->generateToken($email);
                    $check->token = $token;
                    $check->save();
                }
                $response = [
                    'token'=>$token,
                    'success'=>'true',
                    'code'=>1007
                ];
            } else {
                $response = $this->response2;
            }
        } else {
            $response = $this->response1;
        }
        return json_encode($response);
    }

    public function generateToken($var){
        $token = Hash::make($var);
        return $token;
    }

    public function register(Request $request){
        $rules = [
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $response = [
                'success'=>'false',
                'message'=> $errors,
                'code'=>1005
            ];
            return json_encode($response);
        }
        $vCode = str_random(40);
        $username = $request->username;
        $email = $request->email;
        $password = $request->password;
        $token = $this->generateToken($email);
        $user = new User;
        $user->name = $username;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->token = $token;
        $user->verified = false;
        $user->vCode = $vCode;
        $user->save();
        $response = [
            'success'=>'true',
            'message'=>'Please check your Email to verify your account',
            'code'=>1006
        ];
//        $mail = sendEmail($username,$vCode);
        return json_encode($response);
    }

    public function verify($username,$code){
        $check = User::where('name',$username)->first();
        if(!empty($check) && $check->verified==false){
           if($check->vCode==$code){
               $check->verified=true;
               $check->save();
               return 'success';
           }
        }
    }
}

