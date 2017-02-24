<?php

/**
* CApplication holds core functionality.
* 
* TODO: Prevent cycled redirect when there is no error path.
* TODO: Add error exception renderging (release mode).
* TODO: Security-protect cache and session files.
* TODO: Log function, send to email, rotate logs.
* TODO: Enforce URL suffix (do not allow with no suffix).
* TODO: Add ability to handle 404 and 500 errors separately.
*/
class CApplication extends CObject
{
    /**
    * @var string The name of the $_SERVER variable that contains real client IP address.
    */
    public $remoteAddrVar = 'HTTP_X_REAL_IP';
    
    /**
    * @var string Path holding application actions.
    */
    public $actionPath;
    
    /**
    * @var string Path containing application views.
    */
    public $viewPath;
    
    /**
    * @var string Cookie security algorythm: '#' to sign or '*' to encrypt. Defaults to null, meaning plain cookies.
    */
    public $cookieSecurity;
    
    /**
    * @var string The route $_GET variable name.
    */
    public $routeVar = 'r';
    
    /**
    * @var array The map used to masquarade URLs.
    */
    public $routeMap = array();
    
    /**
    * @var boolean Whether to use strict URLs.
    */
    public $urlStrict = true;
    
    /**
    * @var boolean Whether to show script name in the URLs or false not to use.
    */
    public $urlScript = false;
    
    /**
    * @var string URL suffux to be appended to route or false not to use URL suffix.
    */
    public $urlSuffix = false;
    
    /**
    * @var mixed The default route to use when none is specified.
    */
    public $homeRoute = 'website/home';
    
    /**
    * @var string The route to redirect guest for logging in.
    */
    public $loginRoute = 'website/login';
    
    /**
    * @var mixed The route to redirect on error.
    */
    public $errorRoute = 'website/error';
    
    /**
    * @var array The IDs of the application components that should be preloaded.
    */
    public $preload = array('error');
    
    
    private $_hostInfo;
    private $_modulesConfig;
    private $_modules;
    private $_controllersConfig;
    private $_controller;
    private $_currentRoute;
    
    /**
    * Constructs application.
    * @param string Configuration path.
    */
    public function __construct($config)
    {
        // Set application instance.
        cf::setApplication($this);
        
        // Read configuration.
        if (!is_array($config) && is_file($config))
            $config = include($config);
        
        // Initialize properties.
        foreach ($config as $key => $value)
            $this->$key = $value;
            
        // Fix REMOTE_ADDR variable.
        if (!DEBUG && $this->remoteAddrVar && isset($_SERVER[$this->remoteAddrVar]))
            $_SERVER['REMOTE_ADDR'] = $_SERVER[$this->remoteAddrVar];
        
        // Check base path
        if (!is_dir($this->actionPath))
            throw new CException('The base path "%s" is not a valid directory.', $this->actionPath);
        
        // Check view path
        if (!is_dir($this->viewPath))
            throw new CException('The view path "%s" is not a valid directory.', $this->viewPath);
        
        if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
        {
            if (isset($_GET)) $_GET = $this->stripSlashes($_GET);
            if (isset($_POST)) $_POST = $this->stripSlashes($_POST);
            if (isset($_REQUEST)) $_REQUEST = $this->stripSlashes($_REQUEST);
            if (isset($_COOKIE)) $_COOKIE = $this->stripSlashes($_COOKIE);
        }
        
        // Preload application components
        foreach ($this->preload as $id)
            $this->getComponent($id);
    }
    
    /**
    * @param mixed Data to remove slashes from.
    */
    public function stripSlashes(&$data)
    {
        return is_array($data) ? array_map(array($this, 'stripSlashes'), $data) : stripslashes($data);
    }
    
    /**
    * @param string Prefered schema.
    * @return string Host info with protocol, domain name and port.
    */
    public function getHostInfo($schema = '')
    {
        if ($this->_hostInfo === null)
        {
            $ssl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on';
            $port = $_SERVER['SERVER_PORT'];
            
            $this->_hostInfo .= $ssl ? 'https://' : 'http://';
            $this->_hostInfo .= reset(@explode(':', $_SERVER['HTTP_HOST']));
            
            if (($port != 80 && !$ssl) || ($port != 443 && $ssl))
                $this->_hostInfo .= ':' . $port;
        }
        
        // Replace schema if specified.
        if ($schema !== '' && ($pos = strpos($this->_hostInfo, ':')) !== false)
            return $schema . substr($this->_hostInfo, $pos);
        else
            return $this->_hostInfo;
    }
    
    /**
    * Sets the schema and host part of the application URL.
    * This setter is provided in case the schema and hostname cannot be determined
    * on certain Web servers.
    * @param string The schema and host part of the application URL.
    */
    public function setHostInfo($value)
    {
        $this->_hostInfo = rtrim($value, '/');
    }
    
    /**
    * Parses the URL into an application route.
    */
    public function parseUrl()
    {
        if (!$this->_currentRoute)
        {
            // Set default route
            $path = $this->homeRoute;
            
            // Parse URL in get mode
            if ($this->routeVar !== false)
            {
                if (isset($_GET[$this->routeVar]) && strlen($_GET[$this->routeVar]) > 0)
                    $path = $_GET[$this->routeVar];
                
                if (isset($_POST[$this->routeVar]) && strlen($_POST[$this->routeVar]) > 0)
                    $path = $_POST[$this->routeVar];
            }
            
            // Parse URL in path mode (no routeVar)
            else if (strlen(trim($uri = urldecode($_SERVER['REQUEST_URI']), '/')) > 0)
            {
                // Remove query string
                if (($pos = strpos($uri, '?')) > 0)
                    $uri = substr($uri, 0, $pos);
                
                // Remove script name
                if (($pos = strpos($uri, $_SERVER['SCRIPT_NAME'])) === 0)
                    $uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
                else if ($this->urlStrict)
                    throw new CException('[404] Specified path is not valid.');
                
                // Remove URL suffix
                if ($this->urlSuffix)
                {
                    if (substr($uri, -strlen($this->urlSuffix)) === $this->urlSuffix)
                        $uri = substr($uri, 0, -strlen($this->urlSuffix));
                    else if ($this->urlStrict)
                        throw new CException('[404] Specified path is not valid.');
                }
                
                // Remove leading slash
                if (strlen($url = trim($uri, '/')) > 0)
                    $path = $url;
            }
            
            // Apply route map
            if ($path === $this->homeRoute)
                return $this->_currentRoute = $path;
            else if (isset($this->routeMap[$path]))
                $path = $this->routeMap[$path];
            else if ($this->urlStrict)
                throw new CException('[404] Specified path is not valid.');
            
            $this->_currentRoute = $path;
        }
        
        return $this->_currentRoute;
    }
    
    /**
    * Creates a URL from an array-based form.
    */
    public function createUrl($route, $params = array())
    {
        $url = '';
        $query = '';
        $anchor = '';
        
        // Absolute URL
        if (strpos($route, '//') === 0) {$route = substr($route, 2); $url = $this->getHostInfo();}
        if (strpos($route, '/') === 0) {$route = $this->homeRoute;}
        if (strpos($route, 'http://') === 0) {$route = substr($route, 7); $url = $this->getHostInfo('http');}
        if (strpos($route, 'https://') === 0) {$route = substr($route, 8); $url = $this->getHostInfo('https');}
        
        // Apply route map
        if ($key = array_search($route, $this->routeMap))
            $route = $key;
        else if ($this->urlStrict)
            throw new CException('Specified path is not in the URL map: %s', $route);
        
        // Need to add leading slash
        if ($this->routeVar !== false)
            $url .= '/';
        
        // Add script name to URL
        else if ($this->urlScript === true)
            $url .= $_SERVER['SCRIPT_NAME'];
        
        // Add route part to the URL
        if ($this->routeVar === false)
            $url .= '/' . $route . (!empty($route) ? $this->urlSuffix : '');
        else
            $url .= '?' . $this->routeVar . '=' . $route;
        
        // Process hashtag sign
        if (isset($params['#']))
        {
            $anchor = '#' . urlencode($params['#']);
            unset($params['#']);
        }
        
        // Add parameters
        foreach ($params as $name => $value)
            $query .= '&' . urlencode($name) . '=' . urlencode($value);
        
        // Fix parameters
        if ($this->routeVar === false && substr($query, 0, 1) === '&')
            $query = substr_replace($query, '?', 0, 1);
        
        return $url . $query . $anchor;
    }
    
    /**
    * Redirects user request to a specified url or path.
    * @param mixed String path to redirect to.
    */
    public function redirect($path, $params = array())
    {
        header('Location: ' . $this->createUrl($path, $params));
        exit();
    }
    
    /**
    * This should be called whenever guest is trying to request protected page.
    */
    public function redirectToLogin()
    {
        if ($this->loginRoute)
            $this->runController($this->loginRoute);
    }
    
    /**
    * This should be called whenever website error occures and application is not in DEBUG mode.
    */
    public function redirectToError()
    {
        if ($this->errorRoute)
            $this->runController($this->errorRoute);
    }
    
    /**
    * Gets a cookie.
    * 
    * @var string Cookie name.
    * @var string Default value that will be returned is cookie is not set.
    * @return string Cookie value.
    */
    public function getCookie($name, $default = null)
    {
        if (isset($_COOKIE[$name]))
        {
            $value = $_COOKIE[$name];
            
            if (!is_null($char = $this->cookieSecurity))
            {
                if ($char === '*') $value = cf::security()->decrypt($value);
                if ($char === '#') $value = cf::security()->validateData($value);
            }
            
            return $value;
        }
        else
            return $default;
    }
    
    /**
    * Sets a cookie.
    * 
    * @var string Cookie name.
    * @var string Cookie value.
    * @var integer Cookie expiry timestamp.
    * @var string The path on the server in which the cookie will be available on. Defaults to '/'.
    * @var string The domain that the cookie is available on. Defaults to null, meaning any domain.
    * @var boolean Whether cookie should only be transfered via SSL connection. Defaults to false.
    * @return boolean Whether cookie has been set.
    */
    public function setCookie($name, $value, $expire = null, $path = '/', $domain = null, $secure = false, $httpOnly = false)
    {
        if (is_null($expire))
            $expire = time() + 365 * 24 * 60 * 60;
        
        if (!is_null($char = $this->cookieSecurity))
        {
            if ($char === '*') $value = cf::security()->encrypt($value);
            if ($char === '#') $value = cf::security()->hashData($value);
        }
        
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }
    
    /**
    * @param array Modules configuration.
    */
    public function setComponents($config)
    {
        $this->_modulesConfig = $config;
    }
    
    /**
    * @param string Module ID.
    * @return CComponent Application module instance.
    */
    public function getComponent($id)
    {
        $id = strtolower($id);
        
        if (isset($this->_modules[$id]))
            return $this->_modules[$id];
        
        // Create component instance.
        if (isset($this->_modulesConfig[$id]))
            $module = CObject::create($this->_modulesConfig[$id]);
        else
            throw new CException('Cannot create application component, configuration is missing: %s', $id);
        
        // Initialize component.
        if ($module instanceof CComponent)
            $module->init();
        else
            throw new CException('Application component should inherit from CComponent: %s', get_class($module));
        
        // Persist component instance.
        return $this->_modules[$id] = $module;
    }
    
    /**
    * @param array Actions configuration.
    */
    public function setControllers($config)
    {
        $this->_controllersConfig = $config;
    }
    
    /**
    * @return CAction Currently active action.
    */
    public function getController()
    {
        return $this->_controller;
    }
    
    /**
    * Runs action.
    */
    public function runController($path)
    {
        $cid = '';
        $dir = $this->actionPath;
        $segs = explode('/', trim($path, '\\/'));
        
        // Loop through array
        while (!is_null($seg = array_shift($segs)))
        {
            // Is current segment a folder?
            if (is_dir($dirName = $dir . '/' . $seg))
            {
                $cid = $cid . '/' . $seg;
                $dir = $dirName;
                continue;
            }
            
            // Is current segment a controller file?
            if (is_file($fileName = $dir . '/' . ucfirst($seg) . 'Controller.php'))
            {
                // Consider controller ID
                $cid = ltrim($cid . '/' . $seg, '/');
                
                // Include controller file
                include($fileName);
                
                // Create controller
                $method = implode('/', $segs);
                $className = ucfirst($seg) . 'Controller';
                $controller = $this->_controller = new $className($seg);
                
                // Configure controller
                if (isset($this->_controllersConfig[$cid]))
                {
                    foreach ($this->_controllersConfig[$cid] as $key => $value)
                        $controller->$key = $value;
                }
                
                break;
            }
        }
        
        if (!isset($controller))
            throw new CException('[404] Could not find controller for path "%s".', $path);
        
        if (!method_exists($controller, 'run'))
            throw new CException('%s must implement run() method.', $className);
        
        return $controller->run($method);
    }
}

/**
* CComponent is the base class for application modules.
*/
abstract class CComponent extends CObject
{
    /**
    * Initializes the module after all properties were set.
    */
    public function init() {}
}