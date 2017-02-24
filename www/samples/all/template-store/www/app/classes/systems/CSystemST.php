<?php

/**
* TSolidTrustPay manages Solid Trust Pay operations.
* Supported payment units are: USD, EUR, GBP, AUD, CAD, NZD.
*/
class CSystemST extends CComponent
{
    const URL_SCI = 'https://solidtrustpay.com/handle.php';
    
    public $units = 'USD';
    
    public $account;
    public $sciName = 'SCI';
    public $sciPass;
    
    /**
    * Renders SCI payment HTML form.
    */
    public function renderForm($data, $amount, $statusUrl, $successUrl, $failureUrl)
    {
        
        $fields = array
        (
            'merchantAccount' => $this->account,
            'sci_name' => $this->sciName,
            'item_id' => $data, // length not specified
            'amount' => $amount,
            'currency' => $this->units,
            'memo' => $data,
            'testmode' => 'OFF',
            
            'notify_url' => $statusUrl,
            'return_url' => $successUrl,
            'cancel_url' => $failureUrl,
            
            // 'return_method' => 'POST',
            // 'cancel_method' => 'POST', // experimental, non-specified field
        );
        
        $form = sprintf('<form action="%s" method="%s">' . "\n", self::URL_SCI, 'POST');
        foreach ($fields as $name => $value)
            $form .= sprintf('<input type="hidden" name="%s" value="%s">', $name, $value) . "\n";
        
        return $form . '</form>';
    }
    
    /**
    * Accepts and validates payment status request.
    */
    public function acceptStatus(&$data, &$amount, &$account, &$batch, &$error)
    {
        if (!isset($_POST['hash']))
            $error = "Solid Trust Pay status message doesn't provide hash";
            
        else
        {
            $check = '';
            $check .= $_POST['tr_id'] . ':';
            $check .= md5(md5($this->sciPass . 's+E_a*')) . ':';
            $check .= $_POST['amount'] . ':';
            $check .= $_POST['merchantAccount'] . ':';
            $check .= $_POST['payerAccount'];
            
            // mail('grandmoneyfx@gmail.com', 'STP DEBUG', hash('md5', $check)  . ' ' . @$_POST['hash'] . print_r($_POST, true));
            
            if (hash('md5', $check) !== @$_POST['hash'])
                $error = "Solid Trust Pay status message could not be verified. Either SCI is misconfigured or status message is forged.";
            
            else if (@$_POST['tr_id'] === 'test999')
                $error = "Solid Trust Pay has been forced into a test mode by modifying SCI form, preventing attack";
            
            else if (@$_POST['testmode'] !== 'OFF')
                $error = "Solid Trust Pay has been forced into a test mode by modifying SCI form, preventing attack";

            else if ($this->units != ($units = @$_POST['currency']))
                $error = "Solid Trust Pay status message validated, but provides a mismatching currency: $units";
                
            else
            {
                $amount = floatval(@$_POST['amount']);
                $account = @$_POST['payerAccount'];
                $data = @$_POST['item_id'];
                $batch = @$_POST['tr_id'];
                
                return true;
            }
        } 
    }
}
