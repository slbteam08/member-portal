<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/**
 * HTML View class for the MemberPortal Component
 *
 * @since 0.0.1
 */
class MemberPortalViewPastorReport extends JViewLegacy
{
    public function getSaturdays($year)
    {
        $cur_sat = strtotime("first saturday of January " . $year);
        $saturdays = [];
        while (date("Y", $cur_sat) == $year) {
            $saturdays[] = date("Y-m-d", $cur_sat);
            $cur_sat = strtotime("+7 days", $cur_sat);
        }

        return $saturdays;
    }

    public function buildData($rows)
    {
        // Data structure: district -> zone -> cell -> member
        $data = [];
        $week_count = count($this->week_of_year_mapping);

        foreach ($rows as $row) {
            $week_of_year = $this->week_of_year_mapping[$row->week_start];

            # Increment district count
            if (!array_key_exists($row->district, $data)) {
                $data[$row->district] = [
                    "zones" => [],
                    "series" => array_fill(0, $week_count, 0),
                ];
            }
            $district =& $data[$row->district];
            $district["series"][$week_of_year]++;

            # Increment zone count
            if (!array_key_exists($row->zone, $district["zones"])) {
                $district["zones"][$row->zone] = [
                    "cells" => [],
                    "series" => array_fill(0, $week_count, 0),
                ];
            }
            $zone =& $district["zones"][$row->zone];
            $zone["series"][$week_of_year]++;

            # Increment cell count
            if (!array_key_exists($row->cell, $zone["cells"])) {
                $zone["cells"][$row->cell] = [
                    "members" => [],
                    "series" => array_fill(0, $week_count, 0),
                ];
            }
            $cell =& $zone["cells"][$row->cell];
            $cell["series"][$week_of_year]++;

            # Increment member count
            if (!array_key_exists($row->name_chi, $cell["members"])) {
                $cell["members"][$row->name_chi] = [
                    "member_code" => $row->member_code,
                    "series" => array_fill(0, $week_count, 0),
                ];
            }
            $member =& $cell["members"][$row->name_chi];
            $member["series"][$week_of_year]++;
        }

        return $data;
    }

    /**
     * Display the Member Portal view
     *
     * @param string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $input = Factory::getApplication()->input;

        // Determine member code
        $member_code = $input->get("member_code"); // Secret override
        if (is_null($member_code)) {
            $user = Factory::getUser();
            $member_code = $user->username;
            // print_r($user);
        }

        $model = JModelLegacy::getInstance('MemberPortal', 'MemberPortalModel');
        $this->info = $model->getMemberInfo($member_code);

        // Latest data date
        $this->latest_data_date = $model->getLatestDataDate();
        if (is_null($this->latest_data_date)) {
            $this->latest_month = "";
        } else {
            $date_obj = \DateTime::createFromFormat("Y-m-d", $this->latest_data_date);
            $this->latest_month = $date_obj->format("Y 年 n 月");
        }

        // Get year
        $year = $input->get("year"); // Secret override
        if (is_null($year)) {
            $year = 2024;
        }
        $this->year = $year;

        // Date to week of year mapping
        $this->week_of_year_mapping = [];
        foreach ($this->getSaturdays($year) as $key => $saturday) {
            $this->week_of_year_mapping[$saturday] = $key + 1;
        }

        // Report data
        $cell = "";
        $zone = "";
        $district = "";

        if (strpos($this->info->cell_role, "主任牧師") !== false) {
            // No filters
        } elseif (strpos($this->info->cell_role, "區牧") !== false) {
            $district = $this->info->cell_group_name;
        } elseif (strpos($this->info->cell_role, "區長") !== false) {
            $zone = $this->info->cell_group_name;
        } elseif (strpos($this->info->cell_role, "組長") !== false) {
            $cell = $this->info->cell_group_name;
        } else {
            $cell = "Invalid";
            $zone = "Invalid";
            $district = "Invalid";
        }

        // $cell = "偉豪組";
        // $zone = "永賢區";
        // $district = "男士牧區";

        $this->ceremony_attendance = $this->buildData($model->getPastorReportData($year, $district, $zone, $cell, "ceremony"));
        $this->cell_attendance = $this->buildData($model->getPastorReportData($year, $district, $zone, $cell, "cell"));

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
