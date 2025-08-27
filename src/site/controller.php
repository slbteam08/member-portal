<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Import PhpSpreadsheet library
jimport('phpspreadsheet.phpspreadsheet');

// Import the encryption helper
require_once JPATH_COMPONENT . '/helpers/encryption.php';

/**
 * Member Portal Component Controller
 *
 * @since 0.0.1
 */
class MemberPortalController extends JControllerLegacy
{
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
      ->insert($db->quoteName('#__memberportal_offering_details_uploaded_files'))
      ->columns($db->quoteName($columns))
      ->values($uploaded_file_values);
    $db->setQuery($query);
    $db->execute();
    return $db->insertid();
  }

  /**
   * Converts Excel date values to a standardized Y-m-d format
   *
   * @param mixed $value The Excel date value (either integer or string)
   * @return string|null Formatted date string in Y-m-d format, or null if invalid
   */
  function parseExcelDate($value)
  {
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
   * Encryption helper instance
   *
   * @var MemberPortalEncryption
   */
  private $encryption;

  /**
   * Constructor
   */
  public function __construct()
  {
    parent::__construct();
    $this->encryption = new MemberPortalEncryption();
  }

  /**
   * Encrypt sensitive data before storing
   *
   * @param string $data The data to encrypt
   * @return string The encrypted data
   */
  public function encryptData($data)
  {
    return $this->encryption->encryptText($data);
  }

  /**
   * Decrypt sensitive data after retrieving
   *
   * @param string $encryptedData The encrypted data to decrypt
   * @return string The decrypted data
   */
  public function decryptData($encryptedData)
  {
    return $this->encryption->decryptText($encryptedData);
  }

  /**
   * Process uploaded Excel file
   * 
   * @return void
   */
  public function uploadOfferings()
  {
    $app = JFactory::getApplication();
    $input = $app->input;
    $file  = $input->files->get('upload_file');
    $dryRun = $input->getBool('dry_run', false);

    // Save uploaded file
    // - Destination folder: /var/www/clients/client1/web1/web/components/com_memberportal/uploads
    $ts = date("Ymd_His");
    $ext = JFile::getExt($file['name']);
    $filename = JFile::makeSafe($ts . "." . $ext);

    $src = $file['tmp_name'];
    $dest = JPATH_COMPONENT . DS . "uploads" . DS . $filename;

    if (JFile::upload($src, $dest)) {
      // print_r("<p>檔案已儲存至 " . $dest);
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
    $upload_id = $this->addUploadedFile($user->id, $uploaded_file);

    try {
      // Display dry run mode message
      if ($dryRun) {
        print_r("<h2>測試模式 - 不會對資料庫進行任何更改</h2>");
      }

      $uploadedExcel = $dest;

      // Initialize arrays for all sheets
      $validation_messages = [
        "奉獻記錄明細" => [],
      ]; // Store validation messages by sheet name

      ///////////////////////////////////////////////////////////////////////
      // First Pass: Run all validations
      ///////////////////////////////////////////////////////////////////////

      // Validate Offering Details Sheet
      $offeringDetailsSheet = $this->loadSheet($uploadedExcel, "奉獻記錄明細");
      $offeringKeys = [];  // Track unique combinations of member code, date and offering type
      if (!$offeringDetailsSheet) {
        $validation_messages["奉獻記錄明細"][] = "找不到奉獻記錄明細工作表";
      } else {
        $rows = $offeringDetailsSheet->toArray();
        foreach ($rows as $idx => $row) {
          if ($idx == 0) continue; // Skip header row

          // Check if member code is empty
          if (empty($row[1])) {
            $validation_messages["奉獻記錄明細"][] = "第 " . ($idx + 1) . " 行：崇拜編碼為空";
            continue;
          }

          // Parse date
          $date = $this->parseExcelDate($row[0]);
          if ($date === "ERROR") {
            $validation_messages["奉獻記錄明細"][] = "第 " . ($idx + 1) . " 行：日期格式錯誤";
            continue;
          }

          // (Member code, date, offering type) must be unique
          $offeringKey = $row[1] . "|" . $date . "|" . $row[2];
          if (in_array($offeringKey, $offeringKeys)) {
            $validation_messages["奉獻記錄明細"][] = "第 " . ($idx + 1) . " 行：重複的奉獻記錄";
            continue;
          }

          $offeringKeys[] = $offeringKey;
        }
      }

      // Check if there are any validation errors across all sheets
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

      ///////////////////////////////////////////////////////////////////////
      // Second Pass: Execute imports if no validation errors
      ///////////////////////////////////////////////////////////////////////

      // Get DB Query object
      $db = JFactory::getDbo();

      ///////////////////////////////////////////////////////////////////////
      // Offering Details
      ///////////////////////////////////////////////////////////////////////

      // Import Offering Details
      $offering_details = $offeringDetailsSheet->toArray();
      $offering_details = array_slice($offering_details, 1); // Skip header row
      $offering_details = array_unique($offering_details, SORT_REGULAR);
      $offering_details_values = [];
      $offering_details_months = []; // Track unique months
      $encryption = new MemberPortalEncryption();

      foreach ($offering_details as $offering_detail) {
        $date = $this->parseExcelDate($offering_detail[0]);
        $month = date("Y-m-01", strtotime($date));
        if (!in_array($month, $offering_details_months)) {
          $offering_details_months[] = $month;
        }

        $member_code = $offering_detail[1];
        $offering_type = $offering_detail[2];
        $offering_amount = $offering_detail[3];
        $offering_amount_encrypted = $encryption->encryptText($date . "|" . $member_code . "|" . $offering_amount);
        $remarks = $offering_detail[4];

        $offering_details_values[] = $db->quote($date) . ', ' . $db->quote($member_code) . ', ' . $db->quote($offering_type) . ', ' . $db->quote($offering_amount_encrypted) . ', ' . $db->quote($remarks) . ', ' . $db->quote($upload_id);
      }

      // Get months covered by uploaded data (Assume months are continuous)
      sort($offering_details_months);
      $from_date = $offering_details_months[0];
      $to_date = date('Y-m-t', strtotime(end($offering_details_months)));

      if (!$dryRun) {
        // Delete months covered by uploaded data
        $query = $db->getQuery(true);
        $query
            ->delete($db->quoteName('#__memberportal_offering_details'))
            ->where($db->quoteName('date') . ' between ' . $db->quote($from_date) . ' and '. $db->quote($to_date));
        $db->setQuery($query);
        $db->execute();
        
        // Insert data
        $query = $db->getQuery(true);
        $columns = array('date', 'member_code', 'offering_type', 'offering_amount', 'remarks', 'upload_id');
        $query
            ->insert($db->quoteName('#__memberportal_offering_details'))
            ->columns($db->quoteName($columns))
            ->values($offering_details_values);
        $db->setQuery($query);
        $db->execute();
      }

      print_r("<p>已載入 " . count($offering_details) . " 筆奉獻記錄，從 " . date('Y-m-d', strtotime($from_date)) . " 至 " . date('Y-m-d', strtotime($to_date)) . ($dryRun ? " (測試模式)" : ""));

    } catch (Exception $e) {
      print_r($e);
    }
  }
}
