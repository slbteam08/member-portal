<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Encryption Helper Class
 * Provides encryption and decryption functionality for the Member Portal component
 *
 * @package     MemberPortal
 * @since       0.0.2
 */
class MemberPortalEncryption
{
    /**
     * Private encryption key
     *
     * @var string
     */
    private $key;

    /**
     * Constructor
     *
     * @param string $key Optional custom encryption key
     */
    public function __construct($key = null)
    {
        if ($key === null) {
            // Use default key if none provided
            $this->key = 'pB1SHjmoQ9jHG+7lfch2o7DosZRE2rmmzq14RtUqtns=';
        } else {
            $this->key = $key;
        }
    }

    /**
     * Encrypt text using AES-256-CBC encryption
     *
     * @param string $text The text to encrypt
     * @param string $key The encryption key (optional, uses instance key if not provided)
     * @return string The encrypted text in base64 format
     * @throws Exception If encryption fails
     */
    public function encryptText($text, $key = null)
    {
        if (empty($text)) {
            return '';
        }

        // Use instance key if no key provided
        if ($key === null) {
            $key = $this->key;
        }

        // Generate a random IV
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));
        
        // Encrypt the text
        $encrypted = openssl_encrypt($text, 'AES-256-CBC', $key, 0, $iv);
        
        if ($encrypted === false) {
            throw new Exception('Encryption failed');
        }

        // Combine IV and encrypted data, then base64 encode
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt text that was encrypted with encryptText method
     *
     * @param string $encryptedText The encrypted text in base64 format
     * @param string $key The encryption key (optional, uses instance key if not provided)
     * @return string The decrypted text
     * @throws Exception If decryption fails
     */
    public function decryptText($encryptedText, $key = null)
    {
        if (empty($encryptedText)) {
            return '';
        }

        // Use instance key if no key provided
        if ($key === null) {
            $key = $this->key;
        }

        // Decode from base64
        $data = base64_decode($encryptedText);
        
        if ($data === false) {
            throw new Exception('Invalid base64 data');
        }

        // Extract IV and encrypted data
        $ivLength = openssl_cipher_iv_length('AES-256-CBC');
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);

        // Decrypt the text
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
        
        if ($decrypted === false) {
            throw new Exception('Decryption failed');
        }

        return $decrypted;
    }

    /**
     * Generate a secure random encryption key
     *
     * @param int $length The length of the key (default 32 bytes for AES-256)
     * @return string The generated key in base64 format
     */
    public function generateEncryptionKey($length = 32)
    {
        $key = openssl_random_pseudo_bytes($length);
        return base64_encode($key);
    }

    /**
     * Check if encryption is available on this system
     *
     * @return bool True if encryption functions are available
     */
    public function isEncryptionAvailable()
    {
        return function_exists('openssl_encrypt') && 
               function_exists('openssl_decrypt') && 
               function_exists('openssl_random_pseudo_bytes');
    }

    /**
     * Get the current encryption key
     *
     * @return string The current encryption key
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set a new encryption key
     *
     * @param string $key The new encryption key
     * @return void
     */
    public function setKey($key)
    {
        $this->key = $key;
    }
}
