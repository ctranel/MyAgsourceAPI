$(document).mouseup(function (e){
    var containers = [$(".mega"), $(".mega-category")];
	for(i in containers){
		// if the target of the click isn't the container...... nor a descendant of the container
	 	if (!containers[i].is(e.target) && containers[i].has(e.target).length === 0){
	    	containers[i].hide();
	    }
	}
});

//this function moves the navigation along with other page content when scrolling horizontally
function setFixedNav(){
	if(typeof($("#header").offset()) !== 'undefined'){
		var leftInit = $("#container").offset().left;
		$(window).scroll(function(event) {
		    var x = 0 - $(this).scrollLeft();
		    x = x/100;
		    $("#header").offset({
		    	left: x + leftInit
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