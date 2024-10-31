<?php

namespace Rapidmail\Connector\Admin;

class Encrypter
{
    private $key;
    private $cipher;
    private $iv;

    /**
     * @param string $key
     * @param string $iv
     * @param string $cipher
     */
    public function __construct(
        $key = 'RapidmailConnector',
        $iv = '6Sv1IDOgHUVK9t9A',
        $cipher = 'AES256'
    ) {
        $this->key = $key;
        $this->cipher = $cipher;
        $this->iv = $iv;
    }

    public function encrypt(array $data)
    {
        return base64_encode(openssl_encrypt(json_encode($data), $this->cipher, $this->key, 0, $this->iv));
    }
}