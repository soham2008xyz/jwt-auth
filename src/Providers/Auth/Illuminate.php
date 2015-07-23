<?php

namespace Tymon\JWTAuth\Providers\Auth;

use Exception;
//use Illuminate\Auth\AuthManager;
use Ollieread\Multiauth\MultiManager;
use Tymon\JWTAuth\Contracts\Providers\Auth as AuthContract;
use Auth;

class Illuminate implements AuthContract
{
    /**
     * @var \Ollieread\Multiauth\MultiManager
     */
    protected $auth;

    /**
     * @param \Ollieread\Multiauth\MultiManager  $auth
     */
    public function __construct(MultiManager $auth)
    {
        $this->auth = $auth;
        //$this->auth = Auth::user();
    }

    /**
     * Check a user's credentials
     *
     * @param  array  $credentials
     *
     * @return boolean
     */
    public function byCredentials(array $credentials)
    {
        return $this->auth->once($credentials);
    }

    /**
     * Authenticate a user via the id
     *
     * @param  mixed  $id
     *
     * @return boolean
     */
    public function byId($id)
    {
        try {
            return $this->auth->onceUsingId($id);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get the currently authenticated user
     *
     * @return mixed
     */
    public function user()
    {
        return $this->auth->user();
    }
}
