<?php

/**
* Manages admin login.
* TODO: Implement 3 login types: simple hash, encoded info and simple sessions.
*/
class CWebLogin extends CComponent
{
    /**
    * @var string Administrator username.
    */
    public $username = 'admin';
    
    /**
    * @var string Administrator password. Defaults to md5('admin').
    */
    public $password = '21232f297a57a5a743894a0e4a801fc3';
    
    /**
    * @var string Login cookie name.
    */
    public $loginCookie = 'SESSIONID';
    
    /**
    * Calculates login hash.
    */
    protected function createRequestHash($username, $password)
    {
        return md5(serialize(array(
            'username' => $username,
            'password' => $password,
            'ua' => $_SERVER['HTTP_USER_AGENT'],
            'ip' => $_SERVER['REMOTE_ADDR'],
        )));
    }
    
    /**
    * Checks admin login.
    * @return boolean Whether requesting user is allowed to enter.
    */
    public function checkLogin()
    {
        return $this->createRequestHash($this->username, $this->password) === cf::app()->getCookie($this->loginCookie);
    }
    
    /**
    * Logs admin in.
    */
    public function login($username, $password, $remember = false)
    {
        if ($this->username == $username && $this->password === md5($password))
        {
            cf::app()->setCookie
            (
                $this->loginCookie,
                $this->createRequestHash($username, md5($password)),
                $remember ? time() + 365 * 86400 : false
            );
            
            return true;
        }
        
        return false;
    }
    
    /**
    * Logs admin out.
    */
    public function logout()
    {
        cf::app()->setCookie($this->loginCookie, null);
        
        return true;
    }
}