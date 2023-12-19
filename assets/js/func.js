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

var data_qty = -600;

function init_price_sma_chart(){
	var dates = JSON.parse($("#ch_dates").html()).slice(data_qty);
	var candles = JSON.parse($("#ch_candles").html()).slice(data_qty);
	var volumes = JSON.parse($("#ch_volumes").html()).slice(data_qty);
	var sma_5 = JSON.parse($("#ch_sma_5").html()).slice(data_qty);
	var sma_20 = JSON.parse($("#ch_sma_20").html()).slice(data_qty);
	var sma_60 = JSON.parse($("#ch_sma_60").html()).slice(data_qty);
	var sma_120 = JSON.parse($("#ch_sma_120").html()).slice(data_qty);
	var sma_200 = JSON.parse($("#ch_sma_200").html()).slice(data_qty);
	console.log(dates);
	console.log(candles);
	console.log(volumes);
	console.log(sma_5);
	console.log(sma_20);
	console.log(sma_60);
	console.log(sma_120);
	console.log(sma_200);
	
	$("#chart_price").remove();
	$("#chart_price_block").html('<div id="chart_price" style="min-height: 500px;"></div>');
	var myChart = echarts.init(document.getElementById('chart_price'));

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
	
	$("#chart_price").remove();
	$("#chart_price_block").html('<div id="chart_price" style="min-height: 500px;"></div>');
	var myChart = echarts.init(document.getElementById('chart_price'));

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

$(document).ready(function() {
	if ($("#chart_price").length){
		init_price_sma_chart();
		$("#chart_data_qty").on('change',(function(e) {
			data_qty = $(this).val();
			init_price_sma_chart();
			$(".btn_chart").removeClass("active");
			$(".btn_chart:first").addClass("active");
		}));
		
		$(".btn_chart").on('click',(function(e) {
			var today = $(this).val();
			//alert($(this).val());
			switch (today) {
				case "ch_price_sma":
					$(".ch_price").removeClass("active");
					$(this).addClass("active");
					init_price_sma_chart();
					break;
				case "ch_price_ema":
					$(".ch_price").removeClass("active");
					$(this).addClass("active");
					init_price_ema_chart();
					break;
				default:
					dayMessage = "알 수 없는 날짜";
			}
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


