$(document).ready(function(){
	var docked = 0;
	
	$("#dock li div.panel").height($(window).height());
	
	$("#dock .dock").click(function(){
		$(this).parent().parent().addClass("docked").removeClass("panel");
		
		docked += 1;
		var dockH = ($(window).height()) / docked
		var dockT = 0;               
		
		$("#dock li div.docked").each(function(){
			$(this).height(dockH).css("top", dockT + "px");
			dockT += dockH;
		});
		$(this).parent().find(".undock").show();
		$(this).hide();
		
		if (docked > 0)
			$("#content").css("margin-left","250px");
		else
			$("#content").css("margin-left", "60px");
	});
	
	$("#dock .undock").click(function(){
		$(this).parent().parent().addClass("panel").removeClass("docked")
			.animate({left:"-180px"}, 200).height($(window).height()).css("top", "0px");
		
		docked = docked - 1;
		var dockH = ($(window).height()) / docked
		var dockT = 0;               
		
		$("#dock li div.docked").each(function(){
			$(this).height(dockH).css("top", dockT + "px");
			dockT += dockH;
		});
		$(this).parent().find(".dock").show();
		$(this).hide();
		
		if (docked > 0)
			$("#content").css("margin-left", "250px");
		else
			$("#content").css("margin-left", "60px");
	});

	$("#dock li").hover(function(){
		$(this).find("div.panel").animate({left:"40px"}, 200);
	}, function(){
		$(this).find("div.panel").animate({left:"-180px"}, 200);
   });
}); 