	var MenuEntry = function (title, selected, objSubEntries) {
	    var self = this;
		self.name = title;
		self.isSelected = ko.observable(false);
	    self.selectedChild = ko.observable();
	    self.children = ko.observableArray();
	    self.hasChildren = ko.computed(function(){
	    	for(var i in self.children()){
	    		if(self.children()[i].children().length > 0){
  					return true;
	    		}
	    	}
	    	return false;
	    });
		
	    self.hasGrandchildren = ko.computed(function(){
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
		
	    for(var i in objSubEntries){
	    	self.children.push(new MenuEntry(objSubEntries[i].name, false, objSubEntries[i].children));
	    }

//	    if(typeof(self.children()[0]) !== 'undefined'){
//	    	self.selectedChild(self.children()[0]);
//	    	self.children()[0].isSelected(true);
//	    }
	    self.setSelected = function(menu_item, ev){
	    	for(i in self.children()){
	    		self.children()[i].isSelected(false);
	    	}
	    	self.selectedChild(menu_item);
	    	menu_item.isSelected(true);

	    	
			var clicked_nav = $(ev.target).parents('nav').next('nav');
			if(typeof(clicked_nav.attr('id')) === 'undefined'){
				clicked_nav = $(ev.target).parents('nav').children('nav');
			}
//			else{
				clicked_nav.show();
				if(clicked_nav.attr('id').indexOf('layer') < 0){
					clicked_nav.offset({left: $(ev.target).offset().left});
				}
//	    	}
	    };
	};
	
	var ViewModel = ViewModel || function () {
	    var self = this;

	    self.selectedChild = ko.observable();
	    self.children = ko.observableArray();

	    $.getJSON('/js/nav_data_func_mega.json', function(nav_data, textStatus, jqXHR){
			    var nav = JSON.parse(jqXHR.responseText);
			    for(i in nav){
			    	self.children.push(new MenuEntry(nav[i].name, false, nav[i].children));
			    }
		//	    if(typeof(self.children()[0]) !== 'undefined'){
		//	    	self.selectedChild(self.children()[0]);
		//	    	self.children()[0].isSelected(true);
		//	    }
		    }
		);
		
	    self.setSelected = function(menu_item, ev){
	    	for(var i in self.children()){
	    		self.children()[i].isSelected(false);
	    	}
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
/*	    
	    jqxhro.complete(function(){
console.log(jqxhro.responseText);		    
		    var nav = JSON.parse(jqxhro.responseText);
console.log(nav);		    
		    for(i in nav){
		    	self.children.push(new MenuEntry(nav[i].name, false, nav[i].children));
		    }
	//	    if(typeof(self.children()[0]) !== 'undefined'){
	//	    	self.selectedChild(self.children()[0]);
	//	    	self.children()[0].isSelected(true);
	//	    }
	
		    self.setSelected = function(menu_item, ev){
		    	for(var i in self.children()){
		    		self.children()[i].isSelected(false);
		    	}
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
	    }); */
	};
	
	ko.applyBindings(new ViewModel());