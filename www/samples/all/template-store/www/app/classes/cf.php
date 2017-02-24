<?php

/**
* Application core.
* TODO: Add multiple variants of directory / file import.
* TODO: cf::mergeArray (or CConfig::mergeArray)
* TODO: 
*/
class cf
{
    private static $_app = null;
    private static $_importClasses = array();
    private static $_importFolders = array();
    
    /**
    * @return CApplication The application singleton, null if the singleton has not been created yet.
    */
    public static function app()
    {
        return self::$_app;
    }
    
    /**
    * Stores the application instance in the class static member.
    * This method helps implement a singleton pattern for CApplication.
    * @param CApplication $app the application instance. If this is null, the existing
    * application singleton will be removed.
    * @throws CException If multiple application instances are registered.
    */
    public static function setApplication($app)
    {
        if (self::$_app === null || $app === null)
            self::$_app = $app;
        else
            throw new CException('Application can only be set once.');
    }
    
    /**
    * Merges two arrays recursively.
    * @param array First array.
    * @param array Second array.
    * @return array Merged array.
    */
    static function mergeArray($a, $b)
    {
        foreach ($b as $key => $value)
        {
            if (is_integer($key))
                isset($a[$key]) ? $a[] = $value : $a[$key] = $value;
            else if(is_array($value) && isset($a[$key]) && is_array($a[$key]))
                $a[$key] = self::mergeArray($a[$key],$value);
            else
                $a[$key] = $value;
        }
        return $a;
    }
    
    /**
    * Depending on the arguments does one of the following.
    * If specified argument is a directory, adds this directory to include paths.
    * If specified argument is a file, adds the class file to autoloader.
    * @param string File or directory path to import.
    */
    public static function import($path)
    {
        // Class file import
        if (is_file($path))
            return self::$_importClasses[basename($path, '.php')] = $path;
        
        // Directory import
        if (is_dir($path))
        {
            if (empty(self::$_importFolders))
            {
                self::$_importFolders = array_unique(explode(PATH_SEPARATOR, get_include_path()));
                if (($pos = array_search('.', self::$_importFolders, true)) !== false)
                    unset(self::$_importFolders[$pos]);
            }
            
            array_unshift(self::$_importFolders, $path);
            
            if (!set_include_path('.' . PATH_SEPARATOR . implode(PATH_SEPARATOR, self::$_importFolders)))
                throw new CException('Could not set include path. Check your PHP settings.');
            
            return $path;
        }
        
        // Nothing imported
        return false;
    }
    
    /**
    * Class autoloader.
    * @param string Class name.
    * @return boolean Whether the class has been loaded successfully.
    */
    static function autoload($className)
    {
        // Core class import
        if (is_file($classFile = dirname(__FILE__) . '/classes/' . $className . '.php'))
            include($classFile);
        
        // Class file import
        else if (isset(self::$_importClasses[$className]))
            include(self::$_importClasses[$className]);
        
        // Lookup class
        else
            include($className . '.php');
        
        return class_exists($className, false);
    }
    
    /**
    * Runs an application.
    */
    static function run($config, $class = 'CApplication')
    {
        // Register an autoloader
        spl_autoload_register(array('cf', 'autoload'));
        
        // Create application instance
        $app = new $class($config);
        
        // Start output buffering
        ob_start();

        // Run application
        $app->runController($app->parseUrl());
        
        // End buffering
        ob_end_flush();
    }
    
    /**
    * @return CDatabase Database connection.
    */
    static function db()
    {
        return self::app()->getComponent(__FUNCTION__);
    }
    
    /**
    * @return CSecurity Security component.
    */
    static function security()
    {
        return self::app()->getComponent(__FUNCTION__);
    }
    
    /**
    * @return CMessages Translation-related functions.
    */
    static function messages()
    {
        return self::app()->getComponent(__FUNCTION__);
    }
    
    /**
    * TODO: Implement ISession interface.
    * @return CFileSession Session component instance.
    */
    static function session()
    {
        return self::app()->getComponent(__FUNCTION__);
    }
    
    /**
    * @return CWebLogin Login component instance.
    */
    static function login()
    {
        return self::app()->getComponent(__FUNCTION__);
    }
    
    /**
    * TODO: Implement ICache interface.
    * @return CFileCache Cache component instance.
    */
    static function cache()
    {
        return self::app()->getComponent(__FUNCTION__);
    }
    
    /**
    * TODO: Implement IMailer interface.
    * @return CPostman Mailer component instance.
    */
    static function email()
    {
        return self::app()->getComponent(__FUNCTION__);
    }
    
    /**
    * @return CErrorHandler Log router component instance.
    */
    static function error()
    {
        return self::app()->getComponent(__FUNCTION__);
    }
    
    /**
    * @return CHelpDesk Help desk API component.
    */
    static function helpdesk()
    {
        return self::app()->getComponent(__FUNCTION__);
    }
    
    /**
    * @return CTemplates User compoonent.
    */
    static function templates()
    {
        return self::app()->getComponent(__FUNCTION__);
    }
}


/**
* CObject is the base class for every component.
*/
abstract class CObject
{
    /**
    * Magic method for supporting smart properties and events.
    */
    public function __get($name)
    {
        if (method_exists($this, $getter = 'get' . $name))
            return $this->$getter();
            
        else
            throw new CException('Property "%s.%s" is not defined.', get_class($this), $name);
    }
    
    /**
    * Magic method for supporting smart properties and events.
    */
    public function __set($name, $value)
    {
        if (method_exists($this, $setter = 'set' . $name))
            return $this->$setter($value);
        
        else if (method_exists($this, 'get' . $name))
            throw new CException('Property "%s.%s" is read only.', get_class($this), $name);
        
        else
            throw new CException('Property "%s.%s" is not defined.', get_class($this), $name);
    }
    
    /**
    * Creates an object and initializes it based on the given configuration.
    * @param mixed The configuration.
    * @return mixed The created object.
    * @throws CException If the configuration does not have a 'class' element.
    */
    static function create($config)
    {
        if (!isset($config['class']))
            throw new CException('Object configuration must be an array containing a "class" element.');
        
        $class = $config['class'];
        unset($config['class']);
        
        /* if (!class_exists($name = basename($class), false))
            self::autoload($class); */
        
        $object = new $class();
        foreach ($config as $key => $value)
            $object->$key = $value;
        
        return $object;
    }
}