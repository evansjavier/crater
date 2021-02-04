<?php

namespace Crater\Http\Controllers\V1\Auth;

use Crater\Http\Controllers\Controller;
use Crater\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Crater\Models\User;
use Illuminate\Routing\RedirectController;

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
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except(['logout', 'autoLogin', 'logoutMultiple']);
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm(Request $request)
    {
        $device_id = $request->input('device_id');
        $no_account = $request->input('no_account');

		// Device id para autenticación externa
        $solicitar_token = true;
        
        // Comprobar si el token recibido para realizar la autenticación es válido
        if($device_id){
			$device = Http::get(env('EXTERNAL_AUTH_SERVER') . "/device/" . $device_id)->json();
            
            if($device && $device['estatus_sesion'] == 'pendiente'){ // token válido para iniciar sesión
				$solicitar_token = false;
			}
		}

		if($solicitar_token && !$no_account){
            $view =  View::make('redirect', [
                'to' => env('EXTERNAL_AUTH_SERVER') . "/getAuthToken?site=crater"
            ]);
            return response($view);
		}
		else{
            \Cookie::queue('device_id', $device_id);
            return view('app');
		}
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function home()
    {   
        return redirect('/login');
    }


    public function autoLogin(Request $request){
        
        // Comprobar si el token recibido para realizar la autenticación es válido
        $device_id = $request->input('device_id');
        $device = Http::get(env('EXTERNAL_AUTH_SERVER') . "/device/" . $device_id)->json();

        // Si la sesión está activa guardar en global de auto log
		if($device['estatus_sesion'] == 'activa'){

            // Buscar usuario a loguear
            $user = User::whereEmail( $device['email'] )->first();

            if($user){
                // Registrar inicio de sesión (auto) en sistema de autenticación externo
                $response = Http::post(env('EXTERNAL_AUTH_SERVER') . "/registerLoginAuto", [
                    'sitio' => 'crater',
                    'device_id' => $device_id,
                    'app_password' => env('AUTH_CLIENT_PASSWORD'),
                ]);
                // fin - registrar inicio de sesión

                $this->guard()->login($user, false);

                $view =  View::make('redirect', [
                    'to' => $this->redirectTo
                ]);
                return response($view)->withCookie("device_id", $device_id);
            }
            else{
                // no posee usuario
                return redirect( env('APP_URL') .  '/login?no_account=true&device_id=' . $device_id );
            }
            
		}

    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Registrar cierre de sesión
		$device_id = $request->cookie('device_id');
		if ($device_id) {
            $response = Http::post(env('EXTERNAL_AUTH_SERVER') . "/logout", [
                'device_id' => $device_id,
                'app_password' => env('AUTH_CLIENT_PASSWORD'),
            ]);
        }
        // fin 

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect('/');
    }

    public function logoutMultiple(Request $request){

        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $params = $request->all();
        $params['crater'] = 1;

        return redirect(  env('EXTERNAL_AUTH_SERVER') . "/logoutMultiple?" . http_build_query($params) );

    }
}
