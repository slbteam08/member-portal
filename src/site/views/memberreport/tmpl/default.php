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
</style>
<div class="container-fluid user-content">

  <?php if (!is_null($this->impersonate_member_code)) { ?>
  <div class="row">
    <div class="col-12 admin_mode_banner">
      管理員扮演會友模式 - 崇拜編碼：<?php echo $this->impersonate_member_code; ?>
    </div>
  </div>
  <?php } ?>

  <h3>過去12個月出席及奉獻統計</h3>

  <!-- detail info -->
  <div class="row user-detail">
    <div class="col-sm-6 col-8 user">
      <span>姓名 : <?php echo $this->info->name_chi; ?></span><br>
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
            <td><?php echo $this->attd_cell_cnt; ?> / <?php echo $this->numWeeks; ?></td>
            <td><?php echo $this->attd_cell_pcnt; ?>%</td>
          </tr>
          <tr>
            <td>崇拜出席</td>
            <td><?php echo $this->attd_ceremony_cnt; ?> / <?php echo $this->numWeeks; ?></td>
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

  <br>

  <a id="back" href="javascript:backToSummary()">返回會員記錄</a>
</div>