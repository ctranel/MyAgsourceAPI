var leftInit;

$(document).mouseup(function (e){
    var containers = [$("#top-nav3-mega"), $("#top-nav2-mega")];
	for(i in containers){
		// if the target of the click isn't the container...... nor a descendant of the container
	 	if (!containers[i].is(e.target) && containers[i].has(e.target).length === 0){
	    	containers[i].hide();
	    }
	}
});

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