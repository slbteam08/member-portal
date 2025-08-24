<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * UploadOfferings View
 *
 * @since 0.0.1
 */
class MemberPortalViewUploadOfferings extends JViewLegacy
{
    /**
     * Private encryption key
     *
     * @var string
     */
    private $key = 'pB1SHjmoQ9jHG+7lfch2o7DosZRE2rmmzq14RtUqtns=';

    /**
     * Display the Upload Offerings view
     *
     * @param string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return void
     */
    public function display($tpl = null)
    {
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }

        $this->key = $this->generateEncryptionKey();
        $this->test_payload = "040200|2000";
        $this->encrypted_payload = $this->encryptText($this->test_payload);
        $this->decrypted_payload = $this->decryptText($this->encrypted_payload);

        // Display the template
        parent::display($tpl);
    }

    /**
     * Encrypt text using AES-256-CBC encryption
     *
     * @param string $text The text to encrypt
     * @param string $key The encryption key (optional, uses component config if not provided)
     * @return string The encrypted text in base64 format
     */
    public function encryptText($text, $key = null)
    {
        if (empty($text)) {
            return '';
        }

        // Use component config key if no key provided
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
     * @param string $key The encryption key (optional, uses component config if not provided)
     * @return string The decrypted text
     */
    public function decryptText($encryptedText, $key = null)
    {
        if (empty($encryptedText)) {
            return '';
        }

        // Use component config key if no key provided
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
}
