<?php
/**
 * Created by PhpStorm.
 * User: wangpenghai
 * Date: 2018/08/04
 * Time: 下午6:53
 */

namespace pfWechat\Core\Support;

use Exception;
use pfWechat\Code\Support\XML;

/**
 * Class Encryptor.
 */
class Encryptor
{
    /**
     * App id.
     *
     * @var string
     */
    protected $appId;

    /**
     * App token.
     *
     * @var string
     */
    protected $token;

    /**
     * AES key.
     *
     * @var string
     */
    protected $AESKey;

    /**
     * Block size.
     *
     * @var int
     */
    protected $blockSize;

    /**
     * Constructor.
     *
     * @param string $appId
     * @param string $token
     * @param string $AESKey
     */
    public function __construct($appId, $token, $AESKey)
    {
        $this->appId = $appId;
        $this->token = $token;
        $this->AESKey = $AESKey;
        $this->blockSize = 32;
    }

    /**
     * Encrypt the message and return XML.
     *
     * @param string $xml
     * @param string $nonce
     * @param int    $timestamp
     *
     * @return string
     */
    public function encryptMsg($xml, $nonce = null, $timestamp = null)
    {
        $encrypt = $this->encrypt($xml, $this->appId);

        !is_null($nonce) || $nonce = substr($this->appId, 0, 10);
        !is_null($timestamp) || $timestamp = time();

        //生成安全签名
        $signature = $this->getSHA1($this->token, $timestamp, $nonce, $encrypt);

        $response = [
            'Encrypt' => $encrypt,
            'MsgSignature' => $signature,
            'TimeStamp' => $timestamp,
            'Nonce' => $nonce,
        ];

        //生成响应xml
        return XML::build($response);
    }

    /**
     * Decrypt message.
     *
     * @param string $msgSignature
     * @param string $nonce
     * @param string $timestamp
     * @param string $postXML
     *
     * @return array
     *
     * @throws EncryptionException
     */
    public function decryptMsg($msgSignature, $nonce, $timestamp, $postXML)
    {
        try {
            $array = XML::parse($postXML);
        } catch (BaseException $e) {
            throw new Exception('Invalid xml.', EncryptionException::ERROR_PARSE_XML);
        }

        $encrypted = $array['Encrypt'];

        $signature = $this->getSHA1($this->token, $timestamp, $nonce, $encrypted);

        if ($signature !== $msgSignature) {
            throw new Exception('Invalid Signature.', EncryptionException::ERROR_INVALID_SIGNATURE);
        }

        return XML::parse($this->decrypt($encrypted, $this->appId));
    }

    /**
     * Get SHA1.
     *
     * @return string
     *
     * @throws EncryptionException
     */
    public function getSHA1()
    {
        try {
            $array = func_get_args();
            sort($array, SORT_STRING);

            return sha1(implode($array));
        } catch (BaseException $e) {
            throw new Exception($e->getMessage(), EncryptionException::ERROR_CALC_SIGNATURE);
        }
    }

    /**
     * Encode string.
     *
     * @param string $text
     *
     * @return string
     */
    public function encode($text)
    {
        $padAmount = $this->blockSize - (strlen($text) % $this->blockSize);

        $padAmount = $padAmount !== 0 ? $padAmount : $this->blockSize;

        $padChr = chr($padAmount);

        $tmp = '';

        for ($index = 0; $index < $padAmount; ++$index) {
            $tmp .= $padChr;
        }

        return $text.$tmp;
    }

    /**
     * Decode string.
     *
     * @param string $decrypted
     *
     * @return string
     */
    public function decode($decrypted)
    {
        $pad = ord(substr($decrypted, -1));

        if ($pad < 1 || $pad > $this->blockSize) {
            $pad = 0;
        }

        return substr($decrypted, 0, (strlen($decrypted) - $pad));
    }

    /**
     * Return AESKey.
     *
     * @return string
     *
     * @throws InvalidConfigException
     */
    protected function getAESKey()
    {
        if (empty($this->AESKey)) {
            throw new InvalidConfigException("Configuration mission, 'aes_key' is required.");
        }

        if (strlen($this->AESKey) !== 43) {
            throw new InvalidConfigException("The length of 'aes_key' must be 43.");
        }

        return base64_decode($this->AESKey.'=', true);
    }

    /**
     * Encrypt string.
     *
     * @param string $text
     * @param string $appId
     *
     * @return string
     *
     * @throws EncryptionException
     */
    private function encrypt($text, $appId)
    {
        try {
            $key = $this->getAESKey();
            $random = $this->getRandomStr();
            $text = $this->encode($random.pack('N', strlen($text)).$text.$appId);

            $iv = substr($key, 0, 16);

            $encrypted = openssl_encrypt($text, 'aes-256-cbc', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv);

            return base64_encode($encrypted);
        } catch (BaseException $e) {
            throw new Exception($e->getMessage(), EncryptionException::ERROR_ENCRYPT_AES);
        }
    }

    /**
     * Decrypt message.
     *
     * @param string $encrypted
     * @param string $appId
     *
     * @return string
     *
     * @throws EncryptionException
     */
    private function decrypt($encrypted, $appId)
    {
        try {
            $key = $this->getAESKey();
            $ciphertext = base64_decode($encrypted, true);
            $iv = substr($key, 0, 16);

            $decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv);
        } catch (BaseException $e) {
            throw new Exception($e->getMessage(), EncryptionException::ERROR_DECRYPT_AES);
        }

        try {
            $result = $this->decode($decrypted);

            if (strlen($result) < 16) {
                return '';
            }

            $content = substr($result, 16, strlen($result));
            $listLen = unpack('N', substr($content, 0, 4));
            $xmlLen = $listLen[1];
            $xml = substr($content, 4, $xmlLen);
            $fromAppId = trim(substr($content, $xmlLen + 4));
        } catch (BaseException $e) {
            throw new Exception($e->getMessage(), EncryptionException::ERROR_INVALID_XML);
        }

        if ($fromAppId !== $appId) {
            throw new Exception('Invalid appId.', EncryptionException::ERROR_INVALID_APPID);
        }

        $dataSet = json_decode($xml, true);
        if (JSON_ERROR_NONE === json_last_error()) {
            // For mini-program JSON formats.
            // Convert to XML if the given string can be decode into a data array.
            $xml = XML::build($dataSet);
        }

        return $xml;
    }

    /**
     * Generate random string.
     *
     * @return string
     */
    private function getRandomStr()
    {
        return substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz'), 0, 16);
    }
}

/**
 * Class EncryptionException.
 */
class EncryptionException
{
    const ERROR_INVALID_SIGNATURE = -40001; // Signature verification failed
    const ERROR_PARSE_XML = -40002; // Parse XML failed
    const ERROR_CALC_SIGNATURE = -40003; // Calculating the signature failed
    const ERROR_INVALID_AESKEY = -40004; // Invalid AESKey
    const ERROR_INVALID_APPID = -40005; // Check AppID failed
    const ERROR_ENCRYPT_AES = -40006; // AES Encryption failed
    const ERROR_DECRYPT_AES = -40007; // AES decryption failed
    const ERROR_INVALID_XML = -40008; // Invalid XML
    const ERROR_BASE64_ENCODE = -40009; // Base64 encoding failed
    const ERROR_BASE64_DECODE = -40010; // Base64 decoding failed
    const ERROR_XML_BUILD = -40011; // XML build failed
    const ILLEGAL_BUFFER = -41003; // Illegal buffer
}
