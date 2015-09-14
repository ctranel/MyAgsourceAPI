	var MenuEntry = function (title, href, selected, objSubEntries) {
	    var self = this;
		self.name = title;
		self.href = href;
		self.isSelected = ko.observable(false);
	    self.selectedChild = ko.observable();
	    self.children = ko.observableArray();
	    self.numChildren = ko.computed(function(){
	    	return self.children().length;
	    });
	    self.hasChildren = ko.computed(function(){
	    	for(var i in self.children()){
	    		if(self.children()[i].children().length > 0){
  					return true;
	    		}
	    	}
	    	return false;
	    });
		
	    self.threeLevelNav = ko.computed(function(){
	    	for(var i in self.children()){
	    		if(self.children()[i].children().length > 0){
	    	    	for(var j in self.children()[i].children()){
	    	    		if(self.children()[i].children()[j].children().length > 0){
	    					return true;
	    	    		}
	    	    	}
	    		}
	    	}
	    	return false;
	    });
		
	    self.twoLevelNavWithBar = ko.computed(function(){
	    	if(self.threeLevelNav()){
	    		return false;
	    	}
	    	return (self.children().length > 3 && self.hasChildren());
	    });

	    self.twoLevelMega = ko.computed(function(){
	    	if(self.threeLevelNav()){
	    		return false;
	    	}
	    	return (self.children().length < 4 && self.hasChildren());
	    });

	    self.oneLevel = ko.computed(function(){
	    	return !self.hasChildren();
	    });
	    

	    for(var i in objSubEntries){
	    	self.children.push(new MenuEntry(objSubEntries[i].name, objSubEntries[i].href, false, objSubEntries[i].children));
	    }

	    self.deselectChildren = function(){
	    	for(i in self.children()){
	    		self.children()[i].selectedChild = ko.observable();
    			self.children()[i].isSelected(false);
    			if(self.children()[i].numChildren() > 0){
    				self.children()[i].deselectChildren();
    			}
	    	}
	    };
	    
	    self.setSelected = function(menu_item, ev){
	    	self.deselectChildren();
	    	
	    	if(menu_item.children().length === 0){
	    		return true;
	    	}
	    	
	    	self.selectedChild(menu_item);
	    	menu_item.isSelected(true);

			var clicked_nav = $(ev.target).parents('nav').next('nav');
			if(typeof(clicked_nav.attr('id')) === 'undefined'){
				clicked_nav = $(ev.target).parents('nav').children('nav');
			}
			clicked_nav.show();
			if(clicked_nav.attr('id')){
				if(clicked_nav.attr('id').indexOf('layer') < 0){
					clicked_nav.offset({left: $(ev.target).offset().left});
				}
			}
	    };
	};
	
	var ViewModel = ViewModel || function (nav) {
	    var self = this;

	    self.selectedChild = ko.observable();
	    self.children = ko.observableArray();

	    for(i in nav){
	    	self.children.push(new MenuEntry(nav[i].name, nav[i].href, false, nav[i].children));
	    }
		
	    self.deselectChildren = function(){
	    	for(i in self.children()){
	    		self.children()[i].selectedChild = ko.observable();
    			self.children()[i].isSelected(false);
    			if(self.children()[i].numChildren() > 0){
    				self.children()[i].deselectChildren();
    			}
	    	}
	    };
	    
	    self.setSelected = function(menu_item, ev){
	    	self.deselectChildren();
	    	menu_item.isSelected(true);
	    	self.selectedChild(menu_item);
	    	
			var clicked_nav = $(ev.target).parents('nav').next('nav');
			if(typeof(clicked_nav.attr('id')) === 'undefined'){
				clicked_nav = $(ev.target).parents('nav').find('nav');
			}
			clicked_nav.show();
			if(clicked_nav.attr('id').indexOf('layer') < 0){
				clicked_nav.offset({left: $(ev.target).offset().left});
			}
	    };
	};
	
//$(document).ready(function() {
    $.getJSON('/nav/ajax_json', function(nav_data, textStatus, jqXHR){
    	ko.applyBindings(new ViewModel(JSON.parse(decodeHtml(jqXHR.responseText))));
    });
//});
    
function decodeHtml(html) {
    var txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
}