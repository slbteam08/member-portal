<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

/**
 * HTML View class for the MemberPortal Component
 *
 * @since  0.0.1
 */
class MemberPortalViewMemberReport extends JViewLegacy
{
    /**
     * Display the Member Portal view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
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

        // Report data
        $this->startMonth = date("Y年m月", strtotime("-12 month"));
        $this->endMonth = date("Y年m月", strtotime("-1 month"));

        $filterStart = date("Y-m-01", strtotime("-12 month"));
        $filterEnd = date("Y-m-01");
        $this->numWeeks = $model->getNumWeeksInRange($filterStart, $filterEnd);
        $this->attd_ceremony_dates = $model->getAttendanceCeremonyByRange($member_code, $filterStart, $filterEnd);
        $this->attd_cell_dates = $model->getAttendanceCellByRange($member_code, $filterStart, $filterEnd);
        $this->offering_months = $model->getOfferingMonthsByRange($member_code, $filterStart, $filterEnd);

        $this->attd_ceremony_cnt = count($this->attd_ceremony_dates);
        $this->attd_ceremony_pcnt = (int)round($this->attd_ceremony_cnt / $this->numWeeks * 100);

        $this->attd_cell_cnt = count($this->attd_cell_dates);
        $this->attd_cell_pcnt = (int)round($this->attd_cell_cnt / $this->numWeeks * 100);

        $this->offering_cnt = count($this->offering_months);
        $this->offering_pcnt = (int)round($this->offering_cnt / 12 * 100);

        // Display the view
        parent::display($tpl);
    }
}
