<?php 
/*
 * Pagemenu2 for CIBoard - Smilena (https://github.com/damulhan)  
 * @author Smilena <damulhan@gmail.com>
 */
?>
<?php $viewbase = '/views/admin/basic/page/pagemenu2/'; ?>

<script>
	var csrf_token_name = '<?php echo $this->security->get_csrf_token_name() ?>';
</script>

<!--
<style>
.content_wrapper { z-index:initial; }
</style>
-->

<div class="container" style="">

	<div class="row">

		<div class="col-md-5 col-sm-5">
		
			<button type="button" class="btn btn-info  " onclick="menu_save()">메뉴 저장</button>
			
			<div class="mb20"></div>

			<div class="dd nestable">
				<ol class="dd-list" id="menus">
				</ol>
			</div>
			
			<div class="mb30"></div>
			
		</div>
		
				
		<div class="col-md-5 col-sm-5">

			<form class="" id="menu-add">
				<h4>메뉴 추가</h4>
				<br>
				<div class="form-group">
					<label for="addInputName">이름</label>
					<input type="text" class="form-control" id="addInputName" placeholder="" required>
				</div>
				<div class="form-group">
					<label for="addInputLink">주소</label>
					<input type="text" class="form-control" id="addInputLink" placeholder="" required>
				</div>
				<div class="form-group">
					<label for="addInputCustom">커스텀(a 태그안)</label>
					<input type="text" class="form-control" id="addInputCustom" placeholder="">
				</div>				
				<div class="form-group">
					<label for="editInputTarget">새창</label>
					<select class="form-control" id="addInputTarget">
						<option value="">현재창</option>
						<option value="_blank">새창</option>
					</select>
				</div>
				<div class="form-group checkbox">
					<label>
						<input type="checkbox" id="addInputDesktop" value="1" checked> PC사용
					</label>
				</div>
				<div class="form-group checkbox">
					<label>
						<input type="checkbox" id="addInputMobile" value="1" checked> 모바일사용
					</label>
				</div>
				<button class="btn btn-info" id="addButton">추가</button>
			</form>

			<form class="" id="menu-editor" style="display: none;">
				<h4>메뉴 수정: <span id="currentEditName"></span></h4>
				<br>
				<div class="form-group">
					<label for="editInputName">이름</label>
					<input type="text" class="form-control" id="editInputName" placeholder="" required>
				</div>
				<div class="form-group">
					<label for="editInputLink">주소</label>
					<input type="text" class="form-control" id="editInputLink" placeholder="" required>
				</div>
				<div class="form-group">
					<label for="editInputCustom">커스텀(a 태그안)	</label>
					<input type="text" class="form-control" id="editInputCustom" placeholder="">
				</div>				
				<div class="form-group">
					<label for="editInputTarget">새창</label>
					<select class="form-control" id="editInputTarget">
						<option value="">현재창</option>
						<option value="_blank">새창</option>
					</select>
				</div>
				<div class="form-group checkbox">
					<label>
						<input type="checkbox" id="editInputDesktop" value="1"> PC사용
					</label>
				</div>
				<div class="form-group checkbox">
					<label>
						<input type="checkbox" id="editInputMobile" value="1"> 모바일사용
					</label>
				</div>
				<button class="btn btn-info" id="editButton">저장</button> 
				<button class="btn btn-default" id="editButtonCancel">취소</button>
			</form>

			<div class="mb30"></div>
			<div class="mb30"></div>

		</div>

		<!-- hidden -->
		<div class="row output-container" style="display: none;">
			<div class="col-md-12">
				<form class="form">
					<textarea class="form-control" id="json-output" rows="5"></textarea>
				</form>
			</div>
		</div>

		<script type="text/template" id="menu-tmpl">
			<li class="dd-item" data-id="<%= menu.id %>" data-name="<%= _.escape(menu.name) %>" data-link="<%= _.escape(menu.link) %>" 
				data-custom="<%= _.escape(menu.custom) %>" data-target="<%= _.escape(menu.target) %>" data-desktop="<%= menu.desktop %>" 
				data-mobile="<%= menu.mobile %>" 
				data-new="<%= menu.new %>" data-deleted="<%= menu.deleted %>" data-updated="<%= menu.updated %>"
			>
				<% if(typeof menu.children !== 'undefined' && menu.children.length > 0) { %>
				<button data-action="collapse" type="button" style="display: none; "><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">줄임</font></font></button>
				<button data-action="expand" type="button" style="display: block;"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">펼침</font></font></button>
				<% } %>
				<div class="dd-handle"><span class="name"><%= menu.name %></span><span class="icons pull-right">
					<i class="icon-desktop fa fa-desktop" style="display:<%= (menu.desktop=="1")?"inline-block":"none"%>"></i>
					<i class="icon-mobile fa fa-mobile" style="display:<%= (menu.mobile=="1")?"inline-block":"none"%>"></i></span></div>
				<span class="button-delete btn btn-default btn-xs pull-right" data-owner-id="<%= menu.id %>">
					<i class="fa fa-times-circle-o" aria-hidden="true"></i></span>
				<span class="button-edit btn btn-default btn-xs pull-right" data-owner-id="<%= menu.id %>">
					<i class="fa fa-pencil" aria-hidden="true"></i></span>
				
				<% if(typeof menu.children !== 'undefined' && menu.children.length > 0) { %>
					<ol class="dd-list" style="display: none; ">
					<% _.each(menu.children, function(child) { %>
						<%= templateFn({"menu": child, "templateFn": templateFn}) %>
					<% }); %>
					</ol>
				<% } %>
			</li>
		</script>
		
		<div class="mb30"></div>
		<div class="mb30"></div>
	</div>
	
	<div class="row">
		<div class="col-md-5 col-sm-5 "></div>
		<div class="col-md-5 col-sm-5 ">
			Pagemenu2 for CIBoard - Smilena ( <a href="https://github.com/damulhan" target="_blank">https://github.com/damulhan</a> ) 
		</div>
	</div>
	
</div>

<link rel="stylesheet" type="text/css" href="<?php echo base_url($viewbase.'css/nestable.css'); ?>" />
<script type="text/javascript" src="<?php echo base_url($viewbase.'js/underscore.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url($viewbase.'js/backbone.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url($viewbase.'js/jquery.nestable.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url($viewbase.'js/jquery.nestable++.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url($viewbase.'js/index.js'); ?>"></script>

