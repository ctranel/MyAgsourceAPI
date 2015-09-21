	var MenuEntry = function (title, id, href, selected, objSubEntries) {
	    var self = this;
		self.name = title;
		self.id = id;
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
	    	self.children.push(new MenuEntry(objSubEntries[i].name, objSubEntries[i].id, objSubEntries[i].href, false, objSubEntries[i].children));
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
	    
	    self.setSelected = function(menu_item){
	    	self.deselectChildren();
	    	
	    	if(menu_item.children().length === 0){
	    		return true;
	    	}
	    	self.selectedChild(menu_item);
	    	menu_item.isSelected(true);
	    };

	    //the path_parts param is an array
	    self.setSelectedToCurrentPage = function(path_parts){
	    	for(i in self.children()){
	    		if(path_parts.indexOf(self.children()[i].id) !== -1){
	    			self.selectedChild(self.children()[i]);
	    			self.children()[i].isSelected(true);
	    			var path_param = path_parts.slice(i);
	    			if(path_param.length > 0){
						self.children()[i].setSelectedToCurrentPage(path_param);
					}
	    		}
	    	}
	    	//mega menus should not show when original nav is refreshed
	    	$('.mega').hide();
	    	/*when we no longer worry about IE8 we can do with staight JS:
				var elements = new Array();
				elements = getElementsByClassName('mega');
				for(i in elements ){
				     elements[i].style.display = "none";
				};
			*/
	    };
	};
	
	var ViewModel = ViewModel || function (nav) {
	    var self = this;

	    self.selectedChild = ko.observable();
	    self.children = ko.observableArray();
	    self.numChildren = ko.computed(function(){
	    	return self.children().length;
	    });

	    for(i in nav){
	    	self.children.push(new MenuEntry(nav[i].name, nav[i].id, nav[i].href, false, nav[i].children));
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
	    
	    self.setSelected = function(menu_item){
	    	self.deselectChildren();

	    	menu_item.isSelected(true);
	    	self.selectedChild(menu_item);
	    	if(menu_item.children().length === 0){
	    		return true;
	    	};
	    };
	    
	    self.setSelectedToCurrentPage = function(){
	    	self.deselectChildren();
	    	var path_parts = window.location.pathname.substring(1).split("/");
	    	for(var i in self.children()){
	    		var path_index = path_parts.indexOf(self.children()[i].id);
	    		if(path_index !== -1){
	    			self.setSelected(self.children()[i]);
	    			var path_param = path_parts.slice(path_index + 1);
	    			if(path_param.length > 0){
	    				self.children()[i].setSelectedToCurrentPage(path_param);
	    			}
	    		}
	    	}
	    };
	};
	
$.getJSON('/nav/ajax_json', function(nav_data, textStatus, jqXHR){
	nav_vm = new ViewModel(JSON.parse(decodeHtml(jqXHR.responseText)));
	ko.applyBindings(nav_vm);
	nav_vm.setSelectedToCurrentPage();
	
	//add mouseup function to close menus when clicking anywhere except on the menu
	$(document).mouseup(function (ev){
		//if the click is not within the navigation structure
		if($(ev.target).closest('nav').length === 0){
			nav_vm.setSelectedToCurrentPage();
		}
	});
});
    
function decodeHtml(html) {
    var txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
}