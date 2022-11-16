<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class MemberPortalModelUploadedFiles extends JModelLegacy
{
	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return      string  An SQL query
	 */
	protected function getUploadedFiles()
	{
		// Initialize variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
                ->from($db->quoteName('#__memberportal_uploaded_files'));

		return $query;
	}
}