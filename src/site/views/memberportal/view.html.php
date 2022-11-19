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
		$model = $this->getModel();
		$this->num_weeks = $model->getNumWeeks(2021);
		$this->info = $model->getMemberInfo($member_code);
		$this->attd_ceremony_dates = $model->getAttendanceCeremony($member_code);

		// Evaluate attendance arrays
		$this->attd_ceremony_series = array_fill(0, $this->num_weeks, false);
		foreach($this->attd_ceremony_dates as $date) {
			$this->attd_ceremony_series[$date->week_of_year] = true;
		}
		$this->attd_ceremony_cnt = count($this->attd_ceremony_dates);

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
