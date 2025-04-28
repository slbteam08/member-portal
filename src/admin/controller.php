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

        return "ERROR";
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
            print_r("<p>檔案已儲存至 " . $dest);
        } else {
            print_r("儲存檔案失敗");
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
                print_r("<h2>測試模式 - 不會對資料庫進行任何更改</h2>");
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
            $member_attrs_keys = []; // Track unique combinations
            $validation_messages = [
                "小組組員" => [],
                "小組架構" => [],
                "出席記錄-崇拜" => [],
                "出席記錄-小組" => [],
                "奉獻記錄" => [],
                "奉獻記錄v2" => [],
                "小組日程" => [],
                "組員事奉崗位" => [],
                "課程記錄" => []
            ]; // Store validation messages by sheet name

            foreach ($rows as $idx => $row) {
                if ($idx == 0) {
                    continue;
                } // Skip header row
                if (empty($row[0])) {
                    $validation_messages["小組組員"][] = "第 " . ($idx + 1) . " 行：組員編號為空";
                    continue;
                }

                $members[] = [$row[0], $row[1]]; // Member code and name

                if (!empty($row[2])) {
                    // Cell group name
                    // - Temp source. Should use 小組架構 sheet instead
                    $cell_groups[] = strtoupper(trim($row[2]));
                }

                // Parse dates
                $start_date = $this->parseExcelDate($row[5]);
                $end_date = $this->parseExcelDate($row[6]);

                // Check if dates are in wrong format
                if ($row[5] !== '' && $start_date === "ERROR") {
                    $validation_messages["小組組員"][] = "第 " . ($idx + 1) . " 行：組員編號 " . $row[0] . " 的開始日期格式錯誤";
                    continue;
                }
                if ($row[6] !== '' && $end_date === "ERROR") {
                    $validation_messages["小組組員"][] = "第 " . ($idx + 1) . " 行：組員編號 " . $row[0] . " 的結束日期格式錯誤"; 
                    continue;
                }

                // Check if start date and end date are the same
                if ($end_date !== null && $start_date === $end_date) {
                    $validation_messages["小組組員"][] = "第 " . ($idx + 1) . " 行：組員編號 " . $row[0] . " 的開始日期和結束日期不能相同";
                    continue;
                }

                // Create unique key for member code and start date
                $key = $row[0] . '_' . $start_date;

                // Check if this combination already exists
                if (!isset($member_attrs_keys[$key])) {
                    $member_attrs_keys[$key] = true;
                    $member_attrs[] = [
                        $row[0], 
                        $row[2], 
                        $row[3], 
                        $row[4], 
                        $start_date,
                        $end_date
                    ];
                } else {
                    $validation_messages["小組組員"][] = "第 " . ($idx + 1) . " 行：組員編號 " . $row[0] . " 在日期 " . $start_date . " 已有重複記錄";
                }
            }

            // Check if there are any validation errors
            $has_errors = false;
            foreach ($validation_messages as $sheet => $messages) {
                if (!empty($messages)) {
                    $has_errors = true;
                    break;
                }
            }

            if ($has_errors) {
                print_r("<h3>匯入驗證錯誤：</h3>");
                foreach ($validation_messages as $sheet => $messages) {
                    if (!empty($messages)) {
                        print_r("<h4>" . $sheet . "：</h4>");
                        print_r("<ul>");
                        foreach ($messages as $message) {
                            print_r("<li>" . $message . "</li>");
                        }
                        print_r("</ul>");
                    }
                }
                print_r("<p>請修正以上錯誤後重新匯入。</p>");
                return;
            }

            // Get DB Query object
            $db = JFactory::getDbo();

            ///////////////////////////////////////////////////////////////////////
            // Cell Groups
            ///////////////////////////////////////////////////////////////////////

            $sheet = $this->loadSheet($uploadedExcel, "小組架構");
            if (!$sheet) {
                print_r("<p>找不到小組架構工作表 - 跳過小組架構匯入");
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

                print_r("<p>已載入 " . count($group_values) . " 個小組" . ($dryRun ? " (測試模式)" : ""));
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

            print_r("<p>已載入 " . count($members) . " 位組員" . ($dryRun ? " (測試模式)" : ""));

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

            print_r("<p>已載入 " . count($member_attrs_values) . " 筆組員屬性資料" . ($dryRun ? " (測試模式)" : ""));

            ///////////////////////////////////////////////////////////////////////
            // Ceremony Attendance
            ///////////////////////////////////////////////////////////////////////

            $sheet = $this->loadSheet($uploadedExcel, "出席記錄-崇拜");
            if (!$sheet) {
                print_r("<p>找不到崇拜出席記錄工作表 - 跳過崇拜出席記錄匯入");
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
                    print_r("<p>在崇拜出席記錄工作表中找不到有效日期 - 跳過匯入");
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

                    print_r("<p>已載入 " . count($attendance_ceremony_values) . " 筆崇拜出席記錄，年份：" . implode("、", $attendance_ceremony_years) . ($dryRun ? " (測試模式)" : ""));
                }
            }

            ///////////////////////////////////////////////////////////////////////
            // Cell Attendance
            ///////////////////////////////////////////////////////////////////////

            $sheet = $this->loadSheet($uploadedExcel, "出席記錄-小組");
            if (!$sheet) {
                print_r("<p>找不到小組出席記錄工作表 - 跳過小組出席記錄匯入");
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
                    print_r("<p>在小組出席記錄工作表中找不到有效日期 - 跳過匯入");
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

                    print_r("<p>已載入 " . count($attendance_cell_values) . " 筆小組出席記錄，年份：" . implode("、", $attendance_cell_years) . ($dryRun ? " (測試模式)" : ""));
                }
            }

            ///////////////////////////////////////////////////////////////////////
            // Offerings
            ///////////////////////////////////////////////////////////////////////

            $sheet = $this->loadSheet($uploadedExcel, "奉獻記錄");
            if (!$sheet) {
                print_r("<p>找不到奉獻記錄工作表 - 跳過奉獻記錄匯入");
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

                print_r("<p>已載入 " . count($offering_values) . " 筆奉獻記錄" . ($dryRun ? " (測試模式)" : ""));
            }

            ///////////////////////////////////////////////////////////////////////
            // Offerings v2
            ///////////////////////////////////////////////////////////////////////

            $sheet = $this->loadSheet($uploadedExcel, "奉獻記錄v2");
            if (!$sheet) {
                print_r("<p>找不到奉獻記錄v2工作表 - 跳過奉獻記錄v2匯入");
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

                print_r("<p>已載入 " . count($offering_values) . " 筆奉獻記錄v2，從 " . date('Y M', strtotime($months[0])) . " 至 " . date('Y M', strtotime(end($months))) . ($dryRun ? " (測試模式)" : ""));
            }

            ///////////////////////////////////////////////////////////////////////
            // Cell Group Schedule
            ///////////////////////////////////////////////////////////////////////

            $sheet = $this->loadSheet($uploadedExcel, "小組日程");
            if (!$sheet) {
                print_r("<p>找不到小組日程工作表 - 跳過小組日程匯入");
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

                print_r("<p>已載入 " . count($schedule_values) . " 筆小組日程" . ($dryRun ? " (測試模式)" : ""));
            }

            ///////////////////////////////////////////////////////////////////////
            // Serving Posts
            ///////////////////////////////////////////////////////////////////////

            $sheet = $this->loadSheet($uploadedExcel, "組員事奉崗位");
            if (!$sheet) {
                print_r("<p>找不到事奉崗位工作表 - 跳過事奉崗位匯入");
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

                print_r("<p>已載入 " . count($post_values) . " 筆事奉崗位記錄" . ($dryRun ? " (測試模式)" : ""));
            }

            ///////////////////////////////////////////////////////////////////////
            // Courses
            ///////////////////////////////////////////////////////////////////////

            $sheet = $this->loadSheet($uploadedExcel, "課程記錄");
            if (!$sheet) {
                print_r("<p>找不到課程記錄工作表 - 跳過課程記錄匯入");
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

                print_r("<p>已載入 " . count($course_values) . " 筆課程記錄" . ($dryRun ? " (測試模式)" : ""));
            }
            
            if ($dryRun) {
                print_r("<h3>測試模式已完成。未對資料庫進行任何更改。</h3>");
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
}
