<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pagemenu class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */
 
/*
 * Pagemenu2 for CIBoard - Smilena (https://github.com/damulhan)  
 * @author Smilena <damulhan@gmail.com>
 */

/**
 * 관리자>페이지설정>메뉴관리 controller 입니다.
 */
class Pagemenu2 extends CB_Controller
{

	/**
	 * 관리자 페이지 상의 현재 디렉토리입니다
	 * 페이지 이동시 필요한 정보입니다
	 */
	public $pagedir = 'page/pagemenu2';

	/**
	 * 모델을 로딩합니다
	 */
	protected $models = array('Menu');

	/**
	 * 이 컨트롤러의 메인 모델 이름입니다
	 */
	protected $modelname = 'Menu_model';

	private $error_count = 0;
	private $error_mesg = '';
	
	/**
	 * 헬퍼를 로딩합니다
	 */
	protected $helpers = array('form', 'array');

	function __construct()
	{
		parent::__construct();

		/**
		 * 라이브러리를 로딩합니다
		 */
		$this->load->library(array('querystring'));

		/**
		 * Validation 라이브러리를 가져옵니다
		 */
		$this->load->library('form_validation');
		
	}

	/**
	 * 목록을 가져오는 메소드입니다
	 */
	public function index()
	{
		// 이벤트 라이브러리를 로딩합니다
		// ==> 기존 Pagemenu와 호환을 위해 같은 이름으로 설정 
		$eventname = 'event_admin_page_pagemenu_index';
		$this->load->event($eventname);

		$view = array();
		$view['view'] = array();

		// 이벤트가 존재하면 실행합니다
		$view['view']['event']['before'] = Events::trigger('before', $eventname);


		/**
		 * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
		 */
		$param =& $this->querystring;

		/**
		 * 게시판 목록에 필요한 정보를 가져옵니다.
		 */
		$this->{$this->modelname}->allow_order_field = array('men_order'); // 정렬이 가능한 필드
		$where = array('men_parent' => 0);
		$result = $this->{$this->modelname}
			->get_admin_list($per_page = 1000, '', $where, '', $findex = 'men_order', $forder = 'ASC', '', '');
		
		if (element('list', $result)) {
			foreach (element('list', $result) as $key => $val) {
				$subwhere = array('men_parent' => element('men_id', $val));
				$subresult = $this->{$this->modelname}
					->get_admin_list($per_page = 1000, '', $subwhere, '', $findex = 'men_order', $forder = 'ASC', '', '');
				$result['list'][$key]['subresult'] = $subresult;
			}
		}
		
		$view['view']['data'] = $result;

		/**
		 * primary key 정보를 저장합니다
		 */
		$view['view']['primary_key'] = $this->{$this->modelname}->primary_key;

		/**
		 * 쓰기 주소, 삭제 주소등 필요한 주소를 구합니다
		 */
		$view['view']['list_update_url'] = admin_url($this->pagedir . '/listupdate/?' . $param->output());
		$view['view']['list_delete_url'] = admin_url($this->pagedir . '/listdelete/?' . $param->output());

		// 이벤트가 존재하면 실행합니다
		$view['view']['event']['before_layout'] = Events::trigger('before_layout', $eventname);

		/**
		 * 어드민 레이아웃을 정의합니다
		 */
		$layoutconfig = array('layout' => 'layout', 'skin' => 'index');
		$view['layout'] = $this->managelayout->admin($layoutconfig, $this->cbconfig->get_device_view_type());
		$this->data = $view;
		$this->layout = element('layout_skin_file', element('layout', $view));
		$this->view = element('view_skin_file', element('layout', $view));
	}
	
	public function _getmenus($parent_id = 0) {
		
		$this->{$this->modelname}->allow_order_field = array('men_order'); 
		$where = array('men_parent' => $parent_id);
		$r = $this->{$this->modelname}
			->get_admin_list($per_page = 1000, '', $where, '', $findex = 'men_order', $forder = 'ASC', '', '');

		$result = array();
			
		if($r['total_rows'] > 0) {
			
			foreach($r['list'] as $i) {
				$item = array();
				$item['deleted']  = '0';
				$item['new']      = '0';
				$item['id']       = $i['men_id'];
				$item['name']     = $i['men_name'];
				$item['link']     = $i['men_link'];
				$item['target']   = $i['men_target'];
				$item['desktop']  = $i['men_desktop'];
				$item['mobile']   = $i['men_mobile'];
				$item['custom']   = $i['men_custom'];
				$item['order']    = $i['men_order'];
				$item['children'] = $this->_getmenus($i['men_id']);
				$result[] = $item; 
			}
		}
		
		return $result;
	}
	
	public function _getmenus_json() {
		
		$result = $this->_getmenus(0);
		
		if($this->error_count > 0) {
			$result_json['result'] = 'error';
			$result_json['error_count'] = $this->error_count;
			$result_json['mesg'] = $this->error_mesg;
		} else {
			$result_json['result'] = 'success';
			$result_json['mesg'] = '';
			$result_json['menudata'] = $result; 			
		}
		
		return json_encode($result_json, JSON_UNESCAPED_UNICODE);
	}
	
	public function getmenus_json() {
		echo $this->_getmenus_json();
	}
	
	public function _validate_vars($updatedata) {
		
		$this->form_validation->reset_validation();
		
		$this->form_validation->set_data(array(
			'men_parent' => $updatedata['men_parent'],
			'men_name' => $updatedata['men_name'],
			'men_link' => $updatedata['men_link'],
			'men_target' => $updatedata['men_target'],
			'men_custom' => $updatedata['men_custom'],
			'men_order' => $updatedata['men_order'],
			'men_desktop' => $updatedata['men_desktop'],
			'men_mobile' => $updatedata['men_mobile'],
		));

		/**
		 * 전송된 데이터의 유효성을 체크합니다
		 */
		$config = array(
			array(
				'field' => 'men_name',
				'label' => '메뉴명',
				'rules' => 'trim|required',
			),
			array(
				'field' => 'men_parent',
				'label' => '메뉴위치',
				'rules' => 'trim|numeric|is_natural',
			),
			array(
				'field' => 'men_target',
				'label' => '새창여부',
				'rules' => 'trim',
			),
			array(
				'field' => 'men_custom',
				'label' => '커스텀',
				'rules' => 'trim',
			),
			array(
				'field' => 'men_order',
				'label' => '순서',
				'rules' => 'trim|numeric|is_natural',
			),
			array(
				'field' => 'men_desktop',
				'label' => 'PC사용',
				'rules' => 'trim',
			),
			array(
				'field' => 'men_mobile',
				'label' => '모바일사용',
				'rules' => 'trim',
			),
			array(
				'field' => 'men_link',
				'label' => '링크주소',
				'rules' => 'trim|required|prep_url|valid_url',
			),
		);

		$this->form_validation->set_rules($config);

		return $this->form_validation->run();
		
	}
	
	public function _listupdate_menu($menu, $parent_id=0, $order=0) {
		
		$men_name = $menu->name;
		$men_link = $menu->link;
		$men_target = $menu->target;
		$men_custom = $menu->custom;
		$men_desktop = $menu->desktop;
		$men_mobile = $menu->mobile;
		
		$updatedata = array(
			'men_name' => $men_name,
			'men_link' => $men_link,
			'men_target' => $men_target,
			'men_custom' => $men_custom,
			'men_order' => $order,
			'men_desktop' => $men_desktop,
			'men_mobile' => $men_mobile,
		);
		
		if($this->_validate_vars($updatedata) !== false) { # 인증 실패. 
			if($menu->new) {
				$updatedata['men_parent'] = $parent_id;
				$this->{$this->modelname}->insert($updatedata);
				
			} else if($menu->deleted) {
				$this->{$this->modelname}->delete($menu->id);

			} else { # if($menu->updated) { 
				# note: updated check 항목만 변경하려 하였으나, 
				#       화면에 반영된 order 순서를 그대로 반영하기 위하여 
				#       전체 항목을 변경 적용함.
				$updatedata['men_parent'] = $parent_id;
				$this->{$this->modelname}->update($menu->id, $updatedata);
			}
			
		} else {
			$this->error_count += 1;
			$msg = strip_tags(print_r(validation_errors(), true));
			$this->error_mesg .= "- ${men_name}: ${msg}\n";
		}
		
		if($menu->children) {
			$_parent_id = $menu->id;
			$_order = 0;
			foreach($menu->children as $chmenu) {
				$this->_listupdate_menu($chmenu, $_parent_id, $_order);
				$_order++;
			}
		}
		
	}
	
	public function listupdate_ajax() {
		
		$menudata_json = $this->input->post('menudata');
		
		$menudata = json_decode($menudata_json);
		
		$this->error_count = 0;
				
		if (is_array($menudata)) {
			$_order = 0;
			foreach ($menudata as $menu) {
				$this->_listupdate_menu($menu, 0, $_order);
				$_order++;
			}
		}
		
		$this->_delete_cache();
		
		$result_json = array();
		if($this->error_count > 0) {
			$result_json['result'] = 'error';
			$result_json['error_count'] = $this->error_count;
			$result_json['mesg'] = $this->error_mesg;
		} else {
			$result_json['result'] = 'success';
			$result_json['mesg'] = '';
		}
		
		echo json_encode($result_json, JSON_UNESCAPED_UNICODE);
	}
	
	/**
	 * 메뉴관련 캐시를 삭제합니다
	 */
	public function _delete_cache()
	{
		$this->cache->delete('pagemenu-mobile');
		$this->cache->delete('pagemenu-desktop');
	}
}
