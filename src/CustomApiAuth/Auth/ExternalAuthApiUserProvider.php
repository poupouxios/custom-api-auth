<?php

namespace CustomApiAuth\Auth;

use CustomApiAuth\Models\ApiUser;
use ApiWrapper;
use Hash;
use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class ExternalAuthApiUserProvider implements UserProvider
{

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        $ApiUser = null;
        $token = ApiWrapper::token();
        if($token && strlen($token) > 0)
        {
            $ApiUser = $this->retrieveByToken($identifier,$token);
        }else
        {
            $response = ApiWrapper::call("post",'auth/login',["ApiUserId" => $identifier]);
            if($response)
            {
                if(property_exists($response,"token"))
                {
                    $ApiUser = $this->getApiUserModel($response->token);
                }
            }
        }
        return $ApiUser;
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        ApiWrapper::token($token);
        $response = ApiWrapper::call("get","user");
        if($response && property_exists($response,"user"))
        {
            $data = get_object_vars($response->user);
            $ApiUser = $this->constructApiUserModel($data);
            return $ApiUser;
        }
        return null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(UserContract $user, $token)
    {
        $response = ApiWrapper::call("get","auth/logout");
        if($response)
        {
            return $user;
        }
        return null;
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials)) {
            return;
        }

        $ApiUser = null;
        $response = ApiWrapper::call("post",'auth/login',$credentials);
        if($response)
        {
            if(property_exists($response,"token"))
            {
                $ApiUser = $this->getApiUserModel($response->token);
            }
        }
        return $ApiUser;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        $token = ApiWrapper::token();
        if($token && strlen($token) > 0)
        {
            $response = ApiWrapper::call("get","user");
            if($response && property_exists($response,"user"))
            {
                if($response->user->ApiUserLoginName == $user->ApiUserLoginName)
                {
                    return true;
                }
            }
        }else
        {
          $response = ApiWrapper::call("post",'auth/login',$credentials);
          if($response)
          {
              if(property_exists($response,"token"))
              {
                  $ApiUser = $this->getApiUserModel($response->token);
                  if($ApiUser && $ApiUser->ApiUserLoginName == $user->ApiUserLoginName)
                  {
                      return true;
                  }
              }
          }
        }
        return false;
    }


    private function getApiUserModel($token)
    {
        ApiWrapper::token($token);
        $response = ApiWrapper::call("get","user");
        if($response && property_exists($response,"user"))
        {
            $data = get_object_vars($response->user);
            $ApiUser = $this->constructApiUserModel($data);
            return $ApiUser;
        }
        return null;
    }

    /**
     * Convert array data to ApiUser Model
     * @param  array  $data
     * @return App\Models\ApiUser
     */

    public function constructApiUserModel($data)
    {
        $ApiUser = new ApiUser();
        $ApiUser->fill($data);
        return $ApiUser;
    }

}
