<?php

namespace CustomApiAuth\Middleware;

use Closure;
use ApiWrapper;
use Auth;
use CustomApiAuth\Models\ApiUser;

class AuthenticateWithToken
{
    public $redirectTo = "login";
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->get("token",null);
        if($token && Auth::guest())
        {
            ApiWrapper::token($token);
            $response = ApiWrapper::call("get","user");
            if($response && property_exists($response,"user"))
            {
                $data = get_object_vars($response->user);
                $ApiUser = $this->constructApiUserModel($data);
                $ApiUser = \Auth::login($ApiUser);
                if($ApiUser === FALSE)
                {
                    return redirect($this->redirectTo);
                }
            }
        }
        return $next($request);
    }

    /**
     * Convert array data to ApiUser Model
     * @param  array  $data
     * @return CustomApiAuth\Models\ApiUser
     */

    public function constructApiUserModel($data)
    {
        $ApiUser = new ApiUser();
        $ApiUser->fill($data);
        return $ApiUser;
    }

}
