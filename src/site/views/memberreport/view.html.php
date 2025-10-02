<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

// Import the encryption helper
require_once JPATH_COMPONENT . '/helpers/encryption.php';

/**
 * HTML View class for the MemberPortal Component
 *
 * @since 0.0.1
 */
class MemberPortalViewMemberReport extends JViewLegacy
{
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
        $model = JModelLegacy::getInstance('MemberPortal', 'MemberPortalModel');

        // Determine member code
        $user = Factory::getUser();
        $logged_in_member = $user->username;
        $allow_override = [
            "admin", 
            "040200", // 黃偉豪
            "040010", // 馬錦鋒
            "040059", // 黃超明
            "040022", // 黃超明
            "100022", // 梁鍵文
        ];
        if (in_array($logged_in_member, $allow_override)) {
            $member_code = $input->get("member_code"); // Secret override
            if (is_null($member_code)) {
                $member_code = $user->username;
            } else {
                $this->impersonate_member_code = $member_code;
            }
        } else {
            $member_code = $user->username;
        }

        // Pastor view
        $view_member_code = $input->get("view_member_code"); 
        if (!is_null($view_member_code)) {
            // Check if logged in user has pastor view permission
            $pastor_info = $model->getMemberInfo($member_code);
            if (strpos($pastor_info->cell_role, "主任牧師") !== false) {
                // No filters
            } elseif (strpos($pastor_info->cell_role, "區牧") !== false) {
                $district = $pastor_info->cell_group_name;
            } elseif (strpos($pastor_info->cell_role, "區長") !== false) {
                $zone = $pastor_info->cell_group_name;
            } elseif (strpos($pastor_info->cell_role, "組長") !== false) {
                $cell = $pastor_info->cell_group_name;
            } else {
                $cell = "Invalid";
                $zone = "Invalid";
                $district = "Invalid";
            }
            $pastor_tree = $model->getLatestCellTree($district, $zone, $cell, $view_member_code);
            if (count($pastor_tree) > 0) {
                $this->pastor_view_mode = true;
                $this->pastor_info = $pastor_info;
                $member_code = $view_member_code;
            } else {
                echo  "<span style='color: red'>錯誤：沒有權限檢視該會友資料 (崇拜編碼: " . $view_member_code . ")</span>";
                return;
            }
        }

        $this->info = $model->getMemberInfo($member_code);

        // Report data
        $this->latest_data_date = $model->getLatestDataDate();
        if (is_null($this->latest_data_date)) {
            $this->latest_month = "";
            $this->latest_week = date("W");
            $this->latest_month_num = date("n");
        } else {
            $date_obj = \DateTime::createFromFormat("Y-m-d", $this->latest_data_date);
            $this->latest_month = $date_obj->format("Y 年 n 月");
            $this->latest_week = $date_obj->format("W");
            $this->latest_month_num = $date_obj->format("n");
        }

        $endDate = new DateTime($this->latest_data_date);
        $startDate = clone $endDate;
        $startDate->modify('-11 months');
        $this->startMonth = $startDate->format('Y年m月');
        $this->endMonth = $endDate->format('Y年m月');

        $endDateForFilter = clone $endDate;
        $endDateForFilter->modify('+1 month'); // End date filter is exclusive, so we add 1 month
        $filterStart = $startDate->format('Y-m-01');
        $filterEnd = $endDateForFilter->format('Y-m-01');
        $this->attd_ceremony_dates = $model->getAttendanceCeremonyByRange($member_code, $filterStart, $filterEnd);
        $this->attd_cell_dates = $model->getAttendanceCellByRange($member_code, $filterStart, $filterEnd);
        $this->offering_months = $model->getOfferingMonthsByRange($member_code, $filterStart, $filterEnd);
        $this->cell_schedule = $model->getCellScheduleBeforeDate($filterEnd, 52);
        $this->num_weeks = count($this->cell_schedule);
        $this->no_cell_weeks = count(array_filter($this->cell_schedule, function($item) {
            return is_null($item->week_start);
        }));
        $this->num_cell_weeks = $this->num_weeks - $this->no_cell_weeks;

        $this->attd_ceremony_cnt = count($this->attd_ceremony_dates);
        $this->attd_ceremony_pcnt = (int)round($this->attd_ceremony_cnt / $this->num_weeks * 100);

        $this->attd_cell_cnt = count($this->attd_cell_dates);
        $this->attd_cell_pcnt = (int)round($this->attd_cell_cnt / $this->num_cell_weeks * 100);

        $this->offering_cnt = count($this->offering_months);
        $this->offering_pcnt = (int)round($this->offering_cnt / 12 * 100);


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Offering details
        ///////////////////////////////////////////////////////////////////////////////////////////

        // Only show offering details for the member herself
        if (!$this->pastor_view_mode) {
            $startDateOfferingDetails = new DateTime();
            $currentMonth = (int)$startDateOfferingDetails->format('n');
            if ($currentMonth < 4) {
                $startDateOfferingDetails->modify('last year');
            }
            $startDateOfferingDetails->setDate(
                $startDateOfferingDetails->format('Y'),
                4,
                1
            );
            $filterStartOfferingDetails = $startDateOfferingDetails->format('Y-m-01');
            $filterEndOfferingDetails = $endDateForFilter->format('Y-m-01');
            $this->offering_details = $model->getOfferingDetailsByRange($member_code, $filterStartOfferingDetails, $filterEndOfferingDetails);
            $this->startMonthOfferingDetails = $startDateOfferingDetails->format('Y年m月');
            $this->endMonthOfferingDetails = $endDate->format('Y年m月');

            // Expected offering types
            $this->offering_types = [
                "十一奉獻",
                "感恩奉獻",
                "經常奉獻",
                "建堂基金",
                "福音事工",
                "愛鄰舍基金",
                "特別奉獻"
            ];
            // $this->offering_types = array_unique(array_column($this->offering_details, 'offering_type'));

            // Initialize encryption helper
            $encryption = new MemberPortalEncryption();

            // Build date rows
            $this->offering_details_date_rows = [];
            foreach ($this->offering_details as $offering) {
                if (!isset($this->offering_details_date_rows[$offering->date])) {
                    $this->offering_details_date_rows[$offering->date] = [];
                }

                $decrypted_amount_val = $encryption->decryptText($offering->offering_amount);
                $offering_amount = explode("|", $decrypted_amount_val)[2];
                
                $this->offering_details_date_rows[$offering->date][$offering->offering_type] = $offering_amount;
            }
        }

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
