
//this function moves the navigation along with other page content when scrolling horizontally
function setFixedNav(){
	if(typeof($("#header").offset()) !== 'undefined'){
		$(window).scroll(function(event) {
		    var x = 0 - $(this).scrollLeft();
		    x = x/100;
		    $("#header").offset({
		    	left: x + $("#container").offset().left
		    });
		});
	}
}

//make sure the page width can hold the top nav
//$('#container').css('minWidth', $("#top-nav").width());
/*
console.log($("#container").width());
console.log($("#top-nav").width());
$('#top-nav').css('width', $("#container").width() - 200);
console.log($("#top-nav").width());
alert('ugh!');
*/