<?php

/**
* CFileSession implements file-based sessions.
*/
class CFileSession extends CComponent
{
    /**
    * @var string Session storage path.
    */
    public $savePath;
    
    /**
    * @var integer Session timeout in minutes. Defaults to 20.
    */
    public $timeout = 20;
    
    /**
    * @var string Session cookie name.
    */
    public $cookieName = 'PHPSESSID';
    
    /**
    * @var boolean Whether to regenerate session ID on every request. Defaults to true.
    */
    public $regenerateId = true;
    
    /**
    * @var integer Session garbage collecting probability. Defaults to 10.
    */
    public $gcProbability = 10;
    
    /**
    * Initializes sessions.
    */
    public function init()
    {
        if (!is_writeable($this->savePath))
            throw new CException('CPhpSession::savePath specifies a non-existing or write-protected directory.');
        
        // Set default session gc probability.
        if ($this->gcProbability)
        {
            ini_set('session.gc_probability', $this->gcProbability);
            ini_set('session.gc_divisor', 100);
        }
        
        // Set default session timeout.
        if ($this->timeout)
        {
            ini_set('session.cookie_lifetime', $this->timeout * 60);
            ini_set('session.gc_maxlifetime', $this->timeout * 60);
        }
        
        // Set session cookie name.
        if ($this->cookieName)
            session_name($this->cookieName);
        
        // Set session storage path.
        if ($this->savePath)
            session_save_path($this->savePath);
        
        // Start session.
        session_start();
        
        // Regenerate session ID
        if ($this->regenerateId)
            session_regenerate_id(true);
    }
    
    /**
    * Returns the session variable value with the session variable name.
    * @param mixed The session variable name.
    * @param mixed The default value to be returned when the session variable does not exist.
    * @return mixed The session variable value, or $defaultValue if the session variable does not exist.
    */
    public function get($key, $defaultValue = null)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $defaultValue;
    }
    
    /**
    * Sets a session variable.
    * Note, if the specified name already exists, the old value will be removed first.
    * @param mixed Session variable name.
    * @param mixed Session variable value.
    */
    public function set($key, $value)
    {
        if ($value === null)
            unset($_SESSION[$key]);
        else
            $_SESSION[$key] = $value;
    }
    
    /**
    * Clear session.
    */
    public function clear()
    {
        session_cache_expire();
        session_destroy();
    }
}