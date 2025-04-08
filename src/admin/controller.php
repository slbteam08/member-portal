<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Import PhpSpreadsheet library
jimport('phpspreadsheet.phpspreadsheet');

class MemberPortalController extends JControllerLegacy
{
    /**
     * The default view for the display method.
     */
    protected $default_view = 'admin';

    private function isDate($date)
    {
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date)) {
            return true;
        } else {
            return false;
        }
    }

    public function addUploadedFile($user, $uploaded_file)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $uploaded_file_values = [
            implode(
                ", ",
                [
                $db->quote($user),
                $db->quote($uploaded_file["orig_file_name"]),
                $db->quote($uploaded_file["saved_file_name"]),
                $db->quote($uploaded_file["import_result"]),
                ]
            )
        ];

        $columns = array('uploaded_by', 'orig_file_name', 'saved_file_name', 'import_result');
        $query
            ->insert($db->quoteName('#__memberportal_uploaded_files'))
            ->columns($db->quoteName($columns))
            ->values($uploaded_file_values);
        $db->setQuery($query);
        $db->execute();
    }

    public function uploadExcel()
    {
        $app = JFactory::getApplication();
        $input = $app->input;
        $file  = $input->files->get('upload_file');
        $dryRun = $input->getBool('dry_run', false);

        // Save uploaded file
        // - Destination folder: /var/www/clients/client1/web1/web/administrator/components/com_memberportal/uploads
        $ts = date("Ymd_His");
        $ext = JFile::getExt($file['name']);
        $filename = JFile::makeSafe($ts . "." . $ext);

        $src = $file['tmp_name'];
        $dest = JPATH_COMPONENT . DS . "uploads" . DS . $filename;

        if (JFile::upload($src, $dest)) {
            print_r("<p>Saved file to " . $dest);
        } else {
            print_r("Failed to save file");
            exit(0);
        }

        // Add uploaded file record
        $uploaded_file = [
            "orig_file_name" => $file['name'],
            "saved_file_name" => $filename,
            "import_result" => $dryRun ? "Dry Run" : "Successful",
        ];
        $user = JFactory::getUser();
        $this->addUploadedFile($user->id, $uploaded_file);

        try {
            // Display dry run mode message
            if ($dryRun) {
                print_r("<h2>DRY RUN MODE - No database changes will be made</h2>");
            }

            // Read member list
            $uploadedExcel = $dest;
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploadedExcel);
            $memberSheet = $spreadsheet->getSheetByName("小組組員");
            $rows = $memberSheet->toArray();

            $members = [];
            $cell_groups = [];
            $member_attrs = [];
            foreach ($rows as $idx => $row) {
                if ($idx == 0) {
                    continue;
                } // Skip header row
                if (empty($row[0])) {
                    continue;
                } // Skip invalid member code

                $members[] = [$row[0], $row[1]]; // Member code and name

                if (!empty($row[2])) {
                    // Cell group name
                    // - Temp source. Should use 小組架構 sheet instead
                    $cell_groups[] = strtoupper(trim($row[2]));
                }

                $member_attrs[] = [$row[0], $row[2], $row[3], $row[4], $row[5], $row[6]];
            }

            // Get DB Query object
            $db = JFactory::getDbo();


            ///////////////////////////////////////////////////////////////////////
            // Cell Groups
            ///////////////////////////////////////////////////////////////////////

            $sheet = $spreadsheet->getSheetByName("小組架構");
            $rows = $sheet->toArray();

            // Truncate cell groups table
            if (!$dryRun) {
                $db->truncateTable('#__memberportal_cell_groups');
            }

            // Insert cell groups
            $group_values = [];
            $spaces = array(" ", "　");
            foreach ($rows as $row) {
                $group_values[] = implode(
                    ', ',
                    [
                        $db->quote(strtoupper(trim(str_replace($spaces, "", $row[0])))),
                        $db->quote(trim($row[1])),
                        $db->quote(trim($row[2])),
                        $db->quote(trim($row[3])),
                        $db->quote(trim($row[4])),
                    ]
                );
            }

            if (!$dryRun) {
                $query = $db->getQuery(true);
                $columns = array('name', 'district', 'zone', 'start_date', 'end_date');
                $query
                    ->insert($db->quoteName('#__memberportal_cell_groups'))
                    ->columns($db->quoteName($columns))
                    ->values($group_values);
                $db->setQuery($query);
                $db->execute();
            }

            print_r("<p>Loaded " . count($group_values) . " cell groups" . ($dryRun ? " (DRY RUN)" : ""));


            ///////////////////////////////////////////////////////////////////////
            // Members
            ///////////////////////////////////////////////////////////////////////

            // Truncate members table
            if (!$dryRun) {
                $db->truncateTable('#__memberportal_members');
            }

            // Insert members
            $members = array_unique($members, SORT_REGULAR);
            $member_values = [];
            foreach ($members as $member) {
                $member_values[] = $db->quote($member[0]) . ', ' . $db->quote($member[1]);
            }

            if (!$dryRun) {
                $query = $db->getQuery(true);
                $columns = array('member_code', 'name_chi');
                $query
                    ->insert($db->quoteName('#__memberportal_members'))
                    ->columns($db->quoteName($columns))
                    ->values($member_values);
                $db->setQuery($query);
                $db->execute();
            }

            print_r("<p>Loaded " . count($members) . " members" . ($dryRun ? " (DRY RUN)" : ""));


            ///////////////////////////////////////////////////////////////////////
            // Member attributes
            ///////////////////////////////////////////////////////////////////////

            // Truncate member attributes table
            if (!$dryRun) {
                $db->truncateTable('#__memberportal_member_attrs');
            }

            // Insert member attributes
            $member_attrs_values = [];
            foreach ($member_attrs as $attrs) {
                $member_attrs_values[] = implode(
                    ', ',
                    [
                    $db->quote($attrs[0]),
                    $db->quote($attrs[1]),
                    $db->quote($attrs[2]),
                    $db->quote($attrs[3]),
                    $db->quote($attrs[4]),
                    $db->quote($attrs[5]),
                    ]
                );
            }

            if (!$dryRun) {
                $query = $db->getQuery(true);
                $columns = array('member_code', 'cell_group_name', 'cell_role', 'member_category', 'start_date', 'end_date');
                $query
                    ->insert($db->quoteName('#__memberportal_member_attrs'))
                    ->columns($db->quoteName($columns))
                    ->values($member_attrs_values);
                $db->setQuery($query);
                $db->execute();
            }

            print_r("<p>Loaded " . count($member_attrs_values) . " member attribute rows" . ($dryRun ? " (DRY RUN)" : ""));


            ///////////////////////////////////////////////////////////////////////
            // Ceremony Attendance
            ///////////////////////////////////////////////////////////////////////

            $sheet = $spreadsheet->getSheetByName("出席記錄-崇拜");
            $rows = $sheet->toArray();

            // Truncate member attributes table
            if (!$dryRun) {
                $db->truncateTable('#__memberportal_attendance_ceremony');
            }

            print_r("<p>Truncated ceremony attendance rows" . ($dryRun ? " (DRY RUN)" : ""));

            // Insert ceremony attendance
            $attendance_ceremony_values = [];
            foreach ($rows as $idx => $row) {
                if ($idx == 0) {
                    continue;
                }  // Skip header
                $attendance_ceremony_values[] = $db->quote($row[0]) . ', ' . $db->quote($row[1]);
            }
            $attendance_ceremony_values = array_unique($attendance_ceremony_values);
            print_r("<p>Unique ceremony attendance rows: " . count($attendance_ceremony_values));

            if (!$dryRun) {
                $query = $db->getQuery(true);
                $columns = array('date', 'member_code');
                $query
                    ->insert($db->quoteName('#__memberportal_attendance_ceremony'))
                    ->columns($db->quoteName($columns))
                    ->values($attendance_ceremony_values);
                $db->setQuery($query);
                $db->execute();
            }

            print_r("<p>Loaded " . count($attendance_ceremony_values) . " ceremony attendance rows" . ($dryRun ? " (DRY RUN)" : ""));


            ///////////////////////////////////////////////////////////////////////
            // Cell Attendance
            ///////////////////////////////////////////////////////////////////////

            $sheet = $spreadsheet->getSheetByName("出席記錄-小組");
            $rows = $sheet->toArray();

            // Truncate member attributes table
            if (!$dryRun) {
                $db->truncateTable('#__memberportal_attendance_cell');
            }

            // Insert cell attendance
            $attendance_cell_values = [];
            foreach ($rows as $idx => $row) {
                if ($idx == 0) {
                    continue;
                }  // Skip header

                $date = $db->quote($row[0]);
                if (empty($row[1])) {
                    $member_code = "NULL";
                } else {
                    $member_code = $db->quote($row[1]);
                }
                if (empty($row[2])) {
                    $visitor_name = "NULL";
                } else {
                    $visitor_name = $db->quote($row[2]);
                }
                $cell_group_name = $db->quote($row[3]);
                $event_type = $db->quote($row[4]);

                $attendance_cell_values[] = implode(
                    ', ',
                    [
                    $date, $member_code, $visitor_name, $cell_group_name, $event_type
                    ]
                );
            }
            $attendance_cell_values = array_unique($attendance_cell_values);

            if (!$dryRun) {
                $query = $db->getQuery(true);
                $columns = array('date', 'member_code', 'visitor_name', 'cell_group_name', 'event_type');
                $query
                    ->insert($db->quoteName('#__memberportal_attendance_cell'))
                    ->columns($db->quoteName($columns))
                    ->values($attendance_cell_values);
                $db->setQuery($query);
                $db->execute();
            }

            print_r("<p>Loaded " . count($attendance_cell_values) . " cell attendance rows" . ($dryRun ? " (DRY RUN)" : ""));


            ///////////////////////////////////////////////////////////////////////
            // Offerings
            ///////////////////////////////////////////////////////////////////////

            $sheet = $spreadsheet->getSheetByName("奉獻記錄");
            $rows = $sheet->toArray();

            // Truncate member attributes table
            if (!$dryRun) {
                $db->truncateTable('#__memberportal_offerings');
            }

            // Insert offerings
            $offering_members = [];
            foreach ($rows as $idx => $row) {
                if ($idx == 0) {
                    continue;
                }  // Skip header

                $date_arr = date_parse_from_format("j/n/Y", $row[0]);
                if ($date_arr["error_count"] > 0) {
                    // Parse with another format
                    $date_arr = date_parse_from_format("Y-m", $row[0]);
                    $date_arr["day"] = 1;
                }
                $date = implode("-", [$date_arr["year"], $date_arr["month"], $date_arr["day"]]);
                $member_code = $row[1];
                if (empty($row[2])) {
                    $num_offerings = 1; // Default count 1 time
                } else {
                    $num_offerings = $row[2];
                }

                // Put into dict for deduplication
                if (!array_key_exists($member_code, $offering_members)) {
                    $offering_members[$member_code] = [];
                }
                $member_dates = &$offering_members[$member_code];
                if (in_array($date, $member_dates)) {
                    $member_dates[$date] = max($member_dates[$date], $num_offerings);
                } else {
                    $member_dates[$date] = $num_offerings;
                }
            }

            $offering_values = [];
            foreach ($offering_members as $member_code => $member_dates) {
                foreach ($member_dates as $date => $num_offerings) {
                    $offering_values[] = implode(
                        ', ',
                        [
                        $db->quote($date), $db->quote($member_code), $num_offerings
                        ]
                    );
                }
            }
            $offering_values = array_unique($offering_values);

            if (!$dryRun) {
                $query = $db->getQuery(true);
                $columns = array('date', 'member_code', 'num_offerings');
                $query
                    ->insert($db->quoteName('#__memberportal_offerings'))
                    ->columns($db->quoteName($columns))
                    ->values($offering_values);
                $db->setQuery($query);
                $db->execute();
            }

            print_r("<p>Loaded " . count($offering_values) . " offering rows" . ($dryRun ? " (DRY RUN)" : ""));


            ///////////////////////////////////////////////////////////////////////
            // Offerings v2
            ///////////////////////////////////////////////////////////////////////

            $sheet = $spreadsheet->getSheetByName("奉獻記錄v2");
            $rows = $sheet->toArray();

            // Insert offerings
            $offering_members = [];
            $months = [];
            foreach ($rows as $idx => $row) {
                if ($idx == 0) {
                    // Parse months from columns header
                    for ($col=1; $col<count($row); $col++) {
                        $title = $row[$col];
                        $date_arr = date_parse_from_format("Y-m", $title);
                        $date_arr["day"] = 1;
                        $date = implode("-", [$date_arr["year"], $date_arr["month"], $date_arr["day"]]);
                        $months[] = $date;
                    }
                    continue;  // Skip header
                }

                $member_code = $row[0];

                // Parse columns
                for ($col=1; $col<count($row); $col++) {
                    if (!empty($row[$col])) {
                        $num_offerings = $row[$col];
                        $date = $months[$col-1];

                        // Put into dict for deduplication
                        if (!array_key_exists($member_code, $offering_members)) {
                            $offering_members[$member_code] = [];
                        }
                        $member_dates = &$offering_members[$member_code];
                        if (in_array($date, $member_dates)) {
                            $member_dates[$date] = max($member_dates[$date], $num_offerings);
                        } else {
                            $member_dates[$date] = $num_offerings;
                        }
                    }
                }
            }

            $offering_values = [];
            foreach ($offering_members as $member_code => $member_dates) {
                foreach ($member_dates as $date => $num_offerings) {
                    $offering_values[] = implode(
                        ', ',
                        [
                        $db->quote($date), $db->quote($member_code), $num_offerings
                        ]
                    );
                }
            }
            $offering_values = array_unique($offering_values);

            // Delete months covered by v2 sheet
            if (!$dryRun) {
                $query = $db->getQuery(true);
                $query
                    ->delete($db->quoteName('#__memberportal_offerings'))
                    ->where($db->quoteName('date') . ' between ' . $db->quote($months[0]) . ' and '. $db->quote(end($months)));
                $db->setQuery($query);
                $db->execute();

                // Insert rows
                $query = $db->getQuery(true);
                $columns = array('date', 'member_code', 'num_offerings');
                $query
                    ->insert($db->quoteName('#__memberportal_offerings'))
                    ->columns($db->quoteName($columns))
                    ->values($offering_values);
                $db->setQuery($query);
                $db->execute();
            }

            print_r("<p>Loaded " . count($offering_values) . " offering v2 rows" . ($dryRun ? " (DRY RUN)" : ""));


            ///////////////////////////////////////////////////////////////////////
            // Cell Group Schedule
            ///////////////////////////////////////////////////////////////////////

            $sheet = $spreadsheet->getSheetByName("小組日程");
            $rows = $sheet->toArray();

            // Truncate cell groups table
            if (!$dryRun) {
                $db->truncateTable('#__memberportal_cell_schedule');
            }

            $schedule_values = [];
            foreach ($rows as $idx => $row) {
                if ($idx == 0) {
                    continue;
                }  // Skip header
                if (empty($row[0])) {
                    continue;
                } // Skip invalid member code

                $year = $row[0];
                $week = $row[1];
                if ($this->isDate($row[2])) {
                    $week_start = $db->quote($row[2]);
                } else {
                    $week_start = "NULL";
                }

                $schedule_values[] = $year . ", " . $week . ", " . $week_start;
            }

            if (!$dryRun) {
                $query = $db->getQuery(true);
                $columns = array('year', 'week', 'week_start');
                $query
                    ->insert($db->quoteName('#__memberportal_cell_schedule'))
                    ->columns($db->quoteName($columns))
                    ->values($schedule_values);
                $db->setQuery($query);
                $db->execute();
            }

            print_r("<p>Loaded " . count($schedule_values) . " cell schedule dates" . ($dryRun ? " (DRY RUN)" : ""));


            ///////////////////////////////////////////////////////////////////////
            // Serving Posts
            ///////////////////////////////////////////////////////////////////////

            $sheet = $spreadsheet->getSheetByName("組員事奉崗位");
            $rows = $sheet->toArray();

            // Truncate cell groups table
            if (!$dryRun) {
                $db->truncateTable('#__memberportal_serving_posts');
            }

            $post_values = [];
            foreach ($rows as $idx => $row) {
                if ($idx == 0) {
                    continue;
                }  // Skip header
                if (empty($row[0])) {
                    continue;
                } // Skip invalid member code

                $member_code = $row[0];
                $name = $row[1];
                $post = $row[2];
                $start = $row[3];
                $end = $row[4];

                $post_values[] = implode(
                    ', ',
                    [
                    $db->quote($member_code),
                    $db->quote($name),
                    $db->quote($post),
                    $db->quote($start),
                    $db->quote($end)
                    ]
                );
            }

            if (!$dryRun) {
                $query = $db->getQuery(true);
                $columns = array('member_code', 'name', 'post', 'start_date', 'end_date');
                $query
                    ->insert($db->quoteName('#__memberportal_serving_posts'))
                    ->columns($db->quoteName($columns))
                    ->values($post_values);
                $db->setQuery($query);
                $db->execute();
            }

            print_r("<p>Loaded " . count($post_values) . " serving post rows" . ($dryRun ? " (DRY RUN)" : ""));


            ///////////////////////////////////////////////////////////////////////
            // Courses
            ///////////////////////////////////////////////////////////////////////

            $sheet = $spreadsheet->getSheetByName("課程記錄");
            $rows = $sheet->toArray();

            // Truncate cell groups table
            if (!$dryRun) {
                $db->truncateTable('#__memberportal_courses');
            }

            $course_values = [];
            foreach ($rows as $idx => $row) {
                if ($idx == 0) {
                    continue;
                }  // Skip header
                if (empty($row[0])) {
                    continue;
                } // Skip invalid member code

                $member_code = $row[0];
                $name = $row[1];
                $course = $row[2];
                $start = $row[3];
                $end = $row[4];
                $status = $row[5];

                $course_values[] = implode(
                    ', ',
                    [
                    $db->quote($member_code),
                    $db->quote($name),
                    $db->quote($course),
                    $db->quote($start),
                    $db->quote($end),
                    $db->quote($status)
                    ]
                );
            }

            if (!$dryRun) {
                $query = $db->getQuery(true);
                $columns = array('member_code', 'name', 'course', 'start_date', 'end_date', 'status');
                $query
                    ->insert($db->quoteName('#__memberportal_courses'))
                    ->columns($db->quoteName($columns))
                    ->values($course_values);
                $db->setQuery($query);
                $db->execute();
            }

            print_r("<p>Loaded " . count($course_values) . " course rows" . ($dryRun ? " (DRY RUN)" : ""));
            
            if ($dryRun) {
                print_r("<h3>Dry run completed successfully. No database changes were made.</h3>");
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
}
