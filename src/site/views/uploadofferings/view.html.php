<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Import the encryption helper
require_once JPATH_COMPONENT . '/helpers/encryption.php';

/**
 * UploadOfferings View
 *
 * @since 0.0.1
 */
class MemberPortalViewUploadOfferings extends JViewLegacy
{
    /**
     * Encryption helper instance
     *
     * @var MemberPortalEncryption
     */
    private $encryption;

    /**
     * Display the Upload Offerings view
     *
     * @param string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return void
     */
    public function display($tpl = null)
    {
        // Initialize encryption helper
        $encryption = new MemberPortalEncryption();

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }

        // Display the template
        parent::display($tpl);
    }
}
