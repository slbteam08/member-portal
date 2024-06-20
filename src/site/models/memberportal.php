<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class MemberPortalModelMemberPortal extends JModelLegacy
{
    public function getNumWeeks($year)
    {
        $date = new DateTime();
        $date->setISODate($year, 53);
        return ($date->format("W") === "53" ? 53 : 52);
    }

    public function getNumWeeksInRange($start, $end)
    {
        return 52; // Hardcode for now
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

        $query->select(
            [
                'date',
                "date - INTERVAL DAYOFWEEK(date) % 7 DAY as week_start",
                "YEARWEEK(date + INTERVAL 2 DAY) as year_week",
                "WEEKOFYEAR(date - INTERVAL DAYOFWEEK(date) % 7 DAY) as week_of_year"
            ]
        )
            ->from($db->quoteName('#__memberportal_attendance_ceremony'))
            ->where("member_code = " . $db->quote($member_code))
            ->where("YEAR(date - INTERVAL DAYOFWEEK(date) % 7 DAY) = " . $year);

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        return $rows;
    }

    public function getAttendanceCeremonyByRange($member_code, $start, $end)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select(
            [
                'date',
                "YEARWEEK(date + INTERVAL 2 DAY) as year_week",
                "WEEKOFYEAR(date - INTERVAL DAYOFWEEK(date) % 7 DAY) as week_of_year"
            ]
        )
            ->from($db->quoteName('#__memberportal_attendance_ceremony'))
            ->where("member_code = " . $db->quote($member_code))
            ->where("date >= " . $db->quote($start))
            ->where("date < ". $db->quote($end));

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        return $rows;
    }

    public function getCellSchedule($year)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select(
            [
                'year', 'week', 'week_start'
            ]
        )
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

        $query->select(
            [
                'date',
                "date - INTERVAL DAYOFWEEK(date) % 7 DAY as week_start",
                "YEARWEEK(date + INTERVAL 2 DAY) as year_week",
                "WEEKOFYEAR(date - INTERVAL DAYOFWEEK(date) % 7 DAY) as week_of_year",
                "event_type",
            ]
        )
            ->from($db->quoteName('#__memberportal_attendance_cell'))
            ->where("member_code = " . $db->quote($member_code))
            ->where("YEAR(date - INTERVAL DAYOFWEEK(date) % 7 DAY) = " . $year);

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        return $rows;
    }

    public function getAttendanceCellByRange($member_code, $start, $end)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select(
            [
                'date',
                "YEARWEEK(date + INTERVAL 2 DAY) as year_week",
                "WEEKOFYEAR(date - INTERVAL DAYOFWEEK(date) % 7 DAY) as week_of_year",
                "event_type",
            ]
        )
            ->from($db->quoteName('#__memberportal_attendance_cell'))
            ->where("member_code = " . $db->quote($member_code))
            ->where("date >= " . $db->quote($start))
            ->where("date < " . $db->quote($end));

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        return $rows;
    }

    public function getOfferingMonths($member_code, $year)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select(
            [
                'date', 'MONTH(date) as month'
            ]
        )
            ->from($db->quoteName('#__memberportal_offerings'))
            ->where("member_code = " . $db->quote($member_code))
            ->where("YEAR(date) = " . $year);

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        return $rows;
    }

    public function getOfferingMonthsByRange($member_code, $start, $end)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select(
            [
                'date', 'MONTH(date) as month'
            ]
        )
            ->from($db->quoteName('#__memberportal_offerings'))
            ->where("member_code = " . $db->quote($member_code))
            ->where("date >= " . $db->quote($start))
            ->where("date < " . $db->quote($end));

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        return $rows;
    }

    public function getServingPosts($member_code)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select(
            [
                'DISTINCT post'
            ]
        )
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

        $query->select(
            [
                'DISTINCT course'
            ]
        )
            ->from($db->quoteName('#__memberportal_courses'))
            ->where("member_code = " . $db->quote($member_code))
            ->where("status = '完成'");

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        return $rows;
    }

    public function getLatestDataDate()
    {
        // Latest week start of data
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select(
            [
                "max(date) - INTERVAL DAYOFWEEK(date) % 7 DAY as latest_date"
            ]
        )
            ->from($db->quoteName('#__memberportal_attendance_cell'));

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        if (count($rows) == 1) {
            return $rows[0]->latest_date;
        } else {
            return null;
        }
    }

    public function getLatestUploadDate()
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select(
            [
                "DATE(CONVERT_TZ(uploaded, '+00:00', '+08:00')) as latest_date"
            ]
        )
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

    public function getPastorReportData($year, $district, $zone, $cell, $attendance_type)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Filter by the most granular level
        if ($cell != "") {
            $filter = "g.name = " . $db->quote($cell);
        } elseif ($zone != "") {
            $filter = "g.zone = " . $db->quote($zone);
        } elseif ($district != "") {
            $filter = "g.district = " . $db->quote($district);
        } else {
            $filter = "";
        }

        $query->select(
            [
                "m.name_chi",
                "m.member_code",
                "g.name as cell",
                "g.zone",
                "g.district",
                "date",
                "date - INTERVAL DAYOFWEEK(date) % 7 DAY as week_start",
            ]
        )
        ->from($db->quoteName('#__memberportal_cell_groups', 'g'))
        ->join(
            'INNER',
            $db->quoteName('#__memberportal_member_attrs', 't')
                . ' ON ' . $db->quoteName('t.cell_group_name') . ' = ' . $db->quoteName('g.name')
        )
        ->join(
            'INNER',
            $db->quoteName('#__memberportal_members', 'm')
                . ' ON ' . $db->quoteName('m.member_code') . ' = ' . $db->quoteName('t.member_code')
        )
        ->join(
            'INNER',
            $db->quoteName('#__memberportal_attendance_'.$attendance_type, 'a')
                . ' ON ' . $db->quoteName('a.member_code') . ' = ' . $db->quoteName('m.member_code')
        )
        ->where("YEAR(date - INTERVAL DAYOFWEEK(date) % 7 DAY) = " . $year)
        ->order('district asc, zone asc, cell asc, member_code asc, date asc');

        if ($filter != "") {
            $query->where($filter);
        }

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        return $rows;
    }
}
