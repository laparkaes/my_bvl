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
	ajax_simple({company_id: $(this).attr("value")}, "home/favorite_control").done(function(res) {
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


