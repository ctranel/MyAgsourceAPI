var leftInit;

function setFixedNav(){
	if(typeof($("#top-nav").offset()) !== 'undefined'){
		leftInit = $("#top-nav").offset().left;
		$(window).scroll(function(event) {
		    var x = 0 - $(this).scrollLeft();
		    x = x/100;
		    $("#top-nav").offset({
		    	left: x + leftInit
		    });
		    
		});
	}
}

//make sure the page width can hold the top nav
$('#container').css('minWidth', $("#top-nav").width());