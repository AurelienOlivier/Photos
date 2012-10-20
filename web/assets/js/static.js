$(function(){
	// QueryLoader
	$("body").queryLoader2({
		barColor: "#f2f2f2",
		backgroundColor: "#000",
		percentage: true,
		barHeight: 30,
		completeAnimation: "grow"
	});

	// Parallaxe
	$('.bg').each(function(i, item){
		$(item).parallax("50%", (Math.floor(Math.random() * 6) + 5) * 0.1); // set random value between 0.5 - 1
	});
	
	// Create gridster
	$(".gridster ul").gridster({
		widget_margins: [10, 15],
		widget_base_dimensions: [140, 140]
	});
	
	// Center images in wrapper
	var h, w, hp, wp; 
	$('.gridster ul li img').each(function(i,item){
		h = parseFloat($(item).css('height'));
		hp = parseFloat($(item).parent('li').css('height'));
		w = parseFloat($(item).css('width'));
		wp = parseFloat($(item).parent('li').css('width'));
		
		if (h > hp) {
			$(item).css('margin-top',-(h-hp)*.5);
		}
		if (w > wp) {
			$(item).css('margin-left',-(w-wp)*.5);
		}
	});
	
	// Colorbox
	$('.gridster .thumbnail a').colorbox();
});