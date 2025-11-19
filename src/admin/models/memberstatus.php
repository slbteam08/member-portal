<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class MemberPortalModelMemberStatus extends JModelLegacy
{
  public function getAllMembers()
  {
    $db    = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query->select(
      [
        'm.member_code',
        'name_chi',
        'member_category'
      ]
    )
      ->from($db->quoteName('#__memberportal_members', 'm'))
      ->leftJoin(
        $db->quoteName('#__memberportal_member_attrs', 'a') 
        . ' ON ' . $db->quoteName('m.member_code') . ' = ' . $db->quoteName('a.member_code')
        . ' AND ' . $db->quoteName('a.end_date') . ' = ' . $db->quote('0000-00-00')
      )
      ->order('m.member_code asc');

    $db->setQuery($query);
    $rows = $db->loadObjectList();

    return $rows ?: [];
  }

  public function getAttendanceCeremonyByRange($start, $end)
  {
    $db    = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query->select(
      [
        'member_code',
        'count(distinct YEARWEEK(date + INTERVAL 2 DAY)) as num_weeks'
      ]
    )
      ->from($db->quoteName('#__memberportal_attendance_ceremony'))
      ->group($db->quoteName('member_code'))
      ->where("date >= " . $db->quote($start))
      ->where("date < " . $db->quote($end));

    $db->setQuery($query);
    $rows = $db->loadObjectList('member_code');

    return $rows ?: [];
  }

  public function getAttendanceCellByRange($start, $end)
  {
    $db    = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query->select(
      [
        'member_code',
        'count(distinct YEARWEEK(date + INTERVAL 2 DAY)) as num_weeks'
      ]
    )
      ->from($db->quoteName('#__memberportal_attendance_cell'))
      ->group($db->quoteName('member_code'))
      ->where("date >= " . $db->quote($start))
      ->where("date < " . $db->quote($end));

    $db->setQuery($query);
    $rows = $db->loadObjectList('member_code');

    return $rows ?: [];
  }

  public function getOfferingMonthsByRange($start, $end)
  {
    $db    = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query->select(
      [
        'member_code',
        'count(distinct MONTH(date)) as num_months'
      ]
    )
      ->from($db->quoteName('#__memberportal_offerings'))
      ->group($db->quoteName('member_code'))
      ->where("date >= " . $db->quote($start))
      ->where("date < " . $db->quote($end));

    $db->setQuery($query);
    $rows = $db->loadObjectList('member_code');

    return $rows ?: [];
  }

}
