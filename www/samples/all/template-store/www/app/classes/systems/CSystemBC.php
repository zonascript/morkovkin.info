<?php

class CSystemBC extends CComponent
{
    const URL_BLOCKCHAIN = 'https://blockchain.info/';
    
    /**
    * @var string Payment units, defaults to 'USD'.
    */
    public $units = 'USD';
    
    /**
    * @var string The bitcoin address to receive funds to.
    */
    public $address;
    
    /**
    * @var string The bitcoin security word.
    */
    public $secret;
    
    /**
    * @var boolean Whether to make a shared transaction (adds the 0.5% fee).
    */
    public $shared = false;
    
    /**
    * @var integer Minimum number of confirmations to reach.
    */
    public $confirmations = 2;
    
    /**
    * @var string Payment form template.
    */
    public $template = false; // '<center>Send <b>%s BTC</b> to <b>%s</b></center><br><br><img src="%s" />';
    
    /**
    * Renders SCI payment HTML form.
    */
    public function renderForm($data, $amount, $statusUrl, $successUrl, $failureUrl)
    {
        // Get price in BTC
        $amountBTC = file_get_contents(self::URL_BLOCKCHAIN . 'tobtc?currency=' . urlencode($this->units) . '&value=' . urlencode($amount));
        
        // Actual callback URL for this payment
        $callback = $statusUrl . '?amount=' . urlencode($amount) . '&btc=' . urlencode($amountBTC) . '&data=' . urlencode($data) . '&secret=' . urlencode($this->secret);
        
        // Generate payment address at blockchain
        $address = json_decode(file_get_contents(self::URL_BLOCKCHAIN . 'api/receive?method=create&address=' . urlencode($this->address) .'&callback=' . urlencode($callback)));
        
        // Create QR code URL
        $qrCodeSrc = self::URL_BLOCKCHAIN . 'qr?data=bitcoin:' . $address->input_address . '&amount=' . $amountBTC;
        
        // Create payment HTML or return array
        if ($this->template)
            return sprintf($this->template, $amountBTC, $address->input_address, $qrCodeSrc);
        
        else
        {
            return array
            (
                'address' => $address->input_address,
                'amount' => $amountBTC,
                'qr_code' => $qrCodeSrc,
            );
        }
    }
    
    /**
    * Accepts and validates payment status request.
    */
    public function acceptStatus(&$data, &$amount, &$account, &$batch, &$error)
    {
        // Do not allow test callbacks
        if (@$_GET['test'] == true)
            $error = 'BitCoin callback requested with a test notification';
        
        // Check number of confirmations
        else if ($_GET['confirmations'] < $this->confirmations)
            $error = 'BitCoin notification has not enough confirmations: ' . $_GET['confirmations'];
        
        // Verify address
        else if ($_GET['destination_address'] !== $this->address)
            $error = 'BitCoin notification address doesn\'t match: ' . $_GET['destination_address'];
        
        // Verify secret word
        else if ($_GET['secret'] !== $this->secret)
            $error = 'BitCoin notification secret word doesn\'t match: ' . $_GET['secret'];
        
        // Verify amount
        else if (floatval(@$_GET['btc']) !== floatval(@$_GET['value'] / 100000000))
            $error = 'BitCoin notification amount doesn\'t match: ' . ($_GET['value'] / 100000000);

        // Success
        else
        {
            $amount = floatval($_GET['amount']);
            $account = ''; // Not set by BlockChain
            $data = $_GET['data'];
            $batch = $_GET['transaction_hash'];
            
            echo '*ok*';
            
            return true;
        }
    }
}