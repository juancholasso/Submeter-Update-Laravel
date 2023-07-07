<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SmsSendMessage extends Controller
{

    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public static function SendMessage($phone, $message){
        $request = '{
            "api_key":"'.env('SMS_API_KEY', 'b3994f2e90494a448510f26c44d0186a').'",
            "messages":[
                {
                    "from":"Submeter",
                    "to":"'.$phone.'",
                    "text":"'.$message.'"
                }
            ]
        }';

        Log::debug($request);
                    
        $headers = array('Content-Type: application/json');        	
        
        $ch = curl_init('https://api.gateway360.com/api/3.0/sms/send');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        
        $result = curl_exec($ch);
        Log::debug($result); 
        if (curl_errno($ch) != 0 ){
            Log::debug(curl_errno($ch));
            die("curl error: ".curl_errno($ch));
        }      
    }
}
