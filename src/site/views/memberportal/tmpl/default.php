<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<style>
  p {
    margin: 0 0 10px !important;
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

  .user-info {
    padding: 20px;
  }

  .row div:first-child {
    /*  padding: 10px;  */
  }


  .user {
    font-size: 14px;
    color: #707070;
  }

  .user span {
    font-size: 18px;
    color: #707070;
  }

  .update-info {
    font-size: 12px;
    text-align: right;
    color: #aaaaaa;
  }

  .info-icon,
  .info-radialBar,
  .info-heatMap,
  .info-post,
  .info-course {
    background-color: #fff;
    margin: 1px;
    text-align: center;
    box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.05);
    border-radius: 10px;
    padding: 10px;
  }

  .info-post p {
    font-size: 12px;
    color: #707070;
    padding: 5px 0px;
    margin-bottom: 0px;
  }

  .info-box {
    padding: 10px !important;
  }


  .course {
    text-align: left;
  }

  .course-name {
    background-color: #C1C1C1;
    display: inline-block;
    margin: 1px;
    padding: 5px 25px 5px 5px !important;
    margin-bottom: 5px;
  }

  .course-0 {
    background-color: #FFFF74;
    width: 200px;
    margin-left: 13px;
  }

  .course-1 {
    background-color: #FFEF74;
    width: 200px;
    margin-left: 13px;
  }

  .course-2 {
    background-color: #EDE154;
    width: 200px;
    margin-left: 13px;
  }

  .course-3 {
    background-color: #F0CC55;
    width: 200px;
    margin-left: 13px;
  }

  .course-4 {
    background-color: #FFB031;
    width: 200px;
    margin-left: 13px;
  }

  .course-5 {
    background-color: #FD9200;
    width: 200px;
    margin-left: 13px;
  }

  .course-6 {
    background-color: #FF7F00;
    width: 200px;
    margin-left: 13px;
  }

  .course-7 {
    background-color: #FF6E03;
    width: 200px;
    margin-left: 13px;
  }

  .white-font {
    color: #ffffff;
  }

  .course-group {
    vertical-align: top;
    padding: 0px !important;
    margin-left: 5px;
  }

  .course-done {
    color: #255700;
    background: url("<?php echo $this->images_path; ?>/done.png") top right no-repeat #9DCB20;
    background-size: 20px;
  }

  .course-undone {
    color: #767676;
    background: url("<?php echo $this->images_path; ?>/undone.png") top right no-repeat #C1C1C1;
    background-size: 20px;
  }



  .info-icon img {
    width: 60%;
  }

  .info-text-num {
    font-size: 28px;
    line-height: 1.2;
  }

  .info-text {
    font-size: 18px;
    line-height: 1;
  }

  .apexcharts-legend {
    text-align: left;
  }

  #heatmap-week .apexcharts-legend-series[rel="1"] {
    display: none !important;
  }

  #chart2 .apexcharts-canvas {
    overflow-x: scroll;
    overflow-y: hidden;
    width: 100% !important;
    position: relative;
    left: -5px;
  }

  #chart3 .apexcharts-canvas {
    overflow-x: scroll;
    overflow-y: hidden;
    width: 100% !important;
    position: relative;
    left: -5px;
  }

  /* Smartphones (portrait and landscape) ----------- */
  @media only screen and (min-device-width : 320px) and (max-device-width : 480px) {

    /* Styles */
    .user-content {
      width: 100%;
      margin: 0 auto;
      padding-top: 20px;
    }

    .info-box {
      padding: 5px !important;
    }


    .course-group {
      width: 85%;
      margin-left: 20px;
    }

    .course-name {
      display: block;
    }

    .course-0,
    .course-1,
    .course-2,
    .course-3,
    .course-4,
    .course-5,
    .course-6,
    .course-7 {
      width: 90%;
    }

    #chart2 .apexcharts-canvas {
      left: 0px;
    }

  }



  /* iPads (portrait and landscape) ----------- */
  @media only screen and (min-device-width : 768px) and (max-device-width : 1024px) {

    /* Styles */
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

<script>
  function generateData(count, yrange) {
    var i = 0;
    var series = [];
    while (i < count) {
      var x = (i + 1).toString() + "月";
      var y = Math.floor(Math.random() * (yrange.max - yrange.min + 1)) + yrange.min;

      series.push({
        x: x,
        y: y
      });
      i++;
    }
    return series;
  }

  function getSeriesCeremony() {
    var series = [
      <?php
      foreach ($this->attd_ceremony_series as $key => $value) {
        echo "{x: '" . $key . "', y: " . $value . "},";
      }
      ?>
    ];
    return series;
  }

  function getSeriesCell() {
    var series = [
      <?php
      foreach ($this->attd_cell_series as $key => $value) {
        echo "{x: '" . $key . "', y: " . $value . "},";
      }
      ?>
    ];
    return series;
  }

  function getSeriesOffering() {
    var series = [
      <?php
      foreach ($this->offering_series as $key => $value) {
        echo "{x: '" . $key . "', y: " . $value . "},";
      }
      ?>
    ];
    return series;
  }
</script>

<div class="container-fluid user-content">

  <!-- header -->
  <div class="row user-info">
    <div class="col-sm-1 col-4"><img src="<?php echo $this->images_path; ?>/avatar.png" /> </div>
    <div class="col-sm-6 col-8 user">
      <span>歡迎 : <?php echo $this->info->name_chi; ?> </span>
      <br> 組員身份 : <?php echo $this->info->cell_role; ?>
      <br>會友分類 : <?php echo $this->info->member_category; ?>
    </div>
    <div class="col-sm-5 col-12 update-info">以下資料最後更新日期為 : <?php echo $this->latest_date; ?></div>
  </div>

  <!-- top 4 info -->
  <div class="row">
    <div class="col-6 col-sm-3 info-box">
      <div class="col-12 info-icon">
        <img src="<?php echo $this->images_path; ?>/icon_cell.jpg" /><br>
        <div class="info-text-num"><?php echo $this->attd_cell_cnt; ?></div>
        <div class="info-text">小組出席</div>
      </div>
    </div>

    <div class="col-6 col-sm-3 info-box">
      <div class="col-12 info-icon">
        <img src="<?php echo $this->images_path; ?>/icon_sunday.jpg" /><br>
        <div class="info-text-num"><?php echo $this->attd_ceremony_cnt; ?></div>
        <div class="info-text">祟拜出席</div>
      </div>
    </div>
    <div class="col-6 col-sm-3 info-box">
      <div class="col-12 info-icon">
        <img src="<?php echo $this->images_path; ?>/icon_offer.jpg" /><br>
        <div class="info-text-num"><?php echo $this->offering_cnt; ?></div>
        <div class="info-text">奉獻</div>
      </div>
    </div>
    <div class="col-6 col-sm-3 info-box">
      <div class="col-12 info-icon">
        <img src="<?php echo $this->images_path; ?>/icon_bible.jpg" /><br>
        <div class="info-text-num"><?php echo $this->completed_course_cnt; ?></div>
        <div class="info-text">課程</div>
      </div>
    </div>
  </div>

  <!-- attedence -->
  <div class="row">

    <div class="col-12 info-box">
      <div class="col-12 info-heatMap">
        <div id="heatmap-week">
          <div id="chart2">
            <apexchart type="heatmap" height="150" width="100%" :options="chartOptions" :series="series"></apexchart>
          </div>
        </div>

        <div id="heatmap-month">
          <div id="chart3">
            <apexchart type="heatmap" height="100" width="100%" :options="chartOptions" :series="series"></apexchart>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- position -->
  <div class="row">

    <div class="col-12 col-sm-7 info-box">
      <div class="col-12 row info-post">
        <?php
          foreach($this->post_data as $post => $jpg) {
        ?>
          <div class="col-4 col-sm-2">
            <img src="<?php echo $this->images_path . "/" . $jpg; ?>" />
            <p><?php echo $post; ?></p>
          </div>
        <?php
          }
        ?>
      </div>
    </div>


    <!-- donut chart -->

    <div class="col-12 col-sm-5 info-box">
      <div class="col-12 info-radialBar">
        <div id="app">
          <div id="chart">
            <apexchart type="radialBar" height="350" :options="chartOptions" :series="series"></apexchart>
          </div>
        </div>
      </div>
    </div>
  </div>


  <!-- course -->
  <div class="row">
    <div class="col-12 info-box">
      <div class="col-12 info-course">
      <?php
        foreach($this->course_structure as $category) {
      ?>
        <div class="course row">
          <div class="course-name col-3 <?php echo $category["css_classes"]; ?>"><?php echo $category["category"]; ?></div>
          <div class="course-group col-9 col-md-8">
          <?php
            foreach($category["courses"] as $course) {
          ?>
            <div class="course-name course-<?php echo $this->course_status[$course]; ?>"><?php echo $course; ?></div>
          <?php
            }
          ?>
          </div>
        </div>
      <?php
        }
      ?>
      </div>
    </div>
  </div>

</div>

<script>
  new Vue({
    el: '#app',
    components: {
      apexchart: VueApexCharts,
    },
    data: {
      series: [
        <?php echo $this->attd_ceremony_pcnt; ?>, 
        <?php echo $this->attd_cell_pcnt; ?>, 
        <?php echo $this->offering_pcnt; ?>,
      ],
      chartOptions: {
        chart: {
          height: 350,
          type: 'radialBar',
        },
        plotOptions: {
          radialBar: {
            dataLabels: {
              name: {
                fontSize: '20px',
              },
              value: {
                fontSize: '16px',
              },
              total: {
                show: false,
                label: 'Total',
                formatter: function(w) {
                  // By default this function returns the average of all series. The below is just an example to show the use of custom formatter function
                  return 249
                }
              }
            }
          }
        },
        labels: ['祟拜出席', '小組出席', '奉獻'],
        colors: ['#32A09C', '#2465BF', '#9DA818'],
        legend: {
          show: true,
          showForSingleSeries: false,
          showForNullSeries: true,
          showForZeroSeries: true,
          position: 'right',
          horizontalAlign: 'left',
          floating: false,
          fontSize: '14px',
          fontFamily: 'Helvetica, Arial',
          fontWeight: 400,
          formatter: undefined,
          inverseOrder: false,
          width: undefined,
          height: undefined,
          tooltipHoverFormatter: undefined,
          customLegendItems: [],
          offsetX: 0,
          offsetY: 0,
          labels: {
            colors: undefined,
            useSeriesColors: false
          },
          markers: {
            width: 15,
            height: 15,
            strokeWidth: 0,
            strokeColor: '#000',
            fillColors: undefined,
            radius: 0,
            customHTML: undefined,
            onClick: undefined,
            offsetX: -2,
            offsetY: 2
          },
          itemMargin: {
            horizontal: 5,
            vertical: 0
          },
          onItemClick: {
            toggleDataSeries: true
          },
          onItemHover: {
            highlightDataSeries: true
          },
        },
      },


    },

  })

  // heatmap (weekly)
  new Vue({
    el: '#heatmap-week',
    components: {
      apexchart: VueApexCharts,
    },

    data: {

      series: [
        {
          name: '崇拜',
          data: getSeriesCeremony(),
        },
        {
          name: '小組',
          data: getSeriesCell(),
        },
      ],

      chartOptions: {
        legend: {
          position: 'bottom',
        },
        responsive: [{
          breakpoint: 600,
          options: {
            chart: {
              width: 1000,
            },
            legend: {
              position: "bottom",
              horizontalAlign: "left"
            }
          }
        }],
        chart: {
          height: 100,
          type: 'heatmap',
          toolbar: {
            show: false,
          },
        },
        tooltip: {
          enabled: false
        },

        plotOptions: {
          heatmap: {
            shadeIntensity: 0,
            radius: 0,
            useFillColorAsStroke: false,

            colorScale: {
              ranges: [
                {
                  from: 0,
                  to: 5,
                  name: ' ',
                  color: '#e1eeff'
                },
                {
                  from: 6,
                  to: 6,
                  name: '祟拜出席',
                  color: '#2465BF'
                },
                // {
                //   from: 8,
                //   to: 9,
                //   name: '分區出席',
                //   color: '#C76CD8'
                // },
                // {
                //   from: 9,
                //   to: 9,
                //   name: '分區缺席',
                //   color: '#e5d9e7'
                // },
                {
                  from: 10,
                  to: 10,
                  name: '小組出席',
                  color: '#32A09C'
                },
                {
                  from: 11,
                  to: 11,
                  name: '小組缺席',
                  color: '#c2dcdb'
                },
                {
                  from: 12,
                  to: 12,
                  name: '沒有小組',
                  color: '#4e4e4e'
                }
              ]
            }
          }
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          width: 1
        },
        title: {
          text: ' '
        },
      },


    },

  })

  // heatmap (monthly)
  new Vue({
    el: '#heatmap-month',
    components: {
      apexchart: VueApexCharts,
    },

    data: {

      series: [
        {
          name: '奉獻',
          data: getSeriesOffering(),
          // data: generateData(12, {
          //   min: 3,
          //   max: 4,
          // })
        },
      ],

      chartOptions: {
        legend: {
          position: 'bottom',
        },
        responsive: [{
          breakpoint: 600,
          options: {
            chart: {
              width: 1000,
            },
            legend: {
              position: "bottom",
              horizontalAlign: "left"
            }
          }
        }],
        chart: {
          height: 50,
          type: 'heatmap',
          toolbar: {
            show: false,
          },
        },
        tooltip: {
          enabled: false
        },

        plotOptions: {
          heatmap: {
            shadeIntensity: 0,
            radius: 0,
            useFillColorAsStroke: false,

            colorScale: {
              ranges: [
                {
                  from: 0,
                  to: 3,
                  name: ' ',
                  color: '#e3e7d3'
                },
                {
                  from: 4,
                  to: 4,
                  name: '奉獻',
                  color: '#9DA818'
                }
              ]
            }
          }
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          width: 1
        },
        title: {
          text: ' '
        },
      },


    },

  })
</script>