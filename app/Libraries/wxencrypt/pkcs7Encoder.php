<?php
/**
 * PKCS7Encoder class - 基于PKCS7算法的加解密接口.
 * @copyright Copyright (c) 1998-2014 Tencent Inc.
 */

namespace App\Controllers {

class PKCS7Encoder
{
    public static $block_size = 16;

    function encode($text)
    {
        $block_size = PKCS7Encoder::$block_size;
        $text_length = strlen($text);
        $amount_to_pad = PKCS7Encoder::$block_size - ($text_length % PKCS7Encoder::$block_size);
        if ($amount_to_pad == 0) {
            $amount_to_pad = PKCS7Encoder::block_size;
        }
        $pad_chr = chr($amount_to_pad);
        $tmp = str_repeat($pad_chr, $amount_to_pad);
        return $text . $tmp;
    }

    function decode($text)
    {
        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > 32) {
            $pad = 0;
        }
        return substr($text, 0, (strlen($text) - $pad));
    }
}

class Prpcrypt
{
    public $key;

    function __construct($k)
    {
        $this->key = $k;
    }

    public function decrypt($aesCipher, $aesIV)
    {
        try {
            // Use OpenSSL as mcrypt is removed in PHP 8+
            $decrypted = openssl_decrypt($aesCipher, 'AES-128-CBC', $this->key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $aesIV);
            if ($decrypted === false) {
                return array(ErrorCode::$IllegalBuffer, null);
            }
        } catch (\Exception $e) {
            return array(ErrorCode::$IllegalBuffer, null);
        }

        try {
            $pkc_encoder = new PKCS7Encoder;
            $result = $pkc_encoder->decode($decrypted);
        } catch (\Exception $e) {
            return array(ErrorCode::$IllegalBuffer, null);
        }
        return array(0, $result);
    }
}

} // end namespace

namespace {
    if (!class_exists('PKCS7Encoder', false) && class_exists('App\Controllers\PKCS7Encoder', false)) {
        class_alias('App\Controllers\PKCS7Encoder', 'PKCS7Encoder');
    }
    if (!class_exists('Prpcrypt', false) && class_exists('App\Controllers\Prpcrypt', false)) {
        class_alias('App\Controllers\Prpcrypt', 'Prpcrypt');
    }
}
