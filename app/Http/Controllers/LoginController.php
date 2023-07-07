<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContrasenaModificada;
use App\Mail\IntentoContrasena;
use App\User;
use Swift_Mailer;
use Validator;
use Auth;

class LoginController extends Controller
{
    //
    public function sendResetPassword(Request $request)
    {
        $broker = Password::broker();
        
        $backup = Mail::getSwiftMailer();
        
        
        $transport = new \Swift_SmtpTransport(env('MAIL_HOST'), 587, 'tls');
        $transport->setUsername(env('MAIL_USERNAME'));
        $transport->setPassword(env('MAIL_PASSWORD'));
        
        
        $smtp = new Swift_Mailer($transport);
        
        Mail::setSwiftMailer($smtp);
        
        $response = $broker->sendResetLink(
            $request->only('email')
        );
        
        
        
        if($response == Password::RESET_LINK_SENT)
        {
            Mail::setSwiftMailer($backup);
            return back()->with('status', 'Se ha enviado más información del proceso de restauración al correo registrado');
        }
        else
        {
            $cc_submeter = env('RESET_PASSWORD_NOTIFY');
            $cc_submeter = explode(",", $cc_submeter);
            
            $mailObject = new IntentoContrasena($request->get("email"));
            
            $i = 0;
            $mail_send = "";
            foreach($cc_submeter as $email_cc)
            {
                if($i == 0)
                {
                    $mail_send = $email_cc;
                }
                else
                {
                    $mailObject->cc($email_cc);
                }
            }
            
            Mail::to($mail_send)->send($mailObject);
            
            Mail::setSwiftMailer($backup);
            return back()->withErrors(
                ['email' => trans($response)]
                );
        }
    }
    
    public function resetPassword(Request $request)
    {
        Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ])->validate();
        
        $broker = Password::broker();
        $credentials = $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );
        
        $response = $broker->reset(
            $credentials, function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password),
                    'remember_token' => Str::random(60),
                ])->save();
                $guard = Auth::guard();
                $guard->login($user);
            }
        );
        
        if($response == Password::PASSWORD_RESET)
        {
            $backup = Mail::getSwiftMailer();
            
            
            $transport = new \Swift_SmtpTransport(env('MAIL_HOST'), 587, 'tls');
            $transport->setUsername(env('MAIL_USERNAME'));
            $transport->setPassword(env('MAIL_PASSWORD'));
            
            
            $smtp = new Swift_Mailer($transport);
            
            Mail::setSwiftMailer($smtp);
            
            $user = User::where("email", $request->get("email"))->first();
            
            $cc_submeter = env('RESET_PASSWORD_NOTIFY');
            $cc_submeter = explode(",", $cc_submeter);
            
            $mailObject = new ContrasenaModificada($user);            
            
            foreach($cc_submeter as $email_cc)
            {
                $mailObject->bcc($email_cc);
            }
            
            Mail::to($user->email)->send($mailObject);
            
            Mail::setSwiftMailer($backup);
            return redirect("/home")->with('status', trans($response));
        }
        else
        {
            return redirect()->back()->withInput($request->only('email'))
                        ->withErrors(['email' => $response]);
        }
    }
}
