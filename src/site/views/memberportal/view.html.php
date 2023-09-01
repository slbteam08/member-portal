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
			$year = 2022;
		}
		$this->year = $year;

		$model = $this->getModel();
		
		$this->latest_month = $model->getLatestDataMonth();
		$this->latest_date = $model->getLatestUploadDate();
		$this->num_weeks = $model->getNumWeeks($year);
		$this->cell_schedule = $model->getCellSchedule($year);
		$this->info = $model->getMemberInfo($member_code);
		$this->attd_ceremony_dates = $model->getAttendanceCeremony($member_code, $year);
		$this->attd_cell_dates = $model->getAttendanceCell($member_code, $year);
		$this->offering_months = $model->getOfferingMonths($member_code, $year);
		if ($year == date("Y")) {
			$this->current_week = date("W");
			$this->current_month = date("n");
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

		// Serving posts data (Name => JPG prefix)
		$this->post_mapping = [
			"招待員" => "usher",
			"詩班" => "singer",
			"敬拜隊" => "band",
			"音控" => "sound",
			"字幕員" => "ppt",
			"攝影員" => "camera",
			"區長" => "zone",
			"組長" => "leader",
			"核心" => "core",
			"KidsGame<br>義工" => "kidsgame",
			"迦勒牧區<br>義工" => "elderly",
			"兒牧導師<br>(小學級)" => "child",
			"兒牧導師<br>(幼稚級)" => "kinder",
			"兒牧行政<br>(小學級)" => "primary",
			"少牧導師" => "youth",
			"司庫<br>(數奉獻)" => "treasurer",
			"執事會成員" => "deacon",
			"常委會成員" => "committee",
		];
		$this->excel_post_mapping = [
			// "主任牧師" => "",
			// "實習區牧" => "",
			"組長" => "組長",
			// "祈禱服事隊" => "",
			// "執事" => "",
			"常委會" => "常委會成員",
			"核心" => "核心",
			"區長" => "區長",
			"執事會" => "執事會成員",
			"少牧敬拜隊導師" => "少牧導師",
			"敬拜隊" => "敬拜隊",
			"幼牧" => "兒牧導師<br>(幼稚級)",
			"迦勒組長" => "組長",
			"兒牧導師" => "兒牧導師<br>(小學級)",
			"兒牧級主任" => "兒牧導師<br>(小學級)",
			"司庫" => "司庫<br>(數奉獻)",
			"少牧導師" => "少牧導師",
			"實習區長" => "區長",
			"詩班" => "詩班",
			"招待" => "招待員",
			"天韻詩班" => "詩班",
			"兒牧行政" => "兒牧行政<br>(小學級)",
			"歌詠詩班" => "詩班",
			// "兒牧-彩虹王國" => "",
			// "兒牧Helper" => "",
			// "少牧Helper" => "",
		];
		$this->post_data = [];
		foreach($this->post_mapping as $post => $prefix) {
			$this->post_data[$post] = $prefix . ".jpg";
		}
		foreach($this->serving_posts as $post_obj) {
			$excel_post = $post_obj->post;
			if (array_key_exists($excel_post, $this->excel_post_mapping)) {
				$post = $this->excel_post_mapping[$excel_post];
				$this->post_data[$post] = $this->post_mapping[$post] . "_on.jpg";
			}
		}

		// Courses
		$this->course_structure = [
			[
				"category" => "慕道及初信系列",
				"css_classes" => "course-0",
				"courses" => [
					"啟發課程",
					"基督教價值覶",
					"靈修生活-簡易讀經法",
					"敬拜生活",
					"禱告服事與傳福音訓練"
				],
			],
			[
				"category" => "小組栽培系列",
				"css_classes" => "course-1",
				"courses" => [
					"靈程指引",
					"茁苗",
					"一針見血的福音",
				],
			],
			[
				"category" => "栽培受浸系列",
				"css_classes" => "course-2",
				"courses" => [
					"經歷神營會",
					"認識其他宗教",
					"豐盛的生命",
					"浸禮班",
				],
			],
			[
				"category" => "基本組員成長系列 - 1",
				"css_classes" => "course-3",
				"courses" => [
					"靈界的探索",
					"豐盛的恩光",
					"以弗所書",
					"生命成長營",
				],
			],
			[
				"category" => "基本組員成長系列 - 2",
				"css_classes" => "course-4",
				"courses" => [
					"基督生平1-6冊",
					"摩西五經1-2冊",
					"保羅生平1-3冊",
					"百萬領袖1-6冊",
					"靈命塑造營",
				],
			],
			[
				"category" => "人生歷程系列",
				"css_classes" => "course-5",
				"courses" => [
					"生死教育",
					"啟發家長-兒童",
					"啟發家長-少年",
					"從雅歌看婚姻與愛情",
					"好爸爸學堂",
					"輕輕鬆鬆談管教",
				],
			],
			[
				"category" => "單卷聖經系列",
				"css_classes" => "course-6",
				"courses" => [
					"雅各書",
					"士師記",
					"彼得前後書",
					"路得記",
					"約翰一二三書",
					"傳道書",
					"箴言-智在必得",
					"希伯來書",
					"聖經中的男人",
					"聖經中的女人",
					"啟示錄",
				],
			],
			[
				"category" => "事奉系列",
				"css_classes" => "course-7 white-font",
				"courses" => [
					"核心組員訓練班",
					"組長訓練班",
				],
			],
		];
		$this->course_status = [
			// 慕道及初信系列
			"啟發課程" => "undone",
			"基督教價值覶" => "undone",
			"靈修生活-簡易讀經法" => "undone",
			"敬拜生活" => "undone",
			"禱告服事與傳福音訓練" => "undone",

			// 小組栽培系列
			"靈程指引" => "undone",
			"茁苗" => "undone",
			"一針見血的福音" => "undone",

			// 栽培受浸系列
			"經歷神營會" => "undone",
			"認識其他宗教" => "undone",
			"豐盛的生命" => "undone",
			"浸禮班" => "undone",

			// 基本組員成長系列 - 1
			"靈界的探索" => "undone",
			"豐盛的恩光" => "undone",
			"以弗所書" => "undone",
			"生命成長營" => "undone",

			// 基本組員成長系列 - 2
			"基督生平1-6冊" => "undone",
			"摩西五經1-2冊" => "undone",
			"保羅生平1-3冊" => "undone",
			"百萬領袖1-6冊" => "undone",
			"靈命塑造營" => "undone",

			// 人生歷程系列
			"生死教育" => "undone",
			"啟發家長-兒童" => "undone",
			"啟發家長-少年" => "undone",
			"從雅歌看婚姻與愛情" => "undone",
			"好爸爸學堂" => "undone",
			"輕輕鬆鬆談管教" => "undone",

			// 單卷聖經系列
			"雅各書" => "undone",
			"士師記" => "undone",
			"彼得前後書" => "undone",
			"路得記" => "undone",
			"約翰一二三書" => "undone",
			"傳道書" => "undone",
			"箴言-智在必得" => "undone",
			"希伯來書" => "undone",
			"聖經中的男人" => "undone",
			"聖經中的女人" => "undone",
			"啟示錄" => "undone",

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
