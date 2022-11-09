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
		$cell_groups = [];
		$member_attrs = [];
		foreach($rows as $idx => $row) {
			if ($idx == 0) continue; // Skip header row
			if (empty($row[0])) continue; // Skip invalid member code

			$members[] = [$row[0], $row[1]]; // Member code and name

			if (!empty($row[2])) {
				// Cell group name
				// - Temp source. Should use 小組架構 sheet instead
				$cell_groups[] = strtoupper(trim($row[2]));
			}

			$member_attrs[] = [$row[0], $row[2], $row[3], $row[4], $row[5], $row[6]];
		}

		// Get DB Query object
		$db = JFactory::getDbo();


		///////////////////////////////////////////////////////////////////////
		// Cell Groups
		///////////////////////////////////////////////////////////////////////

		// Truncate cell groups table
		$db->truncateTable('#__memberportal_cell_groups');

		// Insert cell groups
		$cell_groups = array_unique($cell_groups);
		// print_r($cell_groups);
		// exit();

		$group_values = [];
		foreach($cell_groups as $cell) {
			$group_values[] = $db->quote($cell);
		}

		$query = $db->getQuery(true);
		$columns = array('name');
		$query
			->insert($db->quoteName('#__memberportal_cell_groups'))
    		->columns($db->quoteName($columns))
    		->values($group_values);
		$db->setQuery($query);
		$db->execute();

		print_r("<p>Loaded " . count($group_values) . " cell groups");


		///////////////////////////////////////////////////////////////////////
		// Members
		///////////////////////////////////////////////////////////////////////

		// Truncate members table
		$db->truncateTable('#__memberportal_members');

		// Insert members
		$member = array_unique($member, SORT_REGULAR);
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

		print_r("<p>Loaded " . count($members) . " members");


		///////////////////////////////////////////////////////////////////////
		// Member attributes
		///////////////////////////////////////////////////////////////////////

		// Truncate member attributes table
		$db->truncateTable('#__memberportal_member_attrs');
		
		// Insert member attributes
		$member_attrs_values = [];
		foreach($member_attrs as $attrs) {
			$member_attrs_values[] = implode(', ', [
				$db->quote($attrs[0]),
				$db->quote($attrs[1]),
				$db->quote($attrs[2]),
				$db->quote($attrs[3]),
				$db->quote($attrs[4]),
				$db->quote($attrs[5]),
			]);
		}

		$query = $db->getQuery(true);
		$columns = array('member_code', 'cell_group_name', 'cell_role', 'member_category', 'start_date', 'end_date');
		$query
			->insert($db->quoteName('#__memberportal_member_attrs'))
    		->columns($db->quoteName($columns))
    		->values($member_attrs_values);
		$db->setQuery($query);
		$db->execute();

		print_r("<p>Loaded " . count($member_attrs_values) . " member attribute rows");


		///////////////////////////////////////////////////////////////////////
		// Ceremony Attendance
		///////////////////////////////////////////////////////////////////////

		$sheet = $spreadsheet->getSheetByName("出席記錄-崇拜");
		$rows = $sheet->toArray();

		// Truncate member attributes table
		$db->truncateTable('#__memberportal_attendance_ceremony');

		// Insert ceremony attendance
		$attendance_ceremony_values = [];
		foreach($rows as $idx => $row) {
			if ($idx == 0) continue;  // Skip header
			$attendance_ceremony_values[] = $db->quote($row[0]) . ', ' . $db->quote($row[1]);
		}
		$attendance_ceremony_values = array_unique($attendance_ceremony_values);

		$query = $db->getQuery(true);
		$columns = array('date', 'member_code');
		$query
			->insert($db->quoteName('#__memberportal_attendance_ceremony'))
    		->columns($db->quoteName($columns))
    		->values($attendance_ceremony_values);
		$db->setQuery($query);
		$db->execute();

		print_r("<p>Loaded " . count($attendance_ceremony_values) . " ceremony attendance rows");
	}
}