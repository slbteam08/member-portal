<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<style>
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

#app2 .apexcharts-legend-series[rel="1"],
#app2 .apexcharts-legend-series[rel="3"],
#app2 .apexcharts-legend-series[rel="5"] {
    display: none !important;
}

#chart2 .apexcharts-canvas{
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


    .course-group{    width: 85%; margin-left: 20px;}
    .course-name{display: block;}
    .course-0,.course-1,.course-2,.course-3,.course-4,.course-5,.course-6,.course-7{ width:90%;}

    #chart2 .apexcharts-canvas{left: 0px;}

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
    var x = (i + 1).toString();
    var y = Math.floor(Math.random() * (yrange.max - yrange.min + 1)) + yrange.min;

    series.push({
        x: x,
        y: y
    });
    i++;
    }
    return series;
}
</script>

<div class="container-fluid user-content">

    <!-- header -->
    <div class="row user-info">
      <div class="col-sm-1 col-4"><img src="<?php echo $this->images_path; ?>/avatar.png" /> </div>
      <div class="col-sm-6 col-8 user"><span>歡迎 : 馬錦鋒 </span><br> 組員身份 : 區長 <br>會友分類 : 責任會友</div>
      <div class="col-sm-5 col-12 update-info">以下資料最後更新日期為 : 2022年 1 月10 日</div>
    </div>

    <!-- top 4 info -->
    <div class="row">
      <div class="col-6 col-sm-3 info-box">
        <div class="col-12 info-icon">
          <img src="<?php echo $this->images_path; ?>/icon_cell.jpg" /><br>
          <div class="info-text-num">48</div>
          <div class="info-text">小組出席</div>
        </div>
      </div>

      <div class="col-6 col-sm-3 info-box">
        <div class="col-12 info-icon">
          <img src="<?php echo $this->images_path; ?>/icon_sunday.jpg" /><br>
          <div class="info-text-num">47</div>
          <div class="info-text">祟拜出席</div>
        </div>
      </div>
      <div class="col-6 col-sm-3 info-box">
        <div class="col-12 info-icon">
          <img src="<?php echo $this->images_path; ?>/icon_offer.jpg" /><br>
          <div class="info-text-num">12</div>
          <div class="info-text">奉獻</div>
        </div>
      </div>
      <div class="col-6 col-sm-3 info-box">
        <div class="col-12 info-icon">
          <img src="<?php echo $this->images_path; ?>/icon_bible.jpg" /><br>
          <div class="info-text-num">2</div>
          <div class="info-text">課程</div>
        </div>
      </div>
    </div>

    <!-- attedence -->
    <div class="row">

      <div class="col-12 info-box">
        <div class="col-12 info-heatMap">
          <div id="app2">
            <div id="chart2">
              <apexchart type="heatmap" height="200" width="100%" :options="chartOptions" :series="series"></apexchart>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- position -->
    <div class="row">

      <div class="col-12 col-sm-7 info-box">
        <div class="col-12 row info-post">
          <div class="col-4 col-sm-2">
            <img src="<?php echo $this->images_path; ?>/usher.jpg" />
            <p>招待員</p>
          </div>
          <div class="col-4 col-sm-2">
            <img src="<?php echo $this->images_path; ?>/singer.jpg" /> <br>
            <p>詩班</p>
          </div>
          <div class="col-4 col-sm-2">
            <img src="<?php echo $this->images_path; ?>/band_on.jpg" /> <br>
            <p>敬拜隊</p>
          </div>
          <div class="col-4 col-sm-2">
            <img src="<?php echo $this->images_path; ?>/sound_on.jpg" /> <br>
            <p>音控</p>
          </div>
          <div class="col-4 col-sm-2">
            <img src="<?php echo $this->images_path; ?>/ppt.jpg" />
            <p>字幕員</p>
          </div>
          <div class="col-4 col-sm-2">
            <img src="<?php echo $this->images_path; ?>/camera.jpg" /> <br>
            <p>攝影員</p>
          </div>

          <div class="col-4 col-sm-2">
            <img src="<?php echo $this->images_path; ?>/zone_on.jpg" /> <br>
            <p>區長</p>
          </div>
          <div class="col-4 col-sm-2">
            <img src="<?php echo $this->images_path; ?>/leader.jpg" /> <br>
            <p>組長</p>
          </div>
          <div class="col-4 col-sm-2">
            <img src="<?php echo $this->images_path; ?>/core.jpg" />
            <p>核心</p>
          </div>
          <div class="col-4 col-sm-2">
            <img src="<?php echo $this->images_path; ?>/kidsgame.jpg" /> <br>
            <p>KidsGame<br>義工</p>
          </div>
          <div class="col-4 col-sm-2">
            <img src="<?php echo $this->images_path; ?>/elderly.jpg" /> <br>
            <p>迦勒牧區<br>義工</p>
          </div>
          <div class="col-4 col-sm-2">
            <img src="<?php echo $this->images_path; ?>/child.jpg" /> <br>
            <p>兒牧導師<br>(小學級)</p>
          </div>


          <div class="col-4 col-sm-2">
            <img src="<?php echo $this->images_path; ?>/kinder.jpg" /> <br>
            <p>兒牧導師<br>(幼稚級)</p>
          </div>
          <div class="col-4 col-sm-2">
            <img src="<?php echo $this->images_path; ?>/primary.jpg" /> <br>
            <p>兒牧行政<br>(小學級)</p>
          </div>
          <div class="col-4 col-sm-2">
            <img src="<?php echo $this->images_path; ?>/youth.jpg" />
            <p>少牧導師</p>
          </div>
          <div class="col-4 col-sm-2">
            <img src="<?php echo $this->images_path; ?>/treasurer.jpg" /> <br>
            <p>司庫<br>(數奉獻)</p>
          </div>
          <div class="col-4 col-sm-2">
            <img src="<?php echo $this->images_path; ?>/deacon.jpg" /> <br>
            <p>執事會成員</p>
          </div>
          <div class="col-4 col-sm-2">
            <img src="<?php echo $this->images_path; ?>/committee.jpg" /> <br>
            <p>常委會成員</p>
          </div>


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
          <div class="course row">
            <div class="course-name course-0 col-3">慕道及初信系列</div>
            <div class="course-group col-9 col-md-8">
            <div class="course-name course-done">啟發課程</div>
            <div class="course-name course-undone">基督教價值覶</div>
            <div class="course-name course-undone">靈修生活-簡易讀經法</div>
            <div class="course-name course-undone">敬拜生活</div>
            <div class="course-name course-undone">禱告服事與傳福音訓練</div>
          </div>
          </div>

          <div class="course row">
            <div class="course-name course-1 col-3">小組栽培系列</div>
            <div class="course-group col-9 col-md-8">
            <div class="course-name course-done">靈程指引</div>
            <div class="course-name course-done">茁苗</div>
            <div class="course-name course-done">一針見血的福音</div>
          </div>
          </div>

          <div class="course row">
            <div class="course-name course-2 col-3">栽培受浸系列</div>
            <div class="course-group col-9 col-md-8">
            <div class="course-name course-done">經歷神營會</div>
            <div class="course-name course-done">認識其他宗教</div>
            <div class="course-name course-done">豐盛的生命</div>
            <div class="course-name course-done">浸禮班</div>
          </div>
          </div>

          <div class="course row">
            <div class="course-name course-3 col-3">基本組員成長系列 - 1</div>
            <div class="course-group col-9 col-md-8">
            <div class="course-name course-done">靈界的探索</div>
            <div class="course-name course-done">豐盛的恩光</div>
            <div class="course-name course-done">以弗所書</div>
            <div class="course-name course-done">生命成長營</div>
          </div>
          </div>

          <div class="course row">
            <div class="course-name course-4 col-3">基本組員成長系列 - 2</div>
            <div class="course-group col-9 col-md-8">
            <div class="course-name course-done">基督生平1-6冊</div>
            <div class="course-name course-undone">摩西五經1-2冊</div>
            <div class="course-name course-done">保羅生平1-3冊</div>
            <div class="course-name course-undone">百萬領袖1-6冊</div>
            <div class="course-name course-done">靈命塑造營</div>
            </div>
          </div>

          <div class="course row">
            <div class="course-name course-5 col-3">人生歷程系列</div>
            <div class="course-group col-9 col-md-8">
              <div class="course-name course-undone">生死教育</div>
              <div class="course-name course-undone">啟發家長-兒童</div>
              <div class="course-name course-undone">啟發家長-少年</div>
              <div class="course-name course-done">從雅歌看婚姻與愛情</div>
              <div class="course-name course-undone">好爸爸學堂</div>
              <div class="course-name course-undone">輕輕鬆鬆談管教</div>
            </div>
          </div>

          <div class="course row">
            <div class="course-name course-6 col-3">單卷聖經系列</div>
            <div class="course-group col-9 col-md-8">
              <div class="course-name course-undone">雅各書</div>
              <div class="course-name course-undone">士師記</div>
              <div class="course-name course-done">彼得前後書</div>
              <div class="course-name course-undone">路得記</div>
              <div class="course-name course-undone">約翰一二三書</div>
              <div class="course-name course-undone">傳道書</div>
              <div class="course-name course-undone">箴言-智在必得</div>
              <div class="course-name course-undone">希伯來書</div>
              <div class="course-name course-done">聖經中的男人</div>
              <div class="course-name course-undone">聖經中的女人</div>
              <div class="course-name course-done">啟示錄</div>
            </div>
          </div>

          <div class="course row">
            <div class="course-name course-7 col-3" style="color:#ffffff;">事奉系列</div>
            <div class="course-group col-9 col-md-8">
            <div class="course-name course-done">核心組員訓練班</div>
            <div class="course-name course-done">組長訓練班</div>
          </div>
          </div>
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

        series: [92, 91, 100],
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


    // heatmap

    new Vue({
        el: '#app2',
        components: {
        apexchart: VueApexCharts,
        },

        data: {

        series: [{
            name: ' ',
            data: generateData(52, {
                min: 8,
                max: 12
            })
            },
            {
            name: ' ',
            data: generateData(52, {
                min: 5,
                max: 6
            })
            },
            {
            name: ' ',
            data: generateData(52, {
                min: 3,
                max: 4
            })
            },
            {
            name: ' ',
            data: generateData(52, {
                min: 1,
                max: 2
            })
            }
        ],

        chartOptions: {
            legend: {
            position: 'bottom',
            },
            responsive: [
            {
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
            }
            ],
            chart: {
            height: 200,
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
                ranges: [{
                    from: 0,
                    to: 1,
                    name: ' ',
                    color: '#ddd3e7'
                    },
                    {
                    from: 2,
                    to: 2,
                    name: '完成課程',
                    color: '#792D7C'
                    },
                    {
                    from: 3,
                    to: 3,
                    name: ' ',
                    color: '#e3e7d3'
                    },
                    {
                    from: 4,
                    to: 4,
                    name: '奉獻',
                    color: '#9DA818'
                    },
                    {
                    from: 5,
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
                    {
                    from: 8,
                    to: 8,
                    name: '分區出席',
                    color: '#C76CD8'
                    },
                    {
                    from: 9,
                    to: 9,
                    name: '分區缺席',
                    color: '#e5d9e7'
                    },
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
</script>