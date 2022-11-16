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
		// Get Current User
		$user = Factory::getUser();
		// print_r($user);

		// Set up media paths
		$input = Factory::getApplication()->input;
		$component_name = $input->get('option');
		$media_base = Uri::base() . "media/" . $component_name . "/";
		$images_path = $media_base . "images/";
		$js_path = $media_base . "js/";
		$css_path = $media_base . "css/";

		// Avatar path
		$this->avatar_url = $images_path . "avatar.png";

		// Add JS and CSS to document
		$document = Factory::getDocument();
		$document->addScript($js_path . "bootstrap.bundle.min.js");
		$document->addScript("https://cdn.jsdelivr.net/npm/vue/dist/vue.min.js");
		$document->addScript("https://cdn.jsdelivr.net/npm/apexcharts");
		$document->addScript("https://cdn.jsdelivr.net/npm/vue-apexcharts");
		$document->addStyleSheet($css_path . "bootstrap.min.css");
		$document->addStyleSheet($css_path . "styles.css");

		// Display the view
		parent::display($tpl);
	}
}