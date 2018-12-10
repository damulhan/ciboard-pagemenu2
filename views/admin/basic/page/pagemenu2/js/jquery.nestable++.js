/* 
Original source from: 
- Drag & Drop Menu Editor (Wordpress-like) jQuery plugin
https://github.com/FrancescoBorzi/Nestable.git
*/

/*************** General ***************/

var updateOutput = function (e) {
	
	var list = e.length ? e : $(e.target),
	output = list.data('output');
	
	if (window.JSON) {
		if (output) {
			output.val(window.JSON.stringify(list.nestable('serialize')));
		}
	} else {
		alert('JSON browser support required for this page.');
	}
	
};

var nestableList = $(".dd.nestable > .dd-list");

/***************************************/


/*************** Delete ***************/

var deleteFromMenuHelper = function (target) {
	if (target.data('new') == 1) {
		// if it's not yet saved in the database, just remove it from DOM
		target.fadeOut(function () {
			target.remove();
			updateOutput($('.dd.nestable').data('output', $('#json-output')));
		});
		
	} else {
		// otherwise hide and mark it for deletion
		target.appendTo(nestableList); // if children, move to the top level
		target.data('deleted', '1');
		target.fadeOut();
	}
};

var deleteFromMenu = function () {
	var targetId = $(this).data('owner-id');
	var target = $('[data-id="' + targetId + '"]');

	var result = confirm("댜음 항목과 하위항목을 모두 삭제하겠습니까? : " + target.data('name'));
	if (!result) {
		return;
	}

	// Remove children (if any)
	target.find("li").each(function () {
		deleteFromMenuHelper($(this));
	});

	// Remove parent
	deleteFromMenuHelper(target);

	// update JSON
	netstableUpdateJSONOutput();
};

/***************************************/


/*************** Edit ***************/

var menuEditor = $("#menu-editor");
var menuAdd = $("#menu-add");
var currentEditName = $("#currentEditName");
var editButton = $("#editButton");
var editButtonCancel = $("#editButtonCancel");

var editInputName = $("#editInputName");
var editInputLink = $("#editInputLink");
var editInputTarget = $("#editInputTarget");
var editInputCustom = $("#editInputCustom");
var editInputDesktop = $("#editInputDesktop");
var editInputMobile = $("#editInputMobile");

// Prepares and shows the Edit Form
var prepareEdit = function () {
	var targetId = $(this).data('owner-id');
	var target = $('[data-id="' + targetId + '"]');

	editInputName.val(target.data("name"));
	editInputLink.val(target.data("link"));
	editInputTarget.val(target.data("target"));
	editInputCustom.val(target.data("custom"));
	editInputDesktop.prop('checked', target.data("desktop") == '1');
	editInputMobile.prop('checked', target.data("mobile") == '1');
	
	currentEditName.html(target.data("name"));
	editButton.data("owner-id", target.data("id"));

	console.log("[INFO] Editing Menu Item " + editButton.data("owner-id"));

	menuAdd.css('display', 'none');
	menuEditor.fadeIn();
};

// Edits the Menu item and hides the Edit Form
var editMenuItem = function () {
	var targetId = $(this).data('owner-id');
	var target = $('[data-id="' + targetId + '"]');
	
	var newName = editInputName.val();
	var newLink = editInputLink.val();
	var newTarget = editInputTarget.val();
	var newCustom = editInputCustom.val();
	var newDesktop = editInputDesktop.prop('checked') ? '1' : '0'; 
	var newMobile = editInputMobile.prop('checked') ? '1' : '0'; 
	
	if(newName == '') {
		alert('이름 항목이 비어 있습니다.');
		editInputName.focus();
		return;
	}
	
	if(newLink == '') {
		alert('주소 항목이 비어 있습니다.');
		editInputLink.focus();
		return;
	}
	
	console.log(target);
	
	target.data("name", newName);
	target.data("link", newLink);
	target.data("target", newTarget);
	target.data("custom", newCustom);
	target.data("desktop", newDesktop);
	target.data("mobile", newMobile);
	
	// check as updated 
	target.data("updated", "1"); 
	
	target.find("> .dd-handle > .name").html(newName);
	target.find("> .dd-handle .icon-desktop").css('display', (newDesktop == "1") ? 'inline-block' : 'none');
	target.find("> .dd-handle .icon-mobile").css('display', (newMobile == "1") ? 'inline-block' : 'none');
	
	netstableUpdateJSONOutput();
	
	menuEditor.css('display', 'none');
	menuAdd.fadeIn();
};

var editCancelMenuItem = function() {
	menuEditor.css('display', 'none');
	menuAdd.fadeIn();
}

/***************************************/


/*************** Add ***************/

var newIdCount = 1;

var addToMenu = function () {
	
	var newName = $("#addInputName").val();
	var newLink = $("#addInputLink").val();
	var newCustom = $("#addInputCustom").val();
	var newTarget = $("#addInputTarget").val();
	var newDesktop = $("#addInputDesktop").prop('checked') ? '1' : '0'; 
	var newMobile = $("#addInputMobile").prop('checked') ? '1' : '0'; 
	
	var newId = 'new-' + newIdCount;
	
	nestableList.append(
		'<li class="dd-item" ' +
				'data-id="' + newId + '" ' +
				'data-name="' + _.escape(newName) + '" ' +
				'data-link="' + _.escape(newLink) + '" ' +
				'data-custom="' + _.escape(newCustom) + '" ' +
				'data-target="' + _.escape(newTarget) + '" ' +
				'data-desktop="' + newDesktop + '" ' +
				'data-mobile="' + newMobile + '" ' +
				'data-order="0" ' +
				'data-new="1" ' +
				'data-deleted="0" ' + 
				'data-updated="0">' +
			'<div class="dd-handle"><span class="name">' + newName + '</span><span class="icons pull-right">' + 
				'<i class="icon-desktop fa fa-desktop" style="display:' + (newDesktop=="1"?"inline-block":"none") + '"></i> ' +
				'<i class="icon-mobile fa fa-mobile" style="display:' + (newMobile=="1"?"inline-block":"none") + '"></i>' +
				'</span></div> ' +
			'<span class="button-delete btn btn-default btn-xs pull-right" ' +
				'data-owner-id="' + newId + '"> ' +
				'<i class="fa fa-times-circle-o" aria-hidden="true"></i> ' +
			'</span>' +
			'<span class="button-edit btn btn-default btn-xs pull-right" ' +
				'data-owner-id="' + newId + '">' +
				'<i class="fa fa-pencil" aria-hidden="true"></i>' +
			'</span>' +
		'</li>'
	);

	newIdCount++;

	netstableUpdateJSONOutput();
	netstableSetEvents();
	
	addToMenuReset();
};

var addToMenuReset = function() {
	$("#addInputName").val('');
	$("#addInputLink").val('');
	$("#addInputCustom").val('');
	$("#addInputTarget").val('');
	$("#addInputDesktop").prop('checked', true);
	$("#addInputMobile").prop('checked', true);
};

var netstableUpdateJSONOutput = function() {
	// update JSON
	updateOutput($('.dd.nestable').data('output', $('#json-output')));
};

var netstableSetEvents = function() {
	// set events
	$(".dd.nestable .button-delete").on("click", deleteFromMenu);
	$(".dd.nestable .button-edit").on("click", prepareEdit);
};

/***************************************/

$(function () {

	// output initial serialised data
	updateOutput($('.dd.nestable').data('output', $('#json-output')));

	// set onclick events
	editButton.on("click", editMenuItem);
	editButtonCancel.on("click", editCancelMenuItem);

	$(".dd.nestable .button-delete").on("click", deleteFromMenu);

	$(".dd.nestable .button-edit").on("click", prepareEdit);

	$("#menu-editor").submit(function (e) {
		e.preventDefault();
	});

	$("#menu-add").submit(function (e) {
		e.preventDefault();
		addToMenu();
	});

});

