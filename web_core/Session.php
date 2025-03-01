<?php

namespace WebCore;

class Session
{
    private $sess_name;
    private $sess_started = false;
    private $sess_lifetime = 15; // set session lifetime (in days)
    private $sess_regen = 30; // set session regen time (in minutes)

    private $sess_data = [];

    public function __construct($session_name)
    {
        $this->sess_name = $session_name;
    }

    public function start()
    {
        // set session name
        session_name($this->sess_name);

        // set session lifetime in days
        ini_set('session.cookie_lifetime', 60 * 60 * 24 * $this->sess_lifetime);
        ini_set('session.gc-maxlifetime', 60 * 60 * 24 * $this->sess_lifetime);

        // start session (compatibility mode for old php and newer)
        if (version_compare(phpversion(), '5.4.0', '<')) {
            if (session_id() == '') session_start();
        } else {
            if (session_status() == PHP_SESSION_NONE) session_start();
        }

        $this->sess_started = true;

        // store session data
        $this->sess_data = $_SESSION;

        // regenerate session every x min
        if (!$this->get('created')) {
            $this->set('created', time());
        } elseif (time() - $this->get('created') > (60 * $this->sess_regen)) {
            session_regenerate_id(true);
            $this->set('created', time());
        }
    }

    public function createSession($details)
    {
        if (!$this->sess_started) {
            throw new \Exception('Session not started');
        }

        $this->destroySession();

        foreach ($details as $key => $value) {
            $this->set($key, $value);
        }

        return true;
    }

    public function destroySession()
    {
        if (!$this->sess_started) {
            throw new \Exception('Session not started');
        }

        session_destroy();
        session_unset();
        $_SESSION = [];
        $this->sess_data = [];

        return true;
    }

    public function get($prop_key, $default = null)
    {
        return isset($this->sess_data[$prop_key]) ? $this->sess_data[$prop_key] : $default;
    }

    public function set($prop_key, $prop_value)
    {
        if (!$this->sess_started) {
            throw new \Exception('Session not started');
        }

        $_SESSION[$prop_key] = $prop_value;
        $this->sess_data[$prop_key] = $prop_value;

        return true;
    }
}
