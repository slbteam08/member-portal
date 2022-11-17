<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class MemberPortalModelMemberPortal extends JModelLegacy
{
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
}