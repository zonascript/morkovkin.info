<?php

class CSystemEP extends CComponent
{
    const URL_SCI = 'https://www.egopay.com/payments/pay/form';
    const URL_SCI_CHECK = 'https://www.egopay.com/payments/request';
    
    public $units = 'USD';
    
    public $account;
    public $sciId;
    public $sciPass;
    
    /**
    * Renders SCI payment HTML form.
    */
    public function renderForm($data, $amount, $statusUrl, $successUrl, $failureUrl)
    {
        // $verify = array($this->sciPass, $amount, $data, $this->units);
        // $verify = hash('sha256', implode('|', $verify));
        
        $fields = array
        (
            'store_id' => $this->sciId,
            'amount' => $amount,
            'currency' => $this->units,
            'cf_1' => $data,
            'success_url' => $successUrl,
            'fail_url' => $failureUrl,
            'callback_url' => $statusUrl,
            // 'description' => '...',
            // 'verify' => $verify,
        );
        
        $form = sprintf('<form action="%s" method="%s">' . "\n", self::URL_SCI, 'POST') . "\n";
        foreach ($fields as $name => $value)
            $form .= sprintf('<input type="hidden" name="%s" value="%s">', $name, $value) . "\n";
        
        return $form . '</form>';
    }
    
    /**
    * Accepts and validates payment status request.
    */
    public function acceptStatus(&$data, &$amount, &$account, &$batch, &$error)
    {
        if (!isset($_POST['product_id']))
            $error = "Ego Pay status message doesn't provide product_id";

        else
        {
            $request = array
            (
                'product_id' => $_POST['product_id'],
                'store_id' => $this->sciId,
                'security_password' => $this->sciPass,
                'v' => '1.1'
            );
            
            if (!is_string($string = $this->performApiRequest(self::URL_SCI_CHECK, $request, $error)))
                $error = "Ego Pay failed to verify SCI status: $string";
            
            else if (strpos($string, 'INVALID') !== false)
                $error = 'Ego Pay has reported our request is INVALID';

            else
            {
                parse_str($string, $response);
                
                if (!is_array($response))
                    $error = 'Ego Pay returned response that could not be parsed';
                
                else if ($response['sStatus'] !== 'Completed')
                    $error = 'Ego Pay is working in test mode';
                
                else if ($response['sCurrency'] !== $this->units)
                    $error = 'Ego Pay currency has been forged: ' . $response['sCurrency'];
                
                else
                {
                    $amount = floatval(@$response['fAmount']);
                    $account = @$response['sEmail'];
                    $data = @$response['cf_1'];
                    $batch = @$response['sId'];
                    
                    return true;
                }
            }
        }
    }
    
    /**
    * Performs Solid Trust Pay API request.
    * @param string Request URL.
    * @param array Request data.
    * @param string Error description.
    * @return array Response data.
    */
    protected function performApiRequest($url, $request, &$error)
    {
        $string = '';
        foreach ($request as $key => $value)
            $string .= $key . '=' . urlencode($value) . '&';
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
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