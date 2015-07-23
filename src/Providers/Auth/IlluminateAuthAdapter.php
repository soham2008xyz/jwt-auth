<?php

namespace Tymon\JWTAuth\Providers\Auth;

use Exception;
//use Illuminate\Auth\AuthManager;
use Ollieread\Multiauth\MultiManager;

class IlluminateAuthAdapter implements AuthInterface
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
        $this->auth = $auth->user();
    }

    /**
     * Check a user's credentials
     *
     * @param  array  $credentials
     * @return bool
     */
    public function byCredentials(array $credentials = [])
    {
        return $this->auth->once($credentials);
    }

    /**
     * Authenticate a user via the id
     *
     * @param  mixed  $id
     * @return bool
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
        return $this->auth->get();
    }
}
