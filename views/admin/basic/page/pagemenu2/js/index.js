/*
 * Pagemenu2 for CIBoard - Smilena (https://github.com/damulhan)  
 * @author Smilena <damulhan@gmail.com>
 */

/* model */

var Menu = Backbone.Model.extend({
	defaults: {
		"id": "",
		"new": "1",
		"deleted": "0",
		"updated": "0",
		"name":"",
		"link": "",
		"target": "",
		"custom": "",
		"desktop": "",
		"mobile": "",
		"order": "",
	},
});

var Menus = Backbone.Collection.extend({
	
	model: Menu,
	
	load: function(menu_arr) {
		var that = this;
		this.reset();		
		_.each(menu_arr, function(item) {
			var menu = new Menu(item);
			that.push(menu);
		});
	},
});


/* view */

var MenusView = Backbone.View.extend({
	
	el: $("#menus"),
	
	initialize: function(col) {        
		this.collection = col;
	},
	
    template: _.template($("#menu-tmpl").html()),
	
	renderItem: function(menu) {
		return this.template({
            "menu": menu, 
            "templateFn": this.template
        });
    }, 
	
	render: function() {
		var that = this;
		var outs = '';
		_.each(this.collection.toJSON(), function(menu) {
			outs += that.renderItem(menu);
		}, this);
        this.$el.html(outs);
	},
	
});


/* menu load / save */

function menu_save() {
	
	if (!confirm('저장하겠습니까?')) {
		return;
	}
	
	console.log("Menu Save started...");

	netstableUpdateJSONOutput();
	
	var menudata = $('#json-output').val();
	
	var data = { menudata:menudata };
	
	data[csrf_token_name] = cb_csrf_hash;
	
	$.ajax({
		method: 'post',
		url: "/admin/page/pagemenu2/listupdate_ajax",
		data: data,
		success: function(data) {
			var json = JSON.parse(data);

			if(json['result'] !== 'success') {
				alert('Error: ' + json['mesg']);
				return;
				
			} else {
				menu_load();
				
				alert('저장되었습니다.');
			}
		},
		error: function(result) {
			alert(result);
		}
	});
}

function menu_load() {
	
	console.log("Menu Loading");
	
	var data = { };
	data[csrf_token_name] = cb_csrf_hash;
		
	$.ajax({
		method: 'get',
		url: "/admin/page/pagemenu2/getmenus_json",
		data: data,
		success: function(data) {
			
			var json = JSON.parse(data);
						
			if(json['result'] !== 'success') {
				alert('Error: ' + json['mesg']);
				return;
			}
			
			var menudata = json['menudata'];			
			menu_render(menudata);
			
		},
		error: function(result) {
			alert(result);
		}
	});	
}

function menu_render(menudata) {
	
	console.log("Menu Render");

	var menus = new Menus();
	menus.load(menudata);

	var menusView = new MenusView(menus); 
	menusView.render();	
	
	netstableSetEvents();
}

/* initialize */

$('document').ready(function() {
	
	// initialize nestable 
	$('.dd.nestable').nestable({ 
		maxDepth: 5
	}).on('change', updateOutput);

	// load menus from server 
	menu_load();
});

