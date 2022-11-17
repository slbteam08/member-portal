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
	// protected function getMemberInfo($member_code)
	// {
	// 	// Initialize variables.
	// 	$db    = JFactory::getDbo();
	// 	$query = $db->getQuery(true);
    //     $subQuery = $db-getQuery(true);

    //     // Latest attribute rows per member
    //     $subQuery->select(["m.name_chi", "a.*", "RANK() over (PARTITION BY member_code ORDER BY start_date desc) as attr_rank"])
    //              ->from($db->quoteName('#__memberportal_members', 'm'))
    //              ->join(
    //                 'INNER', 
    //                 $db->quoteName('#__memberportal_member_attrs', 'a') 
    //                 . ' ON ' . $db->quoteName('a.member_code') . ' = ' . $db->quoteName('m.member_code')
    //              );

	// 	// Create the base select statement.
	// 	$query->select('*')
    //            ->from("(" . $subQuery . ") AS b")
    //            ->where("attr_rank = 1")
    //            ->where("member_code = " . $member_code);

    //     $db->setQuery($query);
    //     $row = $db->loadAssocList('member_code');

	// 	return $row;
	// }

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
		// var_dump($member_code);

		// Get Member Info
		//$info = $this->get("Msg");
		$model = $this->getModel();
		$this->info = $model->getMemberInfo($member_code);
		var_dump($this->info);

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