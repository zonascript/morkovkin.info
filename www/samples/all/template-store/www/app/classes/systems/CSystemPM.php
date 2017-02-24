<?php

/**
* TPerfectMoneyGateway manages Perfect Money operations.
* Supported payment units are: USD, EUR, OAU.
*/
class CSystemPM extends CComponent
{
    const URL_SCI = 'https://perfectmoney.is/api/step1.asp';
    
    public $units = 'USD';
    
    public $payeeName = 'Hyip Payment';
    public $userId;
    public $account;
    public $sciPass;
    
    /**
    * Renders SCI payment HTML form.
    */
    public function renderForm($data, $amount, $statusUrl, $successUrl, $failureUrl)
    {
        $fields = array
        (
            'PAYMENT_ID' => $data, // length not specified
            'PAYMENT_AMOUNT' => $amount,
            'PAYEE_ACCOUNT' => $this->account,
            'PAYEE_NAME' => $this->payeeName,
            'PAYMENT_UNITS' => $this->units,
            'STATUS_URL' => $statusUrl,
            'PAYMENT_URL' => $successUrl,
            'NOPAYMENT_URL' => $failureUrl,
            'PAYMENT_URL_METHOD' => 'POST',
            'NOPAYMENT_URL_METHOD' => 'POST',
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
        if (!isset($_POST['V2_HASH']))
            $error = "Perfect Money status message doesn't provide hash";
        
        else
        {
            $check = '';
            
            $check .= @$_POST['PAYMENT_ID'] . ':';
            $check .= @$_POST['PAYEE_ACCOUNT'] . ':';
            $check .= @$_POST['PAYMENT_AMOUNT'] . ':';
            $check .= @$_POST['PAYMENT_UNITS'] . ':';
            $check .= @$_POST['PAYMENT_BATCH_NUM'] . ':';
            $check .= @$_POST['PAYER_ACCOUNT'] . ':';
            $check .= strtoupper(md5($this->sciPass)) . ':';
            $check .= @$_POST['TIMESTAMPGMT'];
            
            if (strtoupper(md5($check)) !== @$_POST['V2_HASH'])
                $error = "Perfect Money status message could not be verified. Either SCI is misconfigured or status message is forged.";
                
            else if ($this->units != ($units = @$_POST['PAYMENT_UNITS']))
                $error = "Perfect Money status message validated, but provides a mismatching currency [PAYMENT_UNITS = $units]";
                
            else
            {
                $amount = floatval(@$_POST['PAYMENT_AMOUNT']);
                $account = @$_POST['PAYER_ACCOUNT'];
                $data = @$_POST['PAYMENT_ID'];
                $batch = @$_POST['PAYMENT_BATCH_NUM'];
                
                return true;
            }
        }
    }
}

