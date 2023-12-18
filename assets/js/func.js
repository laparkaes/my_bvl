var base_url = $("#base_url").val();

function ajax_form(dom, url){
	var deferred = $.Deferred();
	$.ajax({
		url: base_url + url,
		type: "POST",
		data: new FormData(dom),
		contentType: false,
		processData:false,
		success:function(res){
			deferred.resolve(res);
		}
	});
	
	return deferred.promise();
}

function ajax_simple(data, url){
	var deferred = $.Deferred();
	$.ajax({
		url: base_url + url,
		type: "POST",
		data: data,
		success:function(res){
			deferred.resolve(res);
		}
	});
	
	return deferred.promise();
}

$(".ic_fav_control").on('click',(function(e) {
	ajax_simple({company_id: $(this).attr("value")}, "company/favorite_control").done(function(res) {
		var dom = ".ic_fav_" + res.company_id;
		if (res.type == "inserted"){
			$(dom).removeClass("bi-star");
			$(dom).addClass("bi-star-fill");
		}else{
			$(dom).addClass("bi-star");
			$(dom).removeClass("bi-star-fill");
		}
	});
}));


var chartDom = document.getElementById('chart_block');
var myChart = echarts.init(chartDom);


  var dates = JSON.parse($("#ch_dates").html());
  var candles = JSON.parse($("#ch_candles").html());
  var volumes = JSON.parse($("#ch_volumes").html());
  var sma_5 = JSON.parse($("#ch_sma_5").html());
  var sma_20 = JSON.parse($("#ch_sma_20").html());
  var sma_60 = JSON.parse($("#ch_sma_60").html());
  var sma_120 = JSON.parse($("#ch_sma_120").html());
  var sma_200 = JSON.parse($("#ch_sma_200").html());
  
const upColor = '#00da3c';
const downColor = '#ec0000';
  
  
    
  var options = {
      animation: false,
      legend: {
        bottom: 10,
        left: 'center',
        data: ['Precio', 'SMA5', 'SMA20', 'SMA60', 'SMA120', 'SMA200']
      },
      tooltip: {
        trigger: 'axis',
        axisPointer: {
          type: 'cross'
        },
        borderWidth: 1,
        borderColor: '#ccc',
        padding: 10,
        textStyle: {
          color: '#000'
        },
        position: function (pos, params, el, elRect, size) {
          const obj = {
            top: 10
          };
          obj[['left', 'right'][+(pos[0] < size.viewSize[0] / 2)]] = 30;
          return obj;
        }
        // extraCssText: 'width: 170px'
      },
      axisPointer: {
        link: [
          {
            xAxisIndex: 'all'
          }
        ],
        label: {
          backgroundColor: '#777'
        }
      },
      toolbox: {
        feature: {
          dataZoom: {
            yAxisIndex: false
          },
          brush: {
            type: ['lineX', 'clear']
          }
        }
      },
      brush: {
        xAxisIndex: 'all',
        brushLink: 'all',
        outOfBrush: {
          colorAlpha: 0.1
        }
      },
      visualMap: {
        show: false,
        seriesIndex: 5,
        dimension: 2,
        pieces: [
          {
            value: 1,
            color: downColor
          },
          {
            value: -1,
            color: upColor
          }
        ]
      },
      grid: [
        {
          left: '10%',
          right: '8%',
          height: '50%'
        },
        {
          left: '10%',
          right: '8%',
          top: '63%',
          height: '16%'
        }
      ],
      xAxis: [
        {
          type: 'category',
          data: dates,
          boundaryGap: false,
          axisLine: { onZero: false },
          splitLine: { show: false },
          min: 'dataMin',
          max: 'dataMax',
          axisPointer: {
            z: 100
          }
        },
        {
          type: 'category',
          gridIndex: 1,
          data: dates,
          boundaryGap: false,
          axisLine: { onZero: false },
          axisTick: { show: false },
          splitLine: { show: false },
          axisLabel: { show: false },
          min: 'dataMin',
          max: 'dataMax'
        }
      ],
      yAxis: [
        {
          scale: true,
          splitArea: {
            show: true
          },
        },
        {
          scale: true,
          gridIndex: 1,
          splitNumber: 2,
          axisLabel: { show: false },
          axisLine: { show: false },
          axisTick: { show: false },
          splitLine: { show: false }
        }
      ],
      dataZoom: [
        {
          type: 'inside',
          xAxisIndex: [0, 1],
          start: 98,
          end: 100
        },
        {
          show: true,
          xAxisIndex: [0, 1],
          type: 'slider',
          top: '85%',
          start: 98,
          end: 100
        }
      ],
      series: [
        {
          name: 'Precio',
          type: 'candlestick',
          data: candles,
          itemStyle: {
            color: upColor,
            color0: downColor,
            borderColor: undefined,
            borderColor0: undefined
          }
        },
        {
          name: 'SMA5',
          type: 'line',
          data: sma_5,
          smooth: true,
          lineStyle: {
            opacity: 0.5
          }
        },
        {
          name: 'SMA20',
          type: 'line',
          data: sma_20,
          smooth: true,
          lineStyle: {
            opacity: 0.5
          }
        },
        {
          name: 'SMA60',
          type: 'line',
          data: sma_60,
          smooth: true,
          lineStyle: {
            opacity: 0.5
          }
        },
        {
          name: 'SMA120',
          type: 'line',
          data: sma_120,
          smooth: true,
          lineStyle: {
            opacity: 0.5
          }
        },
        {
          name: 'SMA200',
          type: 'line',
          data: sma_200,
          smooth: true,
          lineStyle: {
            opacity: 0.5
          }
        },
        {
          name: 'Volume',
          type: 'bar',
          xAxisIndex: 1,
          yAxisIndex: 1,
          data: volumes
        }
      ]
    };
  
  
  myChart.setOption(
    (options),
    true
  );

option && myChart.setOption(option);





$(document).ready(function() {
	
});



///////////////////////////


$(".btn_delete_sender").on('click',(function(e) {
	if (!confirm("Are you sure you want to delete sender?")) event.preventDefault();
}));

$(".btn_delete_content").on('click',(function(e) {
	if (!confirm("Are you sure you want to delete content record?")) event.preventDefault();
}));

$(".btn_delete_email_list").on('click',(function(e) {
	if (!confirm("Are you sure you want to delete email list?")) event.preventDefault();
}));

$(".btn_delete_email").on('click',(function(e) {
	if (!confirm("Are you sure you want to delete email record?")) event.preventDefault();
}));

var interval_id;

$("#btn_start").on('click',(function(e) {
	total = 0;
	$("#bl_mailing_result").html("");
	$("#bl_mailing_result").prepend("Starting...<br/>");
	$("#btn_start").addClass("d-none");
	$("#btn_stop").removeClass("d-none");
	
	interval_id = setInterval(function() {
		$("#form_send_email").submit();
    }, 15000); // 10초를 밀리초로 표현한 값
}));

$("#btn_stop").on('click',(function(e) {
	$("#btn_start").removeClass("d-none");
	$("#btn_stop").addClass("d-none");
	
	clearInterval(interval_id);
	$("#bl_mailing_result").prepend("Finished<br/>");
}));
	

$("#form_send_email").submit(function(e) {
	e.preventDefault();
	ajax_form(this, "home/send_email").done(function(res) {
		if (res.type == "success"){
			if ($("#bl_mailing_result").text().includes(res.email)) $("#btn_stop").trigger('click');
			$("#bl_mailing_result").prepend(res.email + "<br/>");
		}else{
			$("#bl_mailing_result").prepend(res.msg);
			$("#btn_stop").trigger('click');
		}
	});
});


