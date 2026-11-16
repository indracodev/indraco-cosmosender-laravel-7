<?php

namespace App\Services;

class CosmosenderCipherService
{
    private $cipher = 'AES-128-CTR';

    public function encrypt(string $plainText): string
    {
        $key = env('COSMOSENDER_ENCRYPT_KEY', 'indraco');
        $iv  = env('COSMOSENDER_ENCRYPT_IV', '0909090909090909');

        return (string) openssl_encrypt($plainText, $this->cipher, $key, 0, $iv);
    }

    public function decrypt(string $encryptedText): string
    {
        $key = env('COSMOSENDER_ENCRYPT_KEY', 'indraco');
        $iv  = env('COSMOSENDER_ENCRYPT_IV', '0909090909090909');

        return (string) openssl_decrypt($encryptedText, $this->cipher, $key, 0, $iv);
    }
}

