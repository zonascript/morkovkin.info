<?php

/**
* CException is an application-level exception.
*/
class CException extends Exception
{
    /**
    * Improved constructor. Syntax is the same as vsprintf function.
    * @param string Exception message.
    * @param mixed Optional parameters.
    */
    public function __construct($message)
    {
        $params = func_get_args();
        $params = array_slice($params, 1);
        Exception::__construct(vsprintf($message, $params));
    }
    
    /**
    * Renders an exception.
    * @param Exception Exception to be rendered.
    */
    static function render($e)
    {
        include(str_replace('.php', '.tpl', __FILE__)); exit();
    }
}