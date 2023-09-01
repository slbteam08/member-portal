<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class MemberPortalModelMemberPortal extends JModelLegacy
{
    public function getNumWeeks($year)
    {
        return 52; // Fixed for now
    }

    public function getMemberInfo($member_code)
    {
        // Initialize variables.
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        $subQuery = $db->getQuery(true);

        // Latest attribute rows per member
        $subQuery->select(["m.name_chi", "a.*", "RANK() over (PARTITION BY member_code ORDER BY start_date desc) as attr_rank"])
            ->from($db->quoteName('#__memberportal_members', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__memberportal_member_attrs', 'a')
                    . ' ON ' . $db->quoteName('a.member_code') . ' = ' . $db->quoteName('m.member_code')
            );

        // Create the base select statement.
        $query->select('*')
            ->from("(" . $subQuery . ") AS b")
            ->where("attr_rank = 1")
            ->where("member_code = " . $member_code);

        $db->setQuery($query);
        $row = $db->loadObject();
        //$row = $db->loadObjectList('member_code');

        return $row;
    }

    public function getAttendanceCeremony($member_code, $year)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select([
                'date', 
                "YEARWEEK(date + INTERVAL 2 DAY) as year_week", 
                "WEEKOFYEAR(date + INTERVAL 2 DAY) as week_of_year"
            ])
            ->from($db->quoteName('#__memberportal_attendance_ceremony'))
            ->where("member_code = " . $db->quote($member_code))
            ->where("YEAR(date) = " . $year);

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        return $rows;
    }

    public function getCellSchedule($year)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select([
                'year', 'week', 'week_start'
            ])
            ->from($db->quoteName('#__memberportal_cell_schedule'))
            ->where("year = " . $year);

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        return $rows;
    }

    public function getAttendanceCell($member_code, $year)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select([
                'date', 
                "YEARWEEK(date + INTERVAL 1 DAY) as year_week", 
                "WEEKOFYEAR(date + INTERVAL 1 DAY) as week_of_year",
                "event_type",
            ])
            ->from($db->quoteName('#__memberportal_attendance_cell'))
            ->where("member_code = " . $db->quote($member_code))
            ->where("YEAR(date) = " . $year);

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        return $rows;
    }

    public function getOfferingMonths($member_code, $year)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select([
                'date', 'MONTH(date) as month'
            ])
            ->from($db->quoteName('#__memberportal_offerings'))
            ->where("member_code = " . $db->quote($member_code))
            ->where("YEAR(date) = " . $year);

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        return $rows;
    }

    public function getServingPosts($member_code)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select([
                'DISTINCT post'
            ])
            ->from($db->quoteName('#__memberportal_serving_posts'))
            ->where("member_code = " . $db->quote($member_code))
            ->where("end_date = '0000-00-00'");

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        return $rows;
    }

    public function getCompletedCourses($member_code)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select([
                'DISTINCT course'
            ])
            ->from($db->quoteName('#__memberportal_courses'))
            ->where("member_code = " . $db->quote($member_code))
            ->where("status = '已完成'");

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        return $rows;
    }

    public function getLatestDataMonth()
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select([
                "max(date) as latest_date"
            ])
            ->from($db->quoteName('#__memberportal_attendance_cell'));

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        if (count($rows) == 1) {
            return \DateTime::createFromFormat("Y-m-d", $rows[0]->latest_date)->format("Y 年 n 月");
        } else {
            return "";
        }
    }

    public function getLatestUploadDate()
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select([
                "DATE(CONVERT_TZ(uploaded, '+00:00', '+08:00')) as latest_date"
            ])
            ->from($db->quoteName('#__memberportal_uploaded_files'))
            ->order('id desc')
            ->setLimit('1');

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        if (count($rows) == 1) {
            return \DateTime::createFromFormat("Y-m-d", $rows[0]->latest_date)->format("Y 年 n 月 j 日");
        } else {
            return "";
        }
    }
}
