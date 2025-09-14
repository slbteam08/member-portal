<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

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

        $model = JModelLegacy::getInstance('MemberPortal', 'MemberPortalModel');
        $this->info = $model->getMemberInfo($member_code);

        // Report data
        $this->startMonth = date("Y年m月", strtotime("-12 month"));
        $this->endMonth = date("Y年m月", strtotime("-1 month"));

        $filterStart = date("Y-m-01", strtotime("-12 month"));
        $filterEnd = date("Y-m-01");
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

        $filterStartOfferingDetails = date("Y-m-01", strtotime("-3 month"));
        $filterEndOfferingDetails = date("Y-m-01");
        $this->offering_details = $model->getOfferingDetailsByRange($member_code, $filterStartOfferingDetails, $filterEndOfferingDetails);
        $this->startMonthOfferingDetails = date("Y年m月", strtotime("-3 month"));
        $this->endMonthOfferingDetails = date("Y年m月", strtotime("-1 month"));

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

        // Display the view
        parent::display($tpl);
    }
}
