<?php

class CHelpDesk extends CComponent
{
    /**
    * @var string Ticket API creator email.
    */
    public $username;
    
    /**
    * @var string Ticket API website ID.
    */
    public $website;
    
    /**
    * @var string Ticket API creator password.
    */
    public $password;
    
    /**
    * @var string Ticket API new ticket category.
    */
    public $category;
    
    /**
    * @var string Whether created ticket should be public.
    */
    public $public = false;
    
    /**
    * @var string Whether created ticket should skip spam.
    */
    public $skipSpam = true;
    
    /**
    * Creates a ticket in a remote system.
    */
    public function createTicket($email, $title, $body, $params = array())
    {
        $url = 'https://api.tenderapp.com/%s/categories/%s/discussions';
        $url = sprintf($url, $this->website, $this->category);
        
        $request = array
        (
            'email' => $email,
            'title' => $title,
            'body' => $body,
            'public' => $this->public,
            'skip_spam' => $this->skipSpam,
        );
        
        if (!empty($params))
            $request['extras'] = $params;
        
        // echo json_encode($request); exit();
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/vnd.tender-v1+json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        if (($result = curl_exec($ch)) === false)
        {
            $error = '[' . curl_errno($ch) . '] ' . curl_error($ch);
            curl_close($ch);
            return false;
        }
        
        curl_close($ch);
        
        return $result;
    }
}