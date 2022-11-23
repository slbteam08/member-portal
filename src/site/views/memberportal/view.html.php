<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/**
 * HTML View class for the MemberPortal Component
 *
 * @since  0.0.1
 */
class MemberPortalViewMemberPortal extends JViewLegacy
{
	/**
	 * Display the Member Portal view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	function display($tpl = null)
	{
		$input = Factory::getApplication()->input;

		// Determine member code
		$member_code = $input->get("member_code"); // Secret override
		if (is_null($member_code)) {
			$user = Factory::getUser();
			$member_code = $user->username;
			// print_r($user);
		}

		// Get member data
		$year = 2021;
		$model = $this->getModel();
		$this->num_weeks = $model->getNumWeeks($year);
		$this->cell_schedule = $model->getCellSchedule($year);
		$this->info = $model->getMemberInfo($member_code);
		$this->attd_ceremony_dates = $model->getAttendanceCeremony($member_code);
		$this->attd_cell_dates = $model->getAttendanceCell($member_code);

		// Attendance code
		$ceremony_present = 6;
		$ceremony_absent = 5;
		$zone_present = 8;
		$zone_absent = 9; // No use
		$cell_present = 10;
		$cell_absent = 11;
		$no_cell = 12;

		// Evaluate attendance arrays
		$this->attd_ceremony_series = array_fill(1, $this->num_weeks, $ceremony_absent);
		foreach($this->attd_ceremony_dates as $date) {
			$this->attd_ceremony_series[$date->week_of_year] = $ceremony_present;
		}
		$this->attd_ceremony_cnt = count($this->attd_ceremony_dates);

		$this->attd_cell_series = [];
		foreach($this->cell_schedule as $cell_date) {
			$week = $cell_date->week;
			if (is_null($cell_date->week_start)) {
				$this->attd_cell_series[$week] = $no_cell;
			} else {
				$this->attd_cell_series[$week] = $cell_absent;
			}
		}
		foreach($this->attd_cell_dates as $date) {
			if ($date->event_type == "小組") {
				$present = $cell_present;
			} else {
				$present = $zone_present;
			}
			$this->attd_cell_series[$date->week_of_year] = $present;
		}
		$this->attd_cell_cnt = count($this->attd_cell_dates);

		// Set up media paths
		$component_name = $input->get('option');
		$media_base = Uri::base() . "media/" . $component_name;
		$this->images_path = $media_base . "/images";
		$this->js_path = $media_base . "/js";
		$this->css_path = $media_base . "/css";

		// Add JS and CSS to document
		$document = Factory::getDocument();
		$document->addScript($this->js_path . "/bootstrap.bundle.min.js");
		$document->addScript("https://cdn.jsdelivr.net/npm/vue/dist/vue.min.js");
		$document->addScript("https://cdn.jsdelivr.net/npm/apexcharts");
		$document->addScript("https://cdn.jsdelivr.net/npm/vue-apexcharts");
		$document->addStyleSheet($this->css_path . "/bootstrap.min.css");
		$document->addStyleSheet($this->css_path . "/styles.css");

		// Display the view
		parent::display($tpl);
	}
}
