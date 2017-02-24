<?php

/**
* CPostman is a tiny component to send text emails.
*/
class CPostman extends CComponent
{
    /**
    * @var string Sender email.
    */
    public $from;
    
    /**
    * @var string Email templates path.
    */
    public $templatePath;
    
    /**
    * Sends an email.
    * 
    * @param string Recipient email address.
    * @param string Sender email address.
    * @param string Email subject.
    */
    public function sendMessage($template, $to, $params = array())
    {
        // Read our template.
        $contents = file($this->templatePath . '/' . $template . '.eml');
        
        // Parse headers.
        $subject = '';
        $headers = '';
        $pattern = '/^([A-Za-z0-9_\-]+)\:\s*(.*)$/';
        while (isset($contents[0]) && preg_match($pattern, $contents[0], $matches))
        {
            // Replace params
            foreach ($params as $key => $value)
                $matches[2] = str_replace('{' . $key . '}', $value, $matches[2]);
            
            if ($matches[1] === 'Subject')
                $subject = $matches[2];
            else
                $headers .= "{$matches[1]}: {$matches[2]}\n";
            
            array_shift($contents);
        }
        
        // Add sender email.
        if ($this->from)
            $headers .= "From: " . $this->from . "\n";
        
        // Implode message lines.
        $contents = trim(implode('', $contents));
        
        // Replace params
        foreach ($params as $key => $value)
            $contents = str_replace('{' . $key . '}', $value, $contents);
        
        // Send our message.
        return $this->deliver($to, $subject, $contents, $headers);
    }
    
    /**
    * Performs message delivery.
    */
    protected function deliver($to, $subject, $message, $headers = null)
    {
        return mail($to, $subject, $message, $headers);
    }
}