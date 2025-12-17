<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<script>
function backToSummary() {
  params = new URLSearchParams(window.location.search);
  params.set("view", "member-portal");
  window.location.search = params.toString();
}
</script>
<style>
.admin_mode_banner {
  text-align: center;
  background: #dc3d3d;
  color: white;
  font-weight: bold;
  padding: 4px;
  margin-bottom: 8px;
}

.pastor_mode_banner {
  text-align: center;
  background: #007b52;
  color: white;
  font-weight: bold;
  padding: 4px;
  margin-bottom: 8px;
}

.pastor_mode_banner_gray {
  text-align: center;
  background: #757575;
  color: white;
  font-weight: bold;
  padding: 4px;
  margin-bottom: 8px;
}

.total-column {
  font-weight: bold;
  background-color: #f8f9fa;
}

.total-column-header {
  font-weight: bold;
}

.user-content {
  width: 100%;
  margin: 0 auto;
  padding-top: 20px;
  max-width: 1020px;
}

.user-content img {
  width: 100%;
}

.user {
  font-size: 14px;
  color: #707070;
}

.user span {
  font-size: 18px;
  color: #707070;
}

.user-info {
  padding: 20px;
}

.row div:first-child {
  /*  padding: 10px;  */
}

.update-info {
  font-size: 12px;
  text-align: right;
  color: #aaaaaa;
  margin-bottom: 12px;
}

/* Smartphones (portrait and landscape) ----------- */
@media only screen and (min-device-width : 320px) and (max-device-width : 480px) {
  .user-content {
    width: 100%;
    margin: 0 auto;
    padding-top: 20px;
  }
}

/* iPads (portrait and landscape) ----------- */
@media only screen and (min-device-width : 768px) and (max-device-width : 1024px) {
  .user-content {
    width: 100%;
    margin: 0 auto;
    padding-top: 20px;
  }
}

/* iPads (landscape) ----------- */
@media only screen and (min-device-width : 768px) and (max-device-width : 1024px) and (orientation : landscape) {
  /* Styles */
}

/* iPads (portrait) ----------- */
@media only screen and (min-device-width : 768px) and (max-device-width : 1024px) and (orientation : portrait) {
  /* Styles */
}

</style>
<div class="container-fluid user-content">

  <?php if (!is_null($this->impersonate_member_code)) { ?>
  <div class="row">
    <div class="col-12 admin_mode_banner">
      管理員扮演會友模式 - 崇拜編碼：<?php echo $this->impersonate_member_code; ?>
    </div>
  </div>
  <?php } ?>

  <?php if ($this->pastor_view_mode) { ?>
  <div class="row">
    <div class="col-12 pastor_mode_banner">
      牧者檢視模式
      <br>牧者：<?php echo $this->pastor_info->name_chi; ?> - 顯示組員：<?php echo $this->info->name_chi; ?>
    </div>
  </div>
  <?php } ?>

  <div class="row user-info">
    <div class="col-sm-1 col-4"><img src="<?php echo $this->images_path; ?>/avatar.png" /> </div>
    <div class="col-sm-6 col-8 user">
      <span>歡迎 : <?php echo $this->info->name_chi; ?> </span>
      <br>身份 : <?php echo $this->info->cell_role; ?>
      <br>會友分類 : <?php echo $this->info->member_category; ?>
    </div>
    <div class="col-sm-5 col-12">
      <div class="update-info">
        以下資料最新數據月份 : <?php echo $this->latest_month; ?>
        <br>（每月大約第三個星期更新）
      </div>
    </div>
  </div>

  <h3 style="margin-top: 20px;">過去12個月出席及奉獻統計</h3>

  <!-- detail info -->
  <div class="row user-detail">
    <div class="col-12 user">
      月份 : <?php echo $this->startMonth; ?> 至 <?php echo $this->endMonth; ?>
    </div>
  </div>

  <br>

  <div class="row">
    <div class="col-12">
      <table>
        <thead>
          <tr>
            <th width="30%">項目</th>
            <th width="30%">次數</th>
            <th>比率</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>小組出席</td>
            <td><?php echo $this->attd_cell_cnt; ?> / <?php echo $this->num_cell_weeks; ?></td>
            <td><?php echo $this->attd_cell_pcnt; ?>%</td>
          </tr>
          <tr>
            <td>崇拜出席</td>
            <td><?php echo $this->attd_ceremony_cnt; ?> / <?php echo $this->num_weeks; ?></td>
            <td><?php echo $this->attd_ceremony_pcnt; ?>%</td>
          </tr>
          <tr>
            <td>奉獻</td>
            <td><?php echo $this->offering_cnt; ?> / 12</td>
            <td><?php echo $this->offering_pcnt; ?>%</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <?php if ($this->pastor_view_mode) { ?>
  <br>
  <div class="row">
    <div class="col-12 pastor_mode_banner_gray">
      牧者檢視模式 - 不顯示奉獻明細
    </div>
  </div>
  <?php } elseif (count($this->offering_details_date_rows) > 0) { ?>
  <br>

  <h3 style="margin-top: 20px;">本財政年度奉獻明細</h3>

  <div class="row user-detail">
    <div class="col-12 user">
      奉獻月份 : <?php echo $this->startMonthOfferingDetails; ?> 至 <?php echo $this->endMonthOfferingDetails; ?>
    </div>
  </div>

  <br>

  <div class="row">
    <div class="col-12">
      <table class="offering-table">
        <thead>
          <tr>
            <th>日期</th>
            <?php foreach ($this->offering_types as $offering_type) { ?>
              <th><?php echo $offering_type; ?></th>
            <?php } ?>
            <th class="total-column-header">合計</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($this->offering_details_date_rows as $date => $offering_details_date_rows) { ?>
            <?php foreach ($offering_details_date_rows as $offering_details_date_row) { ?>
              <tr>
                <td><?php echo $date; ?></td>
              <?php foreach ($this->offering_types as $offering_type) { ?>
                <td><?php echo $offering_details_date_row[$offering_type] ?? ""; ?></td>
              <?php } ?>
              <td class="total-column"><?php echo array_sum($offering_details_date_row); ?></td>
              </tr>
            <?php } ?>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php } ?>

  <br>

  <a id="back" href="javascript:backToSummary()">返回會員記錄</a>
</div>