<?php

/**
* CFileCache implements simple file-based cache.
*/
class CFileCache extends CComponent
{
    /**
    * @var string Path to save cache files.
    */
    public $cachePath;
    
    /**
    * Initializes cache.
    */
    public function init()
    {
        if (!is_writeable($this->cachePath))
            throw new CException('CFileCache::cachePath specifies a non-existing or write-protected directory.');
    }
    
    /**
    * Returns value stored in cache.
    * @param string Cache variable name.
    * @param mixed Default value returned if there is no cached value or it has expired.
    * @return mixed Cached value.
    */
    public function get($name, $default = null)
    {
        $cacheFile = $this->cachePath . DIRECTORY_SEPARATOR . md5($name);
        
        if (($time = @filemtime($cacheFile)) > time())
        {
            $value = @file_get_contents($cacheFile);
            
            if ($value !== false)
                $value = @unserialize($value);
            
            return $value;
        }
            
        else if ($time > 0)
            @unlink($cacheFile);
        
        return null;
    }
    
    /**
    * Stores value to cache.
    * 
    * @param string Cache variable name.
    * @param mixed Value to be stored in cache.
    * @param integer The number of seconds in which the cached value will expire, 0 means never expire.
    */
    public function set($name, $value, $expire = null)
    {
        $cacheFile = $this->cachePath . DIRECTORY_SEPARATOR . md5($name);
        
        // Clear cache value
        if ($value === null)
            return @unlink($cacheFile);
        
        // Calculate expiration timestamp
        $expire = time() + (is_null($expire) ? 31536000 : $expire);
        
        // Serialize value
        $value = serialize($value);
        
        // Create cache file
        if (@file_put_contents($cacheFile, $value, LOCK_EX) !== false)
        {
            @chmod($cacheFile, 0777);
            return @touch($cacheFile, $expire);
        }
        else
            return false;
    }
    
    /**
    * Clears application cache.
    */
    public function clear($expiredOnly = false)
    {
        if (($handle = opendir($path)) === false)
            return;
        
        while (($file = readdir($handle)) !== false)
        {
            if ($file[0] === '.') continue;
            $fullPath = $this->cachePath . DIRECTORY_SEPARATOR . $file;
            if (!$expiredOnly || $expiredOnly && @filemtime($fullPath) < time())
                @unlink($fullPath);
        }
        
        closedir($handle);
    }
}