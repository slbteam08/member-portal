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

.total-column {
  font-weight: bold;
  background-color: #f8f9fa;
}

.total-column-header {
  font-weight: bold;
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

  <span>姓名 : <?php echo $this->info->name_chi; ?></span><br>

  <h3 style="margin-top: 20px;">過去12個月出席及奉獻統計</h3>

  <!-- detail info -->
  <div class="row user-detail">
    <div class="col-sm-6 col-8 user">
      月份 : <?php echo $this->startMonth; ?> 至 <?php echo $this->endMonth; ?>
    </div>
  </div>

  <br>

  <div class="row">
    <div class="col-sm-6">
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

  <?php if (count($this->offering_details_date_rows) > 0) { ?>
  <br>

  <h3 style="margin-top: 20px;">過去3個月奉獻明細</h3>

  <div class="row user-detail">
    <div class="col-sm-6 col-8 user">
      奉獻月份 : <?php echo $this->startMonthOfferingDetails; ?> 至 <?php echo $this->endMonthOfferingDetails; ?>
    </div>
  </div>

  <br>

  <div class="row">
    <div class="col-sm-6">
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
          <?php foreach ($this->offering_details_date_rows as $date => $offering_details_date_row) { ?>
            <tr>
              <td><?php echo $date; ?></td>
            <?php foreach ($this->offering_types as $offering_type) { ?>
              <td><?php echo $offering_details_date_row[$offering_type] ?? ""; ?></td>
            <?php } ?>
            <td class="total-column"><?php echo array_sum($offering_details_date_row); ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php } ?>

  <br>

  <a id="back" href="javascript:backToSummary()">返回會員記錄</a>
</div>