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

var data_qty = -300;
var ch_selected = "ch_price_sma";

function init_price_sma_chart(){
	var dates = JSON.parse($("#ch_dates").html()).slice(data_qty);
	var candles = JSON.parse($("#ch_candles").html()).slice(data_qty);
	var volumes = JSON.parse($("#ch_volumes").html()).slice(data_qty);
	var sma_5 = JSON.parse($("#ch_sma_5").html()).slice(data_qty);
	var sma_20 = JSON.parse($("#ch_sma_20").html()).slice(data_qty);
	var sma_60 = JSON.parse($("#ch_sma_60").html()).slice(data_qty);
	var sma_120 = JSON.parse($("#ch_sma_120").html()).slice(data_qty);
	var sma_200 = JSON.parse($("#ch_sma_200").html()).slice(data_qty);
	
	var myChart = echarts.init(document.getElementById('ch_content'));
	var options = {
		title: {text: "Precio & SMA"},
		animation: false,
		color: ['#c23531', '#2f4554', '#61a0a8', '#d48265', '#91c7ae', '#749f83', '#ca8622', '#bda29a', '#6e7074', '#546570'],
		legend: {
			left: 'center',
			data: ['Precio', 'SMA5', 'SMA20', 'SMA60', 'SMA120', 'SMA200']
		},
		tooltip: {
			trigger: 'axis', axisPointer: {type: 'line'}, borderWidth: 1, borderColor: '#ccc',
			position: function (pos, params, el, elRect, size){
				const obj = {top: 10};
				obj[['left', 'right'][+(pos[0] < size.viewSize[0] / 2)]] = 30;
				return obj;
			}
		},
		axisPointer: {link: [{xAxisIndex: 'all'}], label: {backgroundColor: '#777'}},
		grid: [
			{left: '10%', right: '8%', height: '55%'},
			{left: '10%', right: '8%', height: '20%', top: '75%'}
		],
		xAxis: [
			{type: 'category', data: dates},
			{type: 'category', data: dates, axisLabel: { show: false }, gridIndex: 1}
		],
		yAxis: [
			{scale: true, splitArea: {show: true}},
			{scale: true, splitNumber: 2, axisLabel: { show: false }, axisLine: { show: false }, axisTick: { show: false }, splitLine: { show: false }, gridIndex: 1}
		],
		series: [
			{name: 'Precio', type: 'candlestick', data: candles},
			{name: 'SMA5', type: 'line', data: sma_5, smooth: true, showSymbol: false, lineStyle: {width: 1, opacity: 0.5}},
			{name: 'SMA20', type: 'line', data: sma_20, smooth: true, showSymbol: false, lineStyle: {width: 1, opacity: 0.5}},
			{name: 'SMA60', type: 'line', data: sma_60, smooth: true, showSymbol: false, lineStyle: {width: 1, opacity: 0.5}},
			{name: 'SMA120', type: 'line', data: sma_120, smooth: true, showSymbol: false, lineStyle: {width: 1, opacity: 0.5}},
			{name: 'SMA200', type: 'line', data: sma_200, smooth: true, showSymbol: false, lineStyle: {width: 1, opacity: 0.5}},
			{name: 'Volume', type: 'bar', data: volumes, xAxisIndex: 1, yAxisIndex: 1}
		]
	};
	
	myChart.setOption((options), true);
}

function init_price_ema_chart(){
	var dates = JSON.parse($("#ch_dates").html()).slice(data_qty);
	var candles = JSON.parse($("#ch_candles").html()).slice(data_qty);
	var volumes = JSON.parse($("#ch_volumes").html()).slice(data_qty);
	var ema_5 = JSON.parse($("#ch_ema_5").html()).slice(data_qty);
	var ema_20 = JSON.parse($("#ch_ema_20").html()).slice(data_qty);
	var ema_60 = JSON.parse($("#ch_ema_60").html()).slice(data_qty);
	var ema_120 = JSON.parse($("#ch_ema_120").html()).slice(data_qty);
	var ema_200 = JSON.parse($("#ch_ema_200").html()).slice(data_qty);
	
	var myChart = echarts.init(document.getElementById('ch_content'));
	var options = {
		title: {text: "Precio & EMA"},
		animation: false,
		color: ['#c23531', '#2f4554', '#61a0a8', '#d48265', '#91c7ae', '#749f83', '#ca8622', '#bda29a', '#6e7074', '#546570'],
		legend: {
			left: 'center',
			data: ['Precio', 'EMA5', 'EMA20', 'EMA60', 'EMA120', 'EMA200']
		},
		tooltip: {
			trigger: 'axis', axisPointer: {type: 'line'}, borderWidth: 1, borderColor: '#ccc',
			position: function (pos, params, el, elRect, size){
				const obj = {top: 10};
				obj[['left', 'right'][+(pos[0] < size.viewSize[0] / 2)]] = 30;
				return obj;
			}
		},
		axisPointer: {link: [{xAxisIndex: 'all'}], label: {backgroundColor: '#777'}},
		grid: [
			{left: '10%', right: '8%', height: '55%'},
			{left: '10%', right: '8%', height: '20%', top: '75%'}
		],
		xAxis: [
			{type: 'category', data: dates},
			{type: 'category', data: dates, axisLabel: { show: false }, gridIndex: 1}
		],
		yAxis: [
			{scale: true, splitArea: {show: true}},
			{scale: true, splitNumber: 2, axisLabel: { show: false }, axisLine: { show: false }, axisTick: { show: false }, splitLine: { show: false }, gridIndex: 1}
		],
		series: [
			{name: 'Precio', type: 'candlestick', data: candles},
			{name: 'EMA5', type: 'line', data: ema_5, smooth: true, showSymbol: false, lineStyle: {width: 1, opacity: 0.5}},
			{name: 'EMA20', type: 'line', data: ema_20, smooth: true, showSymbol: false, lineStyle: {width: 1, opacity: 0.5}},
			{name: 'EMA60', type: 'line', data: ema_60, smooth: true, showSymbol: false, lineStyle: {width: 1, opacity: 0.5}},
			{name: 'EMA120', type: 'line', data: ema_120, smooth: true, showSymbol: false, lineStyle: {width: 1, opacity: 0.5}},
			{name: 'EMA200', type: 'line', data: ema_200, smooth: true, showSymbol: false, lineStyle: {width: 1, opacity: 0.5}},
			{name: 'Volume', type: 'bar', data: volumes, xAxisIndex: 1, yAxisIndex: 1}
		]
	};
	
	myChart.setOption((options), true);
}

function init_price_ly_chart(){
	var dates = JSON.parse($("#ch_dates").html()).slice(data_qty);
	var candles = JSON.parse($("#ch_candles").html()).slice(data_qty);
	var ly_min = JSON.parse($("#ch_last_year_min").html()).slice(data_qty);
	var ly_max = JSON.parse($("#ch_last_year_max").html()).slice(data_qty);
	var ly_per = JSON.parse($("#ch_last_year_per").html()).slice(data_qty);
	
	var myChart = echarts.init(document.getElementById('ch_content'));
	var options = {
		title: {text: "Precio & Ult. Año"},
		animation: false,
		color: ['#c23531', '#2f4554', '#61a0a8', '#d48265', '#91c7ae', '#749f83', '#ca8622', '#bda29a', '#6e7074', '#546570'],
		legend: {
			left: 'center',
			data: ['Precio', 'Max', 'Min']
		},
		tooltip: {
			trigger: 'axis', axisPointer: {type: 'line'}, borderWidth: 1, borderColor: '#ccc',
			position: function (pos, params, el, elRect, size){
				const obj = {top: 10};
				obj[['left', 'right'][+(pos[0] < size.viewSize[0] / 2)]] = 30;
				return obj;
			}
		},
		axisPointer: {link: [{xAxisIndex: 'all'}], label: {backgroundColor: '#777'}},
		grid: [
			{left: '10%', right: '8%', height: '55%'},
			{left: '10%', right: '8%', height: '20%', top: '75%'}
		],
		xAxis: [
			{type: 'category', data: dates},
			{type: 'category', data: dates, axisLabel: { show: false }, gridIndex: 1}
		],
		yAxis: [
			{scale: true, splitArea: {show: true}},
			{scale: true, splitNumber: 2, axisLabel: { show: false }, axisLine: { show: false }, axisTick: { show: false }, splitLine: { show: false }, gridIndex: 1}
		],
		series: [
			{name: 'Precio', type: 'candlestick', data: candles},
			{name: 'Max', type: 'line', data: ly_max, smooth: true, showSymbol: false, lineStyle: {width: 1, opacity: 0.5}},
			{name: 'Min', type: 'line', data: ly_min, smooth: true, showSymbol: false, lineStyle: {width: 1, opacity: 0.5}},
			{name: 'Per', type: 'bar', data: ly_per, xAxisIndex: 1, yAxisIndex: 1}
		]
	};
	
	myChart.setOption((options), true);
}

function init_indicators_chart(){
	function make_ind_chart(id, title, legend, xAxis, yAxis, series){
		var options = {
			title: {text: title},
			animation: false,
			color: ['#2f4554', '#61a0a8', '#c23531', '#d48265', '#91c7ae', '#749f83', '#ca8622', '#bda29a', '#6e7074', '#546570'],
			legend: {left: 'center', data: legend},
			tooltip: {
				trigger: 'axis', axisPointer: {type: 'line'}, borderWidth: 1, borderColor: '#ccc',
				position: function (pos, params, el, elRect, size){
					const obj = {top: 10};
					obj[['left', 'right'][+(pos[0] < size.viewSize[0] / 2)]] = 30;
					return obj;
				}
			},
			axisPointer: {link: [{xAxisIndex: 'all'}], label: {backgroundColor: '#777'}},
			grid: [{left: '10%', right: '8%', height: '50%'}],
			xAxis: [{type: 'category', data: xAxis, position: 'bottom', axisLine: {onZero: false}}],
			yAxis: [
				{name: yAxis[0], type: 'value', splitNumber: 1, min: "dataMin", max: "dataMax", splitLine: {show: false}, axisLabel: false},
				{name: yAxis[1], type: 'value', splitNumber: 1, min: "dataMin", max: "dataMax", splitLine: {show: false}, axisLabel: false},
			],
			series: series,
		};
		
		$("#ch_content").append('<div class="mt-3" id="' + id + '" style="min-height: 200px;"></div>');
		var myChart = echarts.init(document.getElementById(id));
		myChart.setOption((options), true);
	}
	
	function init_signals_chart(){
		var legend = ['Precio', 'Compra', 'Venta'];
		var xAxis = JSON.parse($("#ch_dates").html()).slice(data_qty);
		var yAxis = ['Señales', 'Precio'];
		var series = [
			{name: 'Precio', type: 'line', data: JSON.parse($("#ch_prices").html()).slice(data_qty),
				smooth: true, showSymbol: false, yAxisIndex: 1},
			{name: 'Compra', type: 'line', data: JSON.parse($("#ch_buy_sigs").html()).slice(data_qty),
				smooth: true, showSymbol: false, lineStyle: {width: 1}},
			{name: 'Venta', type: 'line', data: JSON.parse($("#ch_sell_sigs").html()).slice(data_qty),
				smooth: true, showSymbol: false, lineStyle: {width: 1}},
		];
		
		make_ind_chart("chart_signals", "#Señales", legend, xAxis, yAxis, series);
	}
	
	function init_adx_chart(){
		var legend = ['Precio', 'ADX', 'PDI', 'MDI'];
		var xAxis = JSON.parse($("#ch_dates").html()).slice(data_qty);
		var yAxis = ['ADX', 'Precio'];
		var series = [
			{name: 'Precio', type: 'line', data: JSON.parse($("#ch_prices").html()).slice(data_qty),
				smooth: true, showSymbol: false, yAxisIndex: 1},
			{name: 'MDI', type: 'line', data: JSON.parse($("#ch_adx_mdi").html()).slice(data_qty),
				smooth: true, showSymbol: false, lineStyle: {width: 1}},
			{name: 'PDI', type: 'line', data: JSON.parse($("#ch_adx_pdi").html()).slice(data_qty),
				smooth: true, showSymbol: false, lineStyle: {width: 1}},
			{name: 'ADX', type: 'line', data: JSON.parse($("#ch_adx").html()).slice(data_qty),
				smooth: true, showSymbol: false, lineStyle: {width: 1}},
		];
		
		make_ind_chart("chart_adx", "ADX", legend, xAxis, yAxis, series);
	}
	
	function init_atr_chart(){
		var legend = ['Precio', 'ATR'];
		var xAxis = JSON.parse($("#ch_dates").html()).slice(data_qty);
		var yAxis = ['ATR', 'Precio'];
		var series = [
			{name: 'Precio', type: 'line', data: JSON.parse($("#ch_prices").html()).slice(data_qty),
				smooth: true, showSymbol: false, yAxisIndex: 1},
			{name: 'ATR', type: 'line', data: JSON.parse($("#ch_atr").html()).slice(data_qty),
				smooth: true, showSymbol: false, lineStyle: {width: 1}},
		];
		
		make_ind_chart("chart_atr", "ATR", legend, xAxis, yAxis, series);
	}
	
	function init_cci_chart(){
		var legend = ['Precio', 'CCI'];
		var xAxis = JSON.parse($("#ch_dates").html()).slice(data_qty);
		var yAxis = ['CCI', 'Precio'];
		var series = [
			{name: 'Precio', type: 'line', data: JSON.parse($("#ch_prices").html()).slice(data_qty),
				smooth: true, showSymbol: false, yAxisIndex: 1},
			{name: 'CCI', type: 'line', data: JSON.parse($("#ch_cci").html()).slice(data_qty),
				smooth: true, showSymbol: false, lineStyle: {width: 1}},
		];
		
		make_ind_chart("chart_cci", "CCI", legend, xAxis, yAxis, series);
	}
	
	function init_macd_chart(){
		var legend = ['Precio', 'MACD', 'Signal', 'Divergence'];
		var xAxis = JSON.parse($("#ch_dates").html()).slice(data_qty);
		var yAxis = ['MACD', 'Precio'];
		var series = [
			{name: 'Precio', type: 'line', data: JSON.parse($("#ch_prices").html()).slice(data_qty),
				smooth: true, showSymbol: false, yAxisIndex: 1},
			{name: 'MACD', type: 'line', data: JSON.parse($("#ch_macd").html()).slice(data_qty),
				smooth: true, showSymbol: false, lineStyle: {width: 1}},
			{name: 'Signal', type: 'line', data: JSON.parse($("#ch_macd_sig").html()).slice(data_qty),
				smooth: true, showSymbol: false, lineStyle: {width: 1}},
			{name: 'Divergence', type: 'line', data: JSON.parse($("#ch_macd_div").html()).slice(data_qty),
				smooth: true, showSymbol: false, lineStyle: {width: 1}},
		];
		
		make_ind_chart("chart_macd", "MACD", legend, xAxis, yAxis, series);
	}
	
	function init_mfi_chart(){
		var legend = ['Precio', 'MFI'];
		var xAxis = JSON.parse($("#ch_dates").html()).slice(data_qty);
		var yAxis = ['MFI', 'Precio'];
		var series = [
			{name: 'Precio', type: 'line', data: JSON.parse($("#ch_prices").html()).slice(data_qty),
				smooth: true, showSymbol: false, yAxisIndex: 1},
			{name: 'MFI', type: 'line', data: JSON.parse($("#ch_mfi").html()).slice(data_qty),
				smooth: true, showSymbol: false, lineStyle: {width: 1}},
		];
		
		make_ind_chart("chart_mfi", "MFI", legend, xAxis, yAxis, series);
	}
	
	function init_mom_chart(){
		var legend = ['Precio', 'MOM', 'Signal'];
		var xAxis = JSON.parse($("#ch_dates").html()).slice(data_qty);
		var yAxis = ['MOM', 'Precio'];
		var series = [
			{name: 'Precio', type: 'line', data: JSON.parse($("#ch_prices").html()).slice(data_qty),
				smooth: true, showSymbol: false, yAxisIndex: 1},
			{name: 'MOM', type: 'line', data: JSON.parse($("#ch_mom").html()).slice(data_qty),
				smooth: true, showSymbol: false, lineStyle: {width: 1}},
			{name: 'Signal', type: 'line', data: JSON.parse($("#ch_mom_sig").html()).slice(data_qty),
				smooth: true, showSymbol: false, lineStyle: {width: 1}},
		];
		
		make_ind_chart("chart_mom", "MOM", legend, xAxis, yAxis, series);
	}
	
	
	
	init_signals_chart();
	init_adx_chart();
	init_atr_chart();
	init_cci_chart();
	init_macd_chart();
	init_mfi_chart();
	init_mom_chart();
	$("#ch_content").append("hola como estas?");
}

function init_bands_chart(){
	function make_band_chart(id, title, legend, xAxis, series){
		var options = {
			title: {text: title},
			animation: false,
			color: ['#2f4554', '#61a0a8', '#c23531', '#d48265', '#91c7ae', '#749f83', '#ca8622', '#bda29a', '#6e7074', '#546570'],
			legend: {left: 'center', data: legend},
			tooltip: {
				trigger: 'axis', axisPointer: {type: 'line'}, borderWidth: 1, borderColor: '#ccc',
				position: function (pos, params, el, elRect, size){
					const obj = {top: 10};
					obj[['left', 'right'][+(pos[0] < size.viewSize[0] / 2)]] = 30;
					return obj;
				}
			},
			axisPointer: {link: [{xAxisIndex: 'all'}], label: {backgroundColor: '#777'}},
			grid: [{left: '10%', right: '8%', height: '70%'}],
			xAxis: [{type: 'category', data: xAxis}],
			yAxis: [{name: 'Precio', type: 'value', min: 'dataMin'}],
			series: series,
		};
		
		$("#ch_content").append('<div class="mt-3" id="' + id + '" style="min-height: 400px;"></div>');
		var myChart = echarts.init(document.getElementById(id));
		myChart.setOption((options), true);
	}
	
	function init_bb_chart(){
		var legend = ['Precio', 'Superior', 'Medio', 'Inferior'];
		var xAxis = JSON.parse($("#ch_dates").html()).slice(data_qty);
		var series = [
			{name: 'Precio', type: 'line', data: JSON.parse($("#ch_prices").html()).slice(data_qty),
				smooth: true, showSymbol: false},
			{name: 'Superior', type: 'line', data: JSON.parse($("#ch_bb_u").html()).slice(data_qty),
				smooth: true, showSymbol: false, lineStyle: {width: 1}},
			{name: 'Medio', type: 'line', data: JSON.parse($("#ch_bb_m").html()).slice(data_qty),
				smooth: true, showSymbol: false, lineStyle: {width: 1}},
			{name: 'Inferior', type: 'line', data: JSON.parse($("#ch_bb_l").html()).slice(data_qty),
				smooth: true, showSymbol: false, lineStyle: {width: 1}},
		];
		
		make_band_chart("chart_bb", "Bollinger Band", legend, xAxis, series);
	}
	
	function init_env_chart(){
		var legend = ['Precio', 'Superior', 'Inferior'];
		var xAxis = JSON.parse($("#ch_dates").html()).slice(data_qty);
		var series = [
			{name: 'Precio', type: 'line', data: JSON.parse($("#ch_prices").html()).slice(data_qty),
				smooth: true, showSymbol: false},
			{name: 'Superior', type: 'line', data: JSON.parse($("#ch_env_u").html()).slice(data_qty),
				smooth: true, showSymbol: false, lineStyle: {width: 1}},
			{name: 'Inferior', type: 'line', data: JSON.parse($("#ch_env_l").html()).slice(data_qty),
				smooth: true, showSymbol: false, lineStyle: {width: 1}},
		];
		
		make_band_chart("chart_env", "Envelope", legend, xAxis, series);
	}
	
	function init_ich_chart(){
		var ich_a = JSON.parse($("#ch_ich_a").html()).slice(data_qty);
		var ich_b = JSON.parse($("#ch_ich_b").html()).slice(data_qty);

		var len = ich_a.length;
		var data_1 = [];
		var data_2 = [];
		var min;
		var val;
		var color;
		for (var i = 0; i < len; i++) {
			val = ich_b[i] - ich_a[i];
			color = (val > 0) ? '#c23531' : '#61a0a8';
			min = (ich_b[i] < ich_a[i]) ? ich_b[i] : ich_a[i];
			
			data_1.push(min);
			data_2.push({value: (parseFloat(Math.abs(val.toFixed(3)))), itemStyle: {color: color}});
		}
		
		var legend = ['Precio', 'Ichimoku Cloud', 'Ichimoku Cloud'];
		var xAxis = JSON.parse($("#ch_dates").html()).slice(data_qty);
		var series = [
			{name: 'Precio', type: 'line', data: JSON.parse($("#ch_prices").html()).slice(data_qty),
				smooth: true, showSymbol: false},
			{name: 'Placeholder', type: 'bar', stack: 'Total', itemStyle: {borderColor: 'transparent', color: 'transparent'}, data: data_1},
			{name: 'I Clould', type: 'bar', stack: 'Total', data: data_2, itemStyle: {opacity: 0.5}},
		];
		
		make_band_chart("chart_ich", "Ichimoku Cloud", legend, xAxis, series);
	}
	
	function init_psar_chart(){
		var dates = JSON.parse($("#ch_dates").html()).slice(data_qty);
		var psar = JSON.parse($("#ch_psar").html()).slice(data_qty);
		var psar_data = [];
		var len = psar.length;
		for (var i = 0; i < len; i++) psar_data.push([dates[i], psar[i]]);
		
		var legend = ['Precio', 'Parabolic Sar'];
		var xAxis = dates;
		var series = [
			{name: 'Precio', type: 'line', data: JSON.parse($("#ch_prices").html()).slice(data_qty),
				smooth: true, showSymbol: false},
			{name: 'Parabolic Sar', type: 'scatter', data: psar_data, symbolSize: 2},
		];
		
		make_band_chart("chart_psar", "Parabolic Sar", legend, xAxis, series);
	}
	
	
	init_bb_chart();
	init_env_chart();
	init_ich_chart();
	init_psar_chart();
}

function set_chart(selected, dom){
	$("#chart_block").html('<div id="ch_content" style="min-height: 500px;"></div>');
	
	ch_selected = selected;
	if (dom != null){
		$(".btn_chart").removeClass("active");
		$(dom).addClass("active");
	}
	switch (selected) {
		case "ch_price_sma":
			init_price_sma_chart();
			break;
		case "ch_price_ema":
			init_price_ema_chart();
			break;
		case "ch_price_ly":
			init_price_ly_chart();
			break;
		case "ch_indicators":
			init_indicators_chart();
			break;
		case "ch_bands":
			init_bands_chart();
			break;
		default:
			alert("Hola mundo");
	}
}

$(document).ready(function() {
	if ($("#chart_block").length){
		set_chart(ch_selected, null);
		
		$("#chart_data_qty").on('change',(function(e) {
			data_qty = $(this).val();
			set_chart(ch_selected, null);
		}));
		
		$(".btn_chart").on('click',(function(e) {
			set_chart($(this).val(), this);
		}));
	}
});



///////////////////////////




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


