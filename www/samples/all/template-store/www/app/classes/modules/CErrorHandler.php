<?php

/**
* CErrorHandler handles exceptions and routes them to file and email.
*/
class CErrorHandler extends CComponent
{
    /**
    * @var string Path to store log files.
    */
    public $logFile;
    
    /**
    * @var integer Max log file size in kilobytes.
    */
    public $logFileSize = 1024;
    
    /**
    * @var string Log file name.
    */
    public $logFilesMax = 5;
    
    /**
    * @var array The list of exception categories that should be written to file.
    */
    public $logFilter;
    
    /**
    * @var string Email to send reports to.
    */
    public $email;
    
    /**
    * @var string Email subject.
    */
    public $emailSubject;
    
    /**
    * @var string Email subject.
    */
    public $emailFrom;
    
    /**
    * @var array The list of exception categories that should be send via email.
    */
    public $emailFilter;
    
    /**
    * Attaches error/exception handlers.
    */
    public function init()
    {
        set_error_handler(array($this, 'handleError'));
        set_exception_handler(array($this, 'handleException'));
    }
    
    /**
    * Converts PHP errors into exceptions.
    * 
    * @param integer Error code.
    * @param string Error message.
    * @param string Error file.
    * @param integer Error line.
    * 
    * @throws ErrorException When error code matches current error reporting level.
    */
    public function handleError($code, $message, $file, $line)
    {
        if ($code & error_reporting())
            $this->handleException(new ErrorException($message, $code, 0, $file, $line));
    }
    
    /**
    * Application exception handler.
    * @param CException Exception instance.
    */
    public function handleException($e)
    {
        // Disable error capturing to avoid recursive errors
        restore_error_handler();
        restore_exception_handler();
        
        // Set HTTP 500 header
        if (!headers_sent())
            header("HTTP/1.0 500 Internal Server Error");
        
        // Clean previously rendered content
        if (ob_get_level() > 0)
            @ob_end_clean();
        
        // Detect exception category
        $category = '*';
        $m = $e->getMessage();
        $p1 = strpos($m, '[');
        $p2 = strpos($m, ']');
        if ($p1 < $p2 && $p1 >= 0)
            $category = substr($m, $p1 + 1, $p2 - $p1 - 1);
        
        // Write to log.
        if ($this->matchFilter($this->logFilter, $category))
            $this->writeToLog($this->formatLogMessage($e));
        
        // Send via email.
        if ($this->matchFilter($this->emailFilter, $category))
            $this->sendToEmail($this->formatLogMessage($e));
        
        // Do we need to use controller for errors?
        if (!DEBUG)
            cf::app()->redirectToError();
            
        // Render exception info
        else
            CException::render($e);
        
        // End application
        exit();
    }
    
    /**
    * Matches message category against filter.
    * @param mixed Filter to match against.
    * @param string Exception category.
    */
    protected function matchFilter($filter, $category)
    {
        if (is_string($filter)) $filter = preg_split('/\s*,\s*/', $filter);
        return empty($filter) || in_array($category, $filter);
    }
    
    /**
    * Formats a log message given different fields.
    * @param string Message content.
    * @param string Message category.
    * @param integer Timestamp.
    * @return string Formatted message.
    */
    protected function formatLogMessage($e)
    {
        return @gmdate('Y/m/d H:i:s', time()) . " {$e->getMessage()} at {$e->getFile()}:{$e->getLine()} from {$_SERVER['REMOTE_ADDR']}\n";
    }
    
    /**
    * Writes current exception to log file and performs log rotation.
    * @param string Exception to log.
    */
    public function writeToLog($message)
    {
        if (@filesize($this->logFile) > $this->logFileSize * 1024)
            $this->rotateLogs();
        $fp = @fopen($this->logFile, 'a');
        @flock($fp, LOCK_EX);
        @fwrite($fp, $message);
        @flock($fp, LOCK_UN);
        @fclose($fp);
    }
    
    /**
    * Rotates log files.
    */
    protected function rotateLogs()
    {
        for($i = $this->logFilesMax; $i > 0; --$i)
        {
            $rotateFile = $this->logFile . '.' . $i;
            if (is_file($rotateFile))
            {
                if ($i === $this->logFilesMax)
                    @unlink($rotateFile);
                else
                    @rename($rotateFile, $this->logFile . '.' . ($i + 1));
            }
        }
        if (is_file($this->logFile))
            @rename($this->logFile, $this->logFile . '.1');
    }
    
    /**
    * Sends current exception to an email.
    * @param Exception Exception to send.
    */
    public function sendToEmail($message)
    {
        $headers[] = "From: {$this->emailFrom}";
        $headers[] = "Reply-To: {$this->emailFrom}";
        return @mail($this->email, $this->emailSubject, $message, implode("\r\n", $headers));
    }
}