<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\User; //[Rogelio R - Workana] - Se agrega al controlador para la actualización de datos de la tabla de usuario
use App\AccessLog; //[Rogelio R -Workana] - Se agrega al controlador para la creación de los registros de accesos

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    //[Rogelio R - Workana]
    //Se agregan las variables al controlador para definir la cantidad de intento antes del bloqueo de login 
    protected $maxAttempts = 3;
    protected $decayMinutes = 1;
    protected $lockoutFlag = 0;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

     /**
     * [Rogelio R - Workana]
     * Se sobreescribe la función de Login para permitir el llamado de la función
     * de deslogueo de otros dispositivos
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        //Se valida el estatus de la cuenta, si esta en (LOCKOUT) se activa la bandera y se cambia el número máximo de intentos
        if(($this->userLockStatus($request->email) == 'LOCKOUT') && ($this->limiter()->availableIn($this->throttleKey($request))<=0)){
            $this->lockoutFlag = 1;
            $this->maxAttempts = 2;
        }

        //Valida que la cuenta de usuario no esté en bloqueada (LOCKED), si la cuenta está bloqueada lo informa en la página de login
        if($this->UserLockedValidate($request->email)){
            return $this->sendLockedLoginResponse($request);
        }

        //Si la conexión es satisfactoria
        if ($this->attemptLogin($request)) {
            //Se realiza el deslogueo de las demás sesiones [Rogelio R - Workana]
            $this->logoutOtherDevices($request->password);

            //Se almacena el acceso en el log
            AccessLog::create(['user_email'=>$request->email, 'ip_address'=>$request->ip(), 'access_status'=>'ACCESO']);

            //Se establece el lock_status en UNLOCKED
            User::where('email', $request->email)->update(['lock_status'=>'UNLOCKED']);

            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        //Si ha sobrepasado el número de intentos
        if ($this->hasTooManyLoginAttempts($request)) {
            
            $this->fireLockoutEvent($request);

            //Si es la primera vez que alcanza el máximo de intentos, pasa la cuenta a LOCKOUT
            if($this->lockoutFlag == 0){
                User::where('email', $request->email)->update(['lock_status'=>'LOCKOUT']);
                AccessLog::create(['user_email'=>$request->email, 'ip_address'=>$request->ip(), 'access_status'=>'ERROR']);
            }
            else{ //Al ser la segunda vez que alcanza el máximo de intentos bloquea la cuenta (LOCKED) e informa al usuario
                User::where('email', $request->email)->update(['lock_status'=>'LOCKED']);
                AccessLog::create(['user_email'=>$request->email, 'ip_address'=>$request->ip(), 'access_status'=>'ERROR']);
                return $this->sendLockedLoginResponse($request); //Se envía la notificación de que su cuenta ha sido bloqueada
            }

            //Retorna la respuesta por bloqueo de login
            return $this->sendLockoutResponse($request);
        }

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * [Rogelio R - Workana]
     * Se sobreescribe el método de sendLoginResponse con la finalidad de que siempre redireccione al /home
     * después de loguearse satisfactoriamente
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        return redirect()->route('home');
    }


    /**
     * [Rogelio R - Workana]
     * Invalida las sesiones del usuario en otros dispositivos o navegadores
     * Copiado de vendor\laravel\framework\src\Illuminate\Auth\SessionGuard.php
     * en Laravel 7.5
     *
     * Utiliza el AuthenticateSession middleware por lo que debe descomentarse 
     * la línea "\Illuminate\Session\Middleware\AuthenticateSession::class" en
     * app\Http\Kernel.php
     *
     * @param  string  $password
     * @param  string  $attribute
     * @return null|bool
     */
    public function logoutOtherDevices($password, $attribute = 'password')
    {
        if (! Auth::user()) {
            return;
        }

        return tap(Auth::user()->forceFill([
            $attribute => Hash::make($password),
        ]))->save();
    }

    /**
     * [Rogelio R - Workana]
     * 
     * Se sobreescribe la funcion sendFailedLoginResponse de la clase "vendor\laravel\framework\src\Illuminate\Foundation\Auth\AuthenticatesUsers.php"
     * con la finalidad de personalizar el mensaje de intentos disponibles en caso de fallo en la contraseña.
     * 
     * La función $this->throttleKey($request) retorna la llave formada por el username e IP de la sesión
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        //Se agrega una variable para obtener los intentos de restantes
        $intentos = $this->limiter()->retriesLeft($this->throttleKey($request), $this->maxAttempts());

        //Si tiene intentos fallidos muestra el mensaje con la cantidad de intentos restantes antes del bloqueo temporal, de lo contrario
        //muestra el mensaje por default.
        $mensaje =  trans('auth.remain_attempts', ['attempts' => $intentos]);
        
            $errors = [$this->username() => $mensaje];

            if ($request->expectsJson()) {
                return response()->json($errors, 422);
            }

            return redirect()->back()
                ->withInput($request->only($this->username(), 'remember'))
                ->withErrors($errors);
    }

    /**
     * [Rogelio R - Workana]
     * 
     * Se agrega una función que obtiene el estatus de bloqueo de la cuenta con la finalidad de establecer el control correspondiente
     * a si la cuenta está bloqueada temporalmente (LOCKOUT), bloqueada de forma permanente (LOCKED) o desbloqueada (UNLOCKED)
     */
    protected function userLockStatus($username){
        $usuario = User::where('email', $username)->first();

        if($usuario != null){
            return $usuario->lock_status;
        }
        else{
            return 'UNLOCKED';
        }
    }

    /**
     * [Rogelio R - Workana]
     * 
     * Funcion que valida si la cuenta se encuentra en estatus bloqueado (LOCKED)
     */
    protected function UserLockedValidate($username){
        if($this->userLockStatus($username) == 'LOCKED'){
            return true;
        }
        else{
            return false;
        }
    }

    protected function sendLockedLoginResponse(Request $request){
        $mensaje = 'Su cuenta ha sido bloqueada por seguridad';
        
        $errors = [$this->username() => $mensaje];

        if ($request->expectsJson()) {
            return response()->json($errors, 422);
        }

        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors($errors)
            ->with('locked', 'LOCKED'); //Se agrega una var de sesion
    }
}
