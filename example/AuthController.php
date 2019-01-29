<?php

namespace App\Http\Controllers;

use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Response as IlluminateResponse;
use Illuminate\Http\Exception\HttpResponseException;
use CustomApiAuth\Models\ApiUser;

class AuthController extends Controller
{
    /**
     * Handle a login request to the application.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function postLogin(Request $request)
    {
        try {
            $this->validate($request,
                $this->getValidationFields($request));
        } catch (HttpResponseException $e) {
            return response()->json([
                'error' => [
                    'message' => 'invalid_auth',
                    'status_code' => IlluminateResponse::HTTP_BAD_REQUEST,
                ],
            ], IlluminateResponse::HTTP_BAD_REQUEST);
        }

        $credentials = $this->getCredentials($request);
        $user = null;

        try {

            if($request->has("user_id")){
                $user = ApiUser::find($request->input('user_id'));
                if (!$token = JWTAuth::fromUser($user)) {
                    return response()->json([
                        'error' => [
                            'message' => 'invalid_credentials',
                        ],
                    ], IlluminateResponse::HTTP_UNAUTHORIZED);
                }
            }else{
                // Attempt to verify the credentials and create a token for the user
                if (!$token = JWTAuth::attempt($credentials)) {
                    return response()->json([
                        'error' => [
                            'message' => 'invalid_credentials',
                        ],
                    ], IlluminateResponse::HTTP_UNAUTHORIZED);
                }
                unset($credentials['password']);
                $user = ApiUser::where($credentials)->first();
            }
        } catch (JWTException $e) {
            // Something went wrong whilst attempting to encode the token
            return response()->json([
                'error' => [
                    'message' => 'could_not_create_token',
                    'exception' => $e->getMessage()
                ],
            ], IlluminateResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        if($user)
        {
            $success = ApiUser::invalidateToken($user->api_token);
            if($success)
            {
                $user->api_token = $token;
                $user->save();
            }else{
                return response()->json([
                    'error' => [
                        'message' => 'could_not_replace_token',
                    ],
                ], IlluminateResponse::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        // All good so return the token
        return response()->json([
            'success' => [
                'message' => 'token_generated',
                'token' => $token,
            ]
        ]);
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    protected function getCredentials(Request $request)
    {
        if($request->has("user_id")){
            return $request->only('user_id');
        }else{
            return $request->only('email', 'password');
        }
    }

    protected function getValidationFields(Request $request)
    {
        if($request->has("user_id")){
            return [
                'user_id' => 'required|integer'
            ];
        }else {
            return [
                'email' => 'required|email|max:255',
                'password' => 'required',
            ];
        }
    }

    /**
     * Invalidate a token.
     *
     * @return \Illuminate\Http\Response
     */
    public function logoutUser()
    {
        try
        {
            $token = JWTAuth::parseToken();
            $success = ApiUser::invalidateToken(JWTAuth::getToken());
            if($success)
            {
                return response()->json([
                    'success' => [
                        'message' => 'token_invalidated',
                        'token' => $token,
                    ]
                ]);
            }else{
                return response()->json([
                    'error' => [
                        'message' => "Cannot find user"
                    ],
                ]);
            }
        }catch(JWTException $e) {
            return response()->json([
                'error' => [
                    'message' => "token doesn't exist",
                    'exception' => $e->getMessage()
                ],
            ]);
        }
    }

}