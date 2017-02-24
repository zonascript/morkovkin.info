<?php

/**
* CSecurity provides private keys, hashing and encryption functions.
*/
class CSecurity extends CComponent
{
    /**
    * @var string The private key used to generate HMAC and to encrypt/decrypt data.
    */
    private $_securityKey;
    
    /**
    * @var string The name of the hashing algorithm to be used by {@link computeHMAC}.
    */
    public $hashAlgorithm = 'sha1';
    
    /**
    * @var mixed The name of the crypt algorithm to be used by {@link encrypt} and {@link decrypt}.
    */
    public $cryptAlgorithm = 'des';
    
    /**
    * @var string Random string charset.
    */
    public $randomCharset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdfghijklmnopqrstuvwxyz0123456789';
    
    /**
    * Initialize component.
    */
    public function init()
    {
        if (!extension_loaded('mcrypt'))
            throw new CException('CCrypt requires PHP mcrypt extension to be loaded in order to use data encryption feature.');
    }
    
    /**
    * @return string The private key used to generate HMAC and to encrypt/decrypt data.
    */
    protected function getSecurityKey()
    {
        return $this->_securityKey;
    }
    
    /**
    * @param string The private key used to generate HMAC and to encrypt/decrypt data.
    * @throws CException If the key is empty.
    */
    public function setSecurityKey($value)
    {
        if (!empty($value))
            $this->_securityKey = $value;
        else
            throw new CException('CCrypt.securityKey cannot be empty.');
    }
    
    /**
    * Encrypts data.
    * @param string Data to be encrypted.
    * @param string The decryption key. This defaults to null, meaning using {@link getEncryptionKey EncryptionKey}.
    * @return string The encrypted data.
    * @throws CException If PHP Mcrypt extension could not be open.
    */
    public function encrypt($data, $key = null)
    {
        if (($module = @mcrypt_module_open($this->cryptAlgorithm, '', MCRYPT_MODE_CBC, '')) === false)
            throw new CException('Failed to initialize the mcrypt module.');
        $key = substr($key === null ? md5($this->securityKey) : $key, 0, mcrypt_enc_get_key_size($module));
        srand();
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($module), MCRYPT_RAND);
        mcrypt_generic_init($module, $key, $iv);
        $encrypted = $iv . mcrypt_generic($module, $data);
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);
        return base64_encode($encrypted);
    }

    /**
    * Decrypts data.
    * @param string Data to be decrypted.
    * @param string The decryption key. This defaults to null, meaning using {@link getEncryptionKey EncryptionKey}.
    * @return string The decrypted data.
    * @throws CException If PHP Mcrypt extension could not be open.
    */
    public function decrypt($data, $key = null)
    {
        if (($module = @mcrypt_module_open($this->cryptAlgorithm, '', MCRYPT_MODE_CBC, '')) === false)
            throw new CException('Failed to initialize the mcrypt module.');
        $data = base64_decode($data);
        $key = substr($key === null ? md5($this->securityKey) : $key, 0, mcrypt_enc_get_key_size($module));
        $ivSize = mcrypt_enc_get_iv_size($module);
        $iv = substr($data, 0, $ivSize);
        mcrypt_generic_init($module, $key, $iv);
        $decrypted = mdecrypt_generic($module, substr($data, $ivSize, strlen($data)));
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);
        return rtrim($decrypted, "\0");
    }
    
    /**
    * Prefixes data with an HMAC.
    * @param string Data to be hashed.
    * @param string The private key to be used for generating HMAC. Defaults to null, meaning using {@link securityKey}.
    * @return string Data prefixed with HMAC.
    */
    public function hashData($data, $key = null)
    {
        return $this->computeHMAC($data, $key) . $data;
    }

    /**
    * Validates if data is tampered.
    * @param string Data to be validated. The data must be previously generated using {@link hashData()}.
    * @param string The private key to be used for generating HMAC. Defaults to null, meaning using {@link securityKey}.
    * @return string The real data with HMAC stripped off. False if the data is tampered.
    */
    public function validateData($data, $key = null)
    {
        $len = strlen($this->computeHMAC('test'));
        if (strlen($data) >= $len)
        {
            $hmac = substr($data, 0, $len);
            $data2 = substr($data, $len, strlen($data));
            return $hmac === $this->computeHMAC($data2, $key) ? $data2 : false;
        }
        else
            return false;
    }

    /**
    * Computes the HMAC for the data with {@link getValidationKey ValidationKey}.
    * @param string Data to be generated HMAC.
    * @param string The private key to be used for generating HMAC. Defaults to null, meaning using {@link securityKey}.
    * @return string The HMAC for the data.
    */
    protected function computeHMAC($data, $key = null)
    {
        if ($key === null)
            $key = $this->securityKey;
        
        if (function_exists('hash_hmac'))
            return hash_hmac($this->hashAlgorithm, $data, $key);
        
        if (!strcasecmp($this->hashAlgorithm, 'sha1'))
        {
            $pack = 'H40';
            $func = 'sha1';
        }
        else
        {
            $pack = 'H32';
            $func = 'md5';
        }
        
        if (strlen($key) > 64) $key = pack($pack, $func($key));
        if (strlen($key) < 64) $key = str_pad($key, 64, chr(0));
        
        $key = substr($key, 0, 64);
        
        return $func((str_repeat(chr(0x5C), 64) ^ $key) . pack($pack, $func((str_repeat(chr(0x36), 64) ^ $key) . $data)));
    }
    
    /**
    * Generates a random password to be used as a temporary password.
    * @param integer password length in bytes. Defaults to 10.
    * @retrun string Randomly generated password.
    */
    public function randomString($length = 16, $charset = null)
    {
        if ($charset === null)
            $charset = $this->randomCharset;
        
        $password = '';
        $possible = str_split($charset);
        while (strlen($password) < $length)
        {
            if (mt_rand() < mt_rand()) shuffle($possible);
            $random = mt_rand(0, count($possible) - 1);
            $password .= $possible[$random];
        }
        
        return $password;
    }
}