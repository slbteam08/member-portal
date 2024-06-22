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

.year-select-container {
  text-align: right;
}

.year-select {
  width: 68px;
}

.selectmenu select {
  margin: 0;
  width: 100%;
  border: none;
  padding: 10px;
  display: flex;
  cursor: pointer;
  overflow: hidden;
  font-weight: normal;
  align-items: center;
  outline-color: #fff;
  height: 43px;
  /* background-color: #ffffff; */
  border: 1px solid #e8ecf3;
  justify-content: space-between;
}

.selectmenu {
  display: inline-block;
  width: fit-content;
}

.selectmenu select>option {
  background-color: #ffffff;
}

#btn_switch {
  padding-left: 5px;
  margin-bottom: 10px;
  border: 1px solid #bbbbbb;
  width: -webkit-fit-content;
  width: -moz-fit-content;
  width: fit-content;
  border-radius: 12px;
  z-index: 1;
}

.toggle_switch {
  display: inline-block;
  cursor: pointer;
  margin-left: -5px;
}

.worship {
  border-radius: 10px 0px 0px 10px;
}

.cell {
  border-radius: 0px 10px 10px 0px;
}

#btn_switch .titleheader {
  display: block;
  text-align: left;
  color: white;
  background-color: rgb(158, 158, 158);
  padding: 5px 10px;
  font-size: 14px;
  width: -webkit-fit-content;
  width: -moz-fit-content;
  width: fit-content;
}

#btn_switch .active .titleheader {
  display: block;
  text-align: left;
  color: white;
  background-color: rgb(0, 106, 255);
  padding: 5px 10px;
  font-size: 14px;
  width: -webkit-fit-content;
  width: -moz-fit-content;
  width: fit-content;
}

#menu {
  margin-top: 8px;
  margin-bottom: 8px;
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
<script>
function backToSummary() {
  params = new URLSearchParams(window.location.search);
  params.set("view", "member-portal");
  window.location.search = params.toString();
}
</script>
<div class="container-fluid user-content">

  <h3>牧養記錄</h3>

  <!-- header -->
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
        <br>（每月大約第二個星期更新）
      </div>
    </div>
  </div>

  <div id="btn_switch">
    <div class="toggle_switch active">
      <div class="titleheader worship" onclick="toggle_switch();">崇拜出席人數分佈</div>
    </div>
    <div class="toggle_switch">
      <div class="titleheader cell" onclick="toggle_switch();">小組出席人數分佈</div>
    </div>
  </div>

  <div class="row">

    <div id="menu">
      <div id="year" class="selectmenu">年份
        <select @change="handleItemChange($event)" name="selectYear" v-model="selectedOption">
          <option v-for="(item , index) in myOptionsArray" v-bind:key="index">
            {{item}}
          </option>
        </select>
      </div>
      <div id="pastoral" class="selectmenu">牧區
        <select @change="handleItemChange($event)" name="selectPastoral" v-model="selectedOption" :disabled="disabled">
          <option v-for="(item , index) in myOptionsArray" v-bind:key="index" :selected="index == 1">
            {{item}}
          </option>
        </select>
      </div>
      <div id="district" class="selectmenu">分區
        <select @change="handleItemChange($event)" name="selectDistrict" v-model="selectedOption" :disabled="disabled">
          <option v-for="(item , index) in myOptionsArray" v-bind:key="index" :selected="index == 1">
            {{item}}
          </option>
        </select>
      </div>
      <div id="cellgroup" class="selectmenu">小組
        <select @change="handleItemChange($event)" name="selectCell" v-model="selectedOption" :disabled="disabled">
          <option v-for="(item , index) in myOptionsArray" v-bind:key="index" :selected="index == 1">
            {{item}}
          </option>
        </select>
      </div>

      <div id="cellmember" class="selectmenu">組員
        <select @change="handleItemChange($event)" name="selectCell" v-model="selectedOption" :disabled="disabled">
          <option v-for="(item , index) in myOptionsArray" v-bind:key="index" :selected="index == 1">
            {{item}}
          </option>
        </select>
      </div>
    </div>


    <div class="col-12 info-box">
      <div class="col-12 info-heatMap">
        <div id="worship_attendance">
          <apexchart type="bar" height="400" :options="chartOptions" :series="series"></apexchart>
        </div>
      </div>
    </div>

  </div>

  <br>

  <a id="back" href="javascript:backToSummary()">返回會員記錄</a>
</div>

<script>

var year_menu = new Vue({
  el: '#year',
  data: {
    myOptionsArray: [2024, 2023, 2022],
    selectedOption: <?php echo $this->year; ?>,
  },
  mounted() {
    //this.myOptionsArray = this.myOptionsArray.sort();
  },
  methods: {
    handleItemChange(event) {
      var year = event.target.value;

      params = new URLSearchParams(window.location.search);
      params.set("year", year);
      window.location.search = params.toString();
    }
  }
})

// Dynamic cell group structure data
var data = {}

<?php
  $datasets = [
    "ceremony" => $this->ceremony_attendance,
    "cell" => $this->cell_attendance,
  ];

  foreach ($datasets as $key => $districts) {
      echo "data['" . $key . "'] = {};\n";

      # Loop districts
      foreach ($districts as $district_name => $district_data) {
          $district_prefix = "data['" .$key. "']['". $district_name . "']";
          echo $district_prefix . " = {\n"
        . "  'series': [" . implode(", ", $district_data["series"]) . "],\n"
        . "  'zones': {},\n"
        . "};\n";

          # Loop zones
          foreach ($district_data["zones"] as $zone_name => $zone_data) {
              $zone_prefix = $district_prefix . "['zones']['" . $zone_name . "']";
              echo $zone_prefix . " = {\n"
          . "  'series': [" . implode(", ", $zone_data["series"]) . "],\n"
          . "  'cells': {},\n"
          . "};\n";

              # Loop cells
              foreach ($zone_data["cells"] as $cell_name => $cell_data) {
                  $cell_prefix = $zone_prefix . "['cells']['" . $cell_name . "']";
                  echo $cell_prefix . " = {\n"
            . "  'series': [" . implode(", ", $cell_data["series"]) . "],\n"
            . "  'members': {},\n"
            . "};\n";

                  # Loop members
                  foreach ($cell_data["members"] as $member_name => $member_data) {
                      $member_prefix = $cell_prefix . "['members']['" . $member_name . "']";
                      echo $member_prefix . " = {\n"
              . "  'series': [" . implode(", ", $member_data["series"]) . "],\n"
              . "  'member_code': " . $member_data["member_code"] . ",\n"
              . "};\n";
                  }
              }
          }
      }
  }
?>

function getSeries(data) {
  var series = [];
  for (var key in data) {
    series.push({
      name: key,
      data: data[key]["series"],
    });
  }
  return series;
}

var attendance_type = "ceremony";

function updateChart() {
  var district = pastor_menu.selectedOption;
  var zone = district_menu.selectedOption;
  var cell = cellgroup.selectedOption;
  var member = cellmember.selectedOption;

  if (member != "所有組員") {
    var member_data = data[attendance_type][district]["zones"][zone]["cells"][cell]["members"][member];
    pastoral_chart.series = [{
      name: member,
      data: member_data["series"]
    }];
  } else if (cell != "所有小組") {
    var cell_data = data[attendance_type][district]["zones"][zone]["cells"][cell];
    pastoral_chart.series = getSeries(cell_data["members"]);
  } else if (zone != "所有分區") {
    var zone_data = data[attendance_type][district]["zones"][zone];
    pastoral_chart.series = getSeries(zone_data["cells"]);
  } else if (district != "所有牧區") {
    var district_data = data[attendance_type][district];
    pastoral_chart.series = getSeries(district_data["zones"]);
  } else {
    pastoral_chart.series = getSeries(data[attendance_type]);
  }
}

var pastor_menu = new Vue({
  el: '#pastoral',
  data: {
    // myOptionsArray: ["所有牧區", "淑芬牧區", "雪貞牧區", "Sarah牧區", "寶玉牧區", "男士牧區", "榕安牧區", "青年牧區"],
    myOptionsArray: ["所有牧區"].concat(Object.keys(data["ceremony"])),
    selectedOption: '所有牧區'
  },
  methods: {
    handleItemChange(event) {
      var district = event.target.value;

      if (district == '所有牧區') {
        pastor_menu.myOptionsArray = ["所有牧區"].concat(Object.keys(data["ceremony"])),
        district_menu.myOptionsArray = ["所有分區"];
      } else {
        var zones = data["ceremony"][district]["zones"];
        district_menu.myOptionsArray = ["所有分區"].concat(Object.keys(zones));
      }

      cellgroup.myOptionsArray = ["所有小組"];
      cellgroup.selectedOption = "所有小組";
      cellmember.myOptionsArray = ["所有組員"];
      cellmember.selectedOption = "所有組員";

      updateChart();
    },
    init() {
      if (this.myOptionsArray.length == 2) {
      // Auto select the only option
      this.selectedOption = this.myOptionsArray[1];
      this.handleItemChange({
        target: {
          value: this.myOptionsArray[1]
        }
      });
    } else {
      this.selectedOption = this.myOptionsArray[0];
    }
    }
  },
  computed: {
    disabled: function() {
      return this.myOptionsArray.length <= 2;
    }
  },
  created: function() {
    // this.selectedOption = this.myOptionsArray[1];
  },
  mounted() {
    //this.myOptionsArray = this.myOptionsArray.sort();
  }
})

var district_menu = new Vue({
  el: '#district',
  data: {
    myOptionsArray: ["所有分區"],
    selectedOption: '所有分區',
  },
  methods: {
    handleItemChange(event) {
      var zone = event.target.value;
      var district = pastor_menu.selectedOption;

      if (zone == '所有分區') {
        cellgroup.myOptionsArray = ["所有小組"];
        cellgroup.selectedOption = "所有小組";
      } else {
        var cells = data["ceremony"][district]["zones"][zone]["cells"];
        cellgroup.myOptionsArray = ["所有小組"].concat(Object.keys(cells));
        cellgroup.selectedOption = "所有小組";
      }

      cellmember.myOptionsArray = ["所有組員"];
      cellmember.selectedOption = "所有組員";

      updateChart();
    }
  },
  computed: {
    disabled: function() {
      return this.myOptionsArray.length <= 2;
    }
  },
  watch: {
    myOptionsArray: function() {
      if (this.myOptionsArray.length == 2) {
        // Auto select the only option
        this.selectedOption = this.myOptionsArray[1];
        this.handleItemChange({
          target: {
            value: this.myOptionsArray[1]
          }
        });
      } else {
        this.selectedOption = this.myOptionsArray[0];
      }
    }
  },
  mounted() {
    //this.myOptionsArray = this.myOptionsArray.sort();
  }
})

var cellgroup = new Vue({
  el: '#cellgroup',
  data: {
    myOptionsArray: ["所有小組"],
    selectedOption: '所有小組',
  },
  computed: {
    disabled: function() {
      return this.myOptionsArray.length <= 2;
    }
  },
  watch: {
    myOptionsArray: function() {
      if (this.myOptionsArray.length == 2) {
        // Auto select the only option
        this.selectedOption = this.myOptionsArray[1];
        this.handleItemChange({
          target: {
            value: this.myOptionsArray[1]
          }
        });
      } else {
        this.selectedOption = this.myOptionsArray[0];
      }
    }
  },
  mounted() {

  },
  methods: {
    handleItemChange(event) {
      var cell = event.target.value;
      var zone = district_menu.selectedOption;
      var district = pastor_menu.selectedOption;
      
      if (cell == '所有小組') {
        cellmember.myOptionsArray = ["所有組員"];
        cellmember.selectedOption = "所有組員";
      } else {
        var members = data["ceremony"][district]["zones"][zone]["cells"][cell]["members"];
        cellmember.myOptionsArray = ["所有組員"].concat(Object.keys(members));
        cellmember.selectedOption = "所有組員";
      }

      updateChart();
    }
  }
})

var cellmember = new Vue({
  el: '#cellmember',
  data: {
    myOptionsArray: ["所有組員"],
    selectedOption: '所有組員',
  },
  computed: {
    disabled: function() {
      return this.myOptionsArray.length <= 2;
    }
  },
  watch: {
    myOptionsArray: function() {
      if (this.myOptionsArray.length == 2) {
        // Auto select the only option
        this.selectedOption = this.myOptionsArray[1];
        this.handleItemChange({
          target: {
            value: this.myOptionsArray[1]
          }
        });
      } else {
        this.selectedOption = this.myOptionsArray[0];
      }
    }
  },
  mounted() {

  },
  methods: {
    handleItemChange(event) {
      updateChart();
    }
  }
})

var pastoral_chart = new Vue({
  el: '#worship_attendance',
  components: {
    apexchart: VueApexCharts,
  },
  data: {
    series: getSeries(data["ceremony"]),
    chartOptions: {
      colors: ['#A60FD3', '#B959D6', '#FC34C6', '#FF9300', '#2C6417', '#E593CF', "#94D652", "#678052", "#6BA3B9",
        "#00E8FF", "#91002a", "#0e1eec", "#41fca3"
      ],
      chart: {
        type: 'bar',
        height: 250,
        stacked: true,
        toolbar: {
          show: true
        },
        zoom: {
          enabled: false
        }
      },
      responsive: [{
        breakpoint: 480,
        options: {
          legend: {
            position: 'bottom',
            offsetX: -10,
            offsetY: 0
          }
        }
      }],
      plotOptions: {
        bar: {
          horizontal: false,
          borderRadius: 0,
          dataLabels: {
            enabled: false,
            total: {
              enabled: true,
              style: {
                fontSize: '9px',
                fontWeight: 900
              }
            }
          }
        },
      },
      dataLabels: {
        enabled: false
      },
      xaxis: {
        type: 'datetime',
        categories: ['2022-01-01', '2022-01-08', '2022-01-15', '2022-01-22', '2022-01-29', '2022-02-05',
          '2022-02-12', '2022-02-19', '2022-02-26', '2022-03-05', '2022-03-12', '2022-03-19', '2022-03-26',
          '2022-04-02', '2022-04-09', '2022-04-16', '2022-04-23', '2022-04-30', '2022-05-07', '2022-05-14',
          '2022-05-21', '2022-05-28', '2022-06-04', '2022-06-11', '2022-06-18', '2022-06-25', '2022-07-02',
          '2022-07-09', '2022-07-16', '2022-07-23', '2022-07-30', '2022-08-06', '2022-08-13', '2022-08-20',
          '2022-08-27', '2022-09-03', '2022-09-10', '2022-09-17', '2022-09-24', '2022-10-01', '2022-10-08',
          '2022-10-15', '2022-10-22', '2022-10-29', '2022-11-05', '2022-11-12', '2022-11-19', '2022-11-26',
          '2022-12-03', '2022-12-10', '2022-12-17', '2022-12-24', '2022-12-31'
        ],
      },
      legend: {
        position: 'top',
        offsetY: 10
      },
      fill: {
        opacity: 1
      }
    },
  },
})

function toggle_switch() {
  // Get all the toggle switch elements
  var toggleSwitches = document.querySelectorAll('.toggle_switch');

  // Loop through the toggle switch elements
  toggleSwitches.forEach(function(element) {
    // Toggle the active class on the clicked element
    if (element === event.target.parentNode) {
      element.classList.add('active');

      if (element.querySelector('.titleheader').textContent == "崇拜出席人數分佈") {
        attendance_type = "ceremony";
      } else {
        attendance_type = "cell";
      }
    } else {
      element.classList.remove('active');
    }
  });

  updateChart();
}

// Initialize selections
pastor_menu.init();
</script>