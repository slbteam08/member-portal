<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/**
 * HTML View class for the MemberPortal Component
 *
 * @since  0.0.1
 */
class MemberPortalViewMemberPortal extends JViewLegacy
{
	/**
	 * Display the Member Portal view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	function display($tpl = null)
	{
		$input = Factory::getApplication()->input;

		// Determine member code
		$member_code = $input->get("member_code"); // Secret override
		if (is_null($member_code)) {
			$user = Factory::getUser();
			$member_code = $user->username;
			// print_r($user);
		}

		// Get member data
		$year = $input->get("year"); // Secret override
		if (is_null($year)) {
			$year = 2023;
		}
		$this->year = $year;

		$model = $this->getModel();
		
		$this->latest_month_val = $model->getLatestDataMonth();
		if (is_null($this->latest_month_val)) {
			$this->latest_month = "";
			$this->latest_week = date("W");
			$this->latest_month_num = date("n");
		} else {
			$date_obj = \DateTime::createFromFormat("Y-m-d", $this->latest_month_val);
			$this->latest_month = $date_obj->format("Y 年 n 月");
			$this->latest_week = $date_obj->format("W");
			$this->latest_month_num = $date_obj->format("n");
		}
		$this->latest_date = $model->getLatestUploadDate();
		$this->num_weeks = $model->getNumWeeks($year);
		$this->cell_schedule = $model->getCellSchedule($year);
		$this->info = $model->getMemberInfo($member_code);
		$this->attd_ceremony_dates = $model->getAttendanceCeremony($member_code, $year);
		$this->attd_cell_dates = $model->getAttendanceCell($member_code, $year);
		$this->offering_months = $model->getOfferingMonths($member_code, $year);
		if ($year == date("Y")) {
			$this->current_week = $this->latest_week;
			$this->current_month = $this->latest_month_num;
		} else {
			$this->current_week = $this->num_weeks;
			$this->current_month = 12;
		}
		$this->serving_posts = $model->getServingPosts($member_code);
		$this->completed_courses = $model->getCompletedCourses($member_code);

		// Attendance code
		$ceremony_present = 6;
		$ceremony_absent = 5;
		$zone_present = 8;
		$zone_absent = 9; // No use
		$cell_present = 10;
		$cell_absent = 11;
		$no_cell = 12;
		$no_offering = 3;
		$did_offering = 4;

		// Evaluate attendance arrays
		$this->attd_ceremony_series = array_fill(1, $this->num_weeks, $ceremony_absent);
		foreach($this->attd_ceremony_dates as $date) {
			$this->attd_ceremony_series[$date->week_of_year] = $ceremony_present;
		}
		$this->attd_ceremony_cnt = count($this->attd_ceremony_dates);  // TODO: Count distinct weeks

		$this->attd_cell_series = [];
		$this->no_cell_weeks = 0;
		foreach($this->cell_schedule as $cell_date) {
			$week = $cell_date->week;
			if (is_null($cell_date->week_start)) {
				$this->attd_cell_series[$week] = $no_cell;
				if ($week <= $this->current_week) {
					$this->no_cell_weeks += 1;
				}
			} else {
				$this->attd_cell_series[$week] = $cell_absent;
			}
		}
		foreach($this->attd_cell_dates as $date) {
			if ($date->event_type == "小組聚會") {
				$present = $cell_present;
			} else {
				$present = $zone_present;
			}
			$this->attd_cell_series[$date->week_of_year] = $present;
		}
		$this->attd_cell_cnt = count($this->attd_cell_dates);

		// Evaluate offering array
		$this->offering_series = array_fill(1, 12, $no_offering);
		foreach($this->offering_months as $month) {
			$this->offering_series[$month->month] = $did_offering;
		}
		$this->offering_cnt = count($this->offering_months);
		$this->offering_pcnt = (int)round($this->offering_cnt / $this->current_month * 100);

		// Attendance percentage
		$this->attd_ceremony_pcnt = (int)round($this->attd_ceremony_cnt / $this->current_week * 100);
		$this->attd_cell_pcnt = (int)round($this->attd_cell_cnt / ($this->current_week - $this->no_cell_weeks) * 100);

		// Serving posts data (Name => SVG prefix)
		$this->post_mapping = [
			"歌詠詩班" => "choir",
			"天韻詩班" => "elder_choir",
			"敬拜隊" => "worship",
			"敬拜隊<br>(領敬拜)" => "worship_leader",
			"敬拜隊<br>(少年)" => "youth_worship",
			"招待員" => "usher",
			"音控" => "soundman",
			"字幕員" => "ppt",
			"攝錄控制員" => "camman",
			"插花司職員" => "flower",
			"禱告服事隊" => "prayer",
			"司庫<br>(數奉獻)" => "treasurer",
			"區牧" => "pastoral",
			"區長" => "zone_pastor",
			"組長" => "cell_leader",
			"核心" => "coreman",
			"迦勒牧區" => "elderly",
			"幼牧導師" => "child_tutor",
			"兒牧導師" => "kids_tutor",
			"少牧導師" => "youth_tutor",
			"幼牧行政" => "child_admin",
			"兒牧行政" => "kids_admin",
			"少牧行政" => "youth_admin",
			"幼牧助手" => "child_helper",
			"兒牧助手" => "kids_helper",
			"少牧助手" => "youth_helper",
			"執事會成員" => "deacon",
			"常委會成員" => "committee",
		];
		$this->excel_post_mapping = [
			"核心組員" => "核心",
			"組長" => "組長",
			"招待員" => "招待員",
			"崇拜祈禱服事隊" => "禱告服事隊",
			"伴唱" => "敬拜隊",
			"歌詠詩班" => "歌詠詩班",
			"迦勒牧區" => "迦勒牧區",
			"天韻詩班" => "天韻詩班",
			"司庫" => "司庫<br>(數奉獻)",
			"實習區長" => "區長",
			"常委會成員" => "常委會成員",
			"主領" => "敬拜隊<br>(領敬拜)",
			"音控" => "音控",
			"司琴" => "敬拜隊",
			"ppt" => "字幕員",
			"區長" => "區長",
			"結他" => "敬拜隊",
			"鼓手" => "敬拜隊",
			"bass" => "敬拜隊",
			"敬拜隊" => "敬拜隊",
			"執事會成員" => "執事會成員",
			"實習區牧" => "區牧",
			"詩班員" => "歌詠詩班",
		];
		$this->post_data = [];
		foreach($this->post_mapping as $post => $prefix) {
			$this->post_data[$post] = [$prefix . ".svg", 0]; // 0 to switch off
		}
		foreach($this->serving_posts as $post_obj) {
			$excel_post = $post_obj->post;
			if (array_key_exists($excel_post, $this->excel_post_mapping)) {
				$post = $this->excel_post_mapping[$excel_post];
				$this->post_data[$post][1] = 1; // 1 to switch on
			}
		}

		// Courses
		$this->course_structure = [
			[
				"category" => "入組流程",
				"css_classes" => "course-0",
				"courses" => [
					"經歷神營會",
				],
			],
			[
				"category" => "小組栽培",
				"css_classes" => "course-1",
				"courses" => [
					"靈程指引",
					"新生命",
				],
			],
			[
				"category" => "受浸流程",
				"css_classes" => "course-2",
				"courses" => [
					"認識其他宗教",
					"豐盛的生命",
					"浸禮班",
				],
			],
			[
				"category" => "信徒裝備",
				"css_classes" => "course-3",
				"courses" => [
					"靈界的探索",
					"豐盛的恩光",
					"以弗所書",
					"基督生平 1",
					"基督生平 2",
					"基督生平 3",
					"基督生平 4",
					"基督生平 5",
					"基督生平 6",
				],
			],
			// [
			// 	"category" => "基本組員成長系列 - 2",
			// 	"css_classes" => "course-4",
			// 	"courses" => [
			// 		"基督生平1-6冊",
			// 		"摩西五經1-2冊",
			// 		"保羅生平1-3冊",
			// 		"百萬領袖1-6冊",
			// 		"靈命塑造營",
			// 	],
			// ],
			// [
			// 	"category" => "人生歷程系列",
			// 	"css_classes" => "course-5",
			// 	"courses" => [
			// 		"生死教育",
			// 		"啟發家長-兒童",
			// 		"啟發家長-少年",
			// 		"從雅歌看婚姻與愛情",
			// 		"好爸爸學堂",
			// 		"輕輕鬆鬆談管教",
			// 	],
			// ],
			// [
			// 	"category" => "單卷聖經系列",
			// 	"css_classes" => "course-6",
			// 	"courses" => [
			// 		"雅各書",
			// 		"士師記",
			// 		"彼得前後書",
			// 		"路得記",
			// 		"約翰一二三書",
			// 		"傳道書",
			// 		"箴言-智在必得",
			// 		"希伯來書",
			// 		"聖經中的男人",
			// 		"聖經中的女人",
			// 		"啟示錄",
			// 	],
			// ],
			// [
			// 	"category" => "事奉系列",
			// 	"css_classes" => "course-7 white-font",
			// 	"courses" => [
			// 		"核心組員訓練班",
			// 		"組長訓練班",
			// 	],
			// ],
		];
		$this->course_status = [
			// 入組流程
			"經歷神營會" => "undone",

			// 小組栽培
			"靈程指引" => "undone",
			"新生命" => "undone",

			// 受浸流程
			"認識其他宗教" => "undone",
			"豐盛的生命" => "undone",
			"浸禮班" => "undone",

			// 信徒裝備
			"靈界的探索" => "undone",
			"豐盛的恩光" => "undone",
			"以弗所書" => "undone",
			"基督生平 1" => "undone",
			"基督生平 2" => "undone",
			"基督生平 3" => "undone",
			"基督生平 4" => "undone",
			"基督生平 5" => "undone",
			"基督生平 6" => "undone",

			// 事奉系列
			"核心組員訓練班" => "undone",
			"組長訓練班" => "undone",
		];
		foreach($this->completed_courses as $course) {
			$this->course_status[$course->course] = "done";
		}
		$this->completed_course_cnt = count($this->completed_courses);

		// Set up media paths
		$component_name = $input->get('option');
		$media_base = Uri::base() . "media/" . $component_name;
		$this->images_path = $media_base . "/images";
		$this->js_path = $media_base . "/js";
		$this->css_path = $media_base . "/css";

		// Add JS and CSS to document
		$document = Factory::getDocument();
		$document->addScript($this->js_path . "/bootstrap.bundle.min.js");
		$document->addScript("https://cdn.jsdelivr.net/npm/vue/dist/vue.min.js");
		$document->addScript("https://cdn.jsdelivr.net/npm/apexcharts");
		$document->addScript("https://cdn.jsdelivr.net/npm/vue-apexcharts");
		$document->addStyleSheet($this->css_path . "/bootstrap.min.css");
		$document->addStyleSheet($this->css_path . "/styles.css");

		// Display the view
		parent::display($tpl);
	}
}
