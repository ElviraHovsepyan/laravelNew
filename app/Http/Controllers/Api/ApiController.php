<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApiController extends Controller
{
    public function loginViaApi(Request $request){
        $email = $request->email;
        $password = $request->password;
        $check = User::where('email',$email)->first();
        if($check){
            $hashedPassword = $check->password;
            if(Hash::check($password, $hashedPassword)){
                $token = $check->token;
                if(empty($token)){
                    $token = str_random(20);
                    $check->token = $token;
                    $check->save();
                }
                return json_encode('Your token : '.$token);
            } else {
                return 'failure';
            }
        } else {
            return 'failure';
        }
    }
}


