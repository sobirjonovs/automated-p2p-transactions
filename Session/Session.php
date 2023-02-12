<?php

namespace App\Session;

/**
 * Class Session
 * @package Session
 */
class Session
{
    /**
     * @param string $key
     * @param $value
     */
    public function store(string $key, $value)
    {
        $this->assign($key, $value);
    }

    /**
     * Start session
     */
    public function start()
    {
        if(session_status() != PHP_SESSION_ACTIVE){
            session_start();
        }
    }

    /**
     * @param $key
     * @param $value
     */
    public function assign($key, $value)
    {
        $this->start();
        $_SESSION[$key] = $value;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        $this->start();
        return $_SESSION[$key] ?? null;
    }
}