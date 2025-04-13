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

    /**
     * Check if a sheet exists in the Excel file
     * 
     * @param string $excelFile Path to the Excel file
     * @param string $sheetName Name of the sheet to check
     * @return bool True if sheet exists, false otherwise
     */
    private function sheetExists($excelFile, $sheetName)
    {
        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($excelFile);
            $worksheetNames = $reader->listWorksheetNames($excelFile);
            return in_array($sheetName, $worksheetNames);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Load a specific sheet from an Excel file
     * 
     * @param string $excelFile Path to the Excel file
     * @param string $sheetName Name of the sheet to load
     * @return \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet|null The worksheet or null if not found
     */
    private function loadSheet($excelFile, $sheetName)
    {
        try {
            // Check if sheet exists first
            if (!$this->sheetExists($excelFile, $sheetName)) {
                return null;
            }

            // Create reader with optimized settings
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($excelFile);
            if ($reader instanceof \PhpOffice\PhpSpreadsheet\Reader\IReader) {
                $reader->setReadDataOnly(true);
                $reader->setReadEmptyCells(false);
            }

            // Set to load only the target sheet
            $reader->setLoadSheetsOnly([$sheetName]);

            // Load the sheet
            $spreadsheet = $reader->load($excelFile);
            $worksheet = $spreadsheet->getSheetByName($sheetName);

            if (!$worksheet) {
                return null;
            }

            return $worksheet;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if a string is a valid date in YYYY-MM-DD format
     * 
     * @param string $date Date string to check
     * @return bool True if valid date, false otherwise
     */
    private function isDate($date)
    {
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add a record of the uploaded file to the database
     * 
     * @param int $user User ID
     * @param array $uploaded_file File information
     */
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

    /**
     * Converts Excel date values to a standardized Y-m-d format
     *
     * @param mixed $value The Excel date value (either integer or string)
     * @return string|null Formatted date string in Y-m-d format, or null if invalid
     */
    function parseExcelDate($value) {
        // Handle null values
        if ($value === null || $value === '') {
            return null;
        }

        // Try to convert string dates first
        if (is_string($value)) {
            $date = DateTime::createFromFormat('Y-m-d', trim($value));
            if ($date !== false && $date->format('Y-m-d') === trim($value)) {
                return $date->format('Y-m-d');
            }
        }

        // Handle numeric Excel timestamps
        if (is_numeric($value)) {
            // Convert Excel timestamp to Unix timestamp
            $unixTimestamp = round(($value - 25569) * 86400);
            
            // Create date object and validate
            $date = new DateTime();
            $date->setTimestamp($unixTimestamp);
            
            // Verify we got a valid date
            if ($date->format('Y-m-d') !== '1970-01-01') { // Avoid false positives
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    /**
     * Process uploaded Excel file
     * 
     * @return void
     */
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

            $uploadedExcel = $dest;

            // Read member list
            $memberSheet = $this->loadSheet($uploadedExcel, "小組組員");
            if (!$memberSheet) {
                throw new Exception("Members sheet not found");
            }
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

            $sheet = $this->loadSheet($uploadedExcel, "小組架構");
            if (!$sheet) {
                print_r("<p>Cell groups sheet not found - skipping cell groups import");
            } else {
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
            }

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

            $sheet = $this->loadSheet($uploadedExcel, "出席記錄-崇拜");
            if (!$sheet) {
                print_r("<p>Ceremony attendance sheet not found - skipping ceremony attendance import");
            } else {
                $rows = $sheet->toArray();

                // Scan records to get years and values in one pass
                $attendance_ceremony_years = [];
                $attendance_ceremony_values = [];
                foreach ($rows as $idx => $row) {
                    if ($idx == 0) {
                        continue;
                    }  // Skip header
                    if (!empty($row[0]) && !empty($row[1])) {
                        $date = $this->parseExcelDate($row[0]);
                        if ($date !== null) {
                            $year = substr($date, 0, 4);
                            if (!in_array($year, $attendance_ceremony_years)) {
                                $attendance_ceremony_years[] = $year;
                            }
                            $attendance_ceremony_values[] = $db->quote($date) . ', ' . $db->quote($row[1]);
                        }
                    }
                }
                $attendance_ceremony_values = array_unique($attendance_ceremony_values);

                if (empty($attendance_ceremony_years)) {
                    print_r("<p>No valid dates found in ceremony attendance sheet - skipping import");
                } else {
                    // Delete existing records for the years being updated
                    if (!$dryRun) {
                        $query = $db->getQuery(true);
                        $query
                            ->delete($db->quoteName('#__memberportal_attendance_ceremony'))
                            ->where($db->quoteName('date') . ' LIKE ' . $db->quote($attendance_ceremony_years[0] . '%'));
                        for ($i = 1; $i < count($attendance_ceremony_years); $i++) {
                            $query->orWhere($db->quoteName('date') . ' LIKE ' . $db->quote($attendance_ceremony_years[$i] . '%'));
                        }
                        $db->setQuery($query);
                        $db->execute();
                    }

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

                    print_r("<p>Loaded " . count($attendance_ceremony_values) . " ceremony attendance rows for years: " . implode(", ", $attendance_ceremony_years) . ($dryRun ? " (DRY RUN)" : ""));
                }
            }

            ///////////////////////////////////////////////////////////////////////
            // Cell Attendance
            ///////////////////////////////////////////////////////////////////////

            $sheet = $this->loadSheet($uploadedExcel, "出席記錄-小組");
            if (!$sheet) {
                print_r("<p>Cell attendance sheet not found - skipping cell attendance import");
            } else {
                $rows = $sheet->toArray();

                // Scan records to get years and values in one pass
                $attendance_cell_years = [];
                $attendance_cell_values = [];
                foreach ($rows as $idx => $row) {
                    if ($idx == 0) {
                        continue;
                    }  // Skip header
                    if (!empty($row[0])) {
                        $date = $this->parseExcelDate($row[0]);
                        if ($date !== null) {
                            $year = substr($date, 0, 4);
                            if (!in_array($year, $attendance_cell_years)) {
                                $attendance_cell_years[] = $year;
                            }

                            $date = $db->quote($date);
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
                    }
                }
                $attendance_cell_values = array_unique($attendance_cell_values);

                if (empty($attendance_cell_years)) {
                    print_r("<p>No valid dates found in cell attendance sheet - skipping import");
                } else {
                    // Delete existing records for the years being updated
                    if (!$dryRun) {
                        $query = $db->getQuery(true);
                        $query
                            ->delete($db->quoteName('#__memberportal_attendance_cell'))
                            ->where($db->quoteName('date') . ' LIKE ' . $db->quote($attendance_cell_years[0] . '%'));
                        for ($i = 1; $i < count($attendance_cell_years); $i++) {
                            $query->orWhere($db->quoteName('date') . ' LIKE ' . $db->quote($attendance_cell_years[$i] . '%'));
                        }
                        $db->setQuery($query);
                        $db->execute();
                    }

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

                    print_r("<p>Loaded " . count($attendance_cell_values) . " cell attendance rows for years: " . implode(", ", $attendance_cell_years) . ($dryRun ? " (DRY RUN)" : ""));
                }
            }

            ///////////////////////////////////////////////////////////////////////
            // Offerings
            ///////////////////////////////////////////////////////////////////////

            $sheet = $this->loadSheet($uploadedExcel, "奉獻記錄");
            if (!$sheet) {
                print_r("<p>Offerings sheet not found - skipping offerings import");
            } else {
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
            }

            ///////////////////////////////////////////////////////////////////////
            // Offerings v2
            ///////////////////////////////////////////////////////////////////////

            $sheet = $this->loadSheet($uploadedExcel, "奉獻記錄v2");
            if (!$sheet) {
                print_r("<p>Offerings v2 sheet not found - skipping offerings v2 import");
            } else {
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

                print_r("<p>Updating offerings for months from " . date('Y M', strtotime($months[0])) . " to " . date('Y M', strtotime(end($months))));

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
            }

            ///////////////////////////////////////////////////////////////////////
            // Cell Group Schedule
            ///////////////////////////////////////////////////////////////////////

            $sheet = $this->loadSheet($uploadedExcel, "小組日程");
            if (!$sheet) {
                print_r("<p>Cell schedule sheet not found - skipping cell schedule import");
            } else {
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
            }

            ///////////////////////////////////////////////////////////////////////
            // Serving Posts
            ///////////////////////////////////////////////////////////////////////

            $sheet = $this->loadSheet($uploadedExcel, "組員事奉崗位");
            if (!$sheet) {
                print_r("<p>Serving posts sheet not found - skipping serving posts import");
            } else {
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
            }

            ///////////////////////////////////////////////////////////////////////
            // Courses
            ///////////////////////////////////////////////////////////////////////

            $sheet = $this->loadSheet($uploadedExcel, "課程記錄");
            if (!$sheet) {
                print_r("<p>Courses sheet not found - skipping courses import");
            } else {
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
            }
            
            if ($dryRun) {
                print_r("<h3>Dry run completed successfully. No database changes were made.</h3>");
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
}
