<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Import PhpSpreadsheet library
jimport('phpspreadsheet.phpspreadsheet');

class MemberPortalController extends JControllerLegacy
{
	/**
	 * The default view for the display method.
	 */
	protected $default_view = 'admin';

	public function uploadExcel() {
		$app = JFactory::getApplication(); 
		$input = $app->input;
		$file  = $input->files->get('upload_file');

		// Read member list
		$uploadedExcel = $file["tmp_name"];
		$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploadedExcel);
		$memberSheet = $spreadsheet->getSheetByName("小組組員");
		$rows = $memberSheet->toArray();
		
		$members = [];
		foreach($rows as $idx => $row) {
			if ($idx == 0) continue; // Skip header row

			if (empty($row[0])) continue; // Skip invalid member code

			$members[] = [$row[0], $row[1]];
		}

		// Get DB Query object
		$db = JFactory::getDbo();

		// Truncate table
		$db->truncateTable('#__memberportal_members');

		// Insert members
		$member_values = [];
		foreach($members as $member) {
			$member_values[] = $db->quote($member[0]) . ', ' . $db->quote($member[1]);
		}

		$query = $db->getQuery(true);
		$columns = array('member_code', 'name_chi');
		$query
			->insert($db->quoteName('#__memberportal_members'))
    		->columns($db->quoteName($columns))
    		->values($member_values);

		$db->setQuery($query);
		$db->execute();

		print_r("Loaded " . count($members) . " members");
	}
}