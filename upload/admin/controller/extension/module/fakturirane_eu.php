<?php
/**
 * Клас за връзка с СУПТО Импортер Фактуриране ЕУ чрез API от https://www.fakturirane.eu
 *
 * Функции за автоматично прехвърляне на поръчки в СУПТО Фактуриране ЕУ.
 * Предлага се от фирма "Лиценз" ЕООД чрез уеб сайта на програма "Фактуране ЕУ" - https://fakturirane.eu/
 * @copyright  2019 Licenz Ltd.
 * @version    Release: 1.12
 * @link       https://fakturirane.eu/help/api/
**/

class ControllerExtensionModuleFakturiraneEu extends Controller {
	private $API_USER = '';
	private $API_KEY = '';
	private $DEBUG_MODE = false;
	private $ADD_TO_CATALOG = true;
	private $PRODUCT_CODE_FIELD = 0;
	private $MEASURE_ID = 70;
	private $SOURCE_ID = 1;
	private $PAYMENT_METHOD_ID = 1;
	private $PAYMENT_CODES = array();
	private $VAT_PERCENT = 0;

	private function check_api(){
		$array = $this->model_extension_supto_fakturirane_eu->getSettings();
		$this->API_USER = $array['api_user'];
		$this->API_KEY = $array['api_key'];
		$this->DEBUG_MODE = ($array['debug_mode'] == 1);
		$this->ADD_TO_CATALOG = ($array['add_to_catalog'] == 1);
		$this->PRODUCT_CODE_FIELD = $array['product_code_field'];
		$this->MEASURE_ID = $array['measure_id'];
		$this->SOURCE_ID = $array['source_id'];
		$this->OBJECT_ID = $array['object_id'];
		$this->STATION_ID = $array['station_id'];
		$this->PAYMENT_METHOD_ID = $array['payment_method_id'];

		$this->PAYMENTS_CASH = $array['payments_cash'];
		$this->PAYMENTS_BANK = $array['payments_bank'];
		$this->PAYMENTS_CARD = $array['payments_card'];
		$this->PAYMENTS_COD = $array['payments_cod']; // cash on delivery
		$this->PAYMENTS_MT = $array['payments_mt']; // money transfer
		$this->VAT_PERCENT = $array['vat_percent'];

		$this->PAYMENT_CODES = $this->model_extension_supto_fakturirane_eu->getPaymentCodes();


		return (($this->API_USER != '') and ($this->API_KEY != ''));
	}

	private function display_info($msg){
		$this->session->data['error_message'] = $msg;
		$this->session->data['error_class'] = 'info';
		$this->response->redirect($this->url->link('extension/module/fakturirane_eu', 'user_token=' . $this->session->data['user_token'], true));
	}

	private function display_warning($msg){
		$this->session->data['error_message'] = $msg;
		$this->session->data['error_class'] = 'danger';
		$this->response->redirect($this->url->link('extension/module/fakturirane_eu', 'user_token=' . $this->session->data['user_token'], true));
	}

	private function format_date($date){
		if (preg_match ("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $date, $regs)) {
			if($regs[2] == '00'){
				$date = '';
			}else{
				$date = $regs[3].'.'.$regs[2].'.'.$regs[1];
			}
		}
		return $date;
	}

	public function index() {
			//$this->response->setOutput('test');

		$this->load->language('extension/module/fakturirane_eu');
		$this->load->model("extension/supto/fakturirane_eu");

		$data['op_url'] = $this->url->link('extension/module/fakturirane_eu', 'user_token=' . $this->session->data['user_token'], true);

		//$data['button_export'] = $this->language->get('button_export');
		$data['export_url'] = $this->url->link('extension/module/fakturirane_eu/export', 'user_token=' . $this->session->data['user_token'], true);


		if ($this->request->server['REQUEST_METHOD'] == 'POST')  {
			$this->model_extension_supto_fakturirane_eu->editSetting($this->request->post);


			$data['api_user'] = $this->request->post['api_user'];
			$data['api_key'] = $this->request->post['api_key'];
			$data['debug_mode'] = (isset($this->request->post['debug_mode']) and  ($this->request->post['debug_mode'] == 1))?'checked':'';

			$data['add_to_catalog'] = ($this->request->post['add_to_catalog'] == 1)?'checked':'';
			$data['product_code_field'] = $this->request->post['product_code_field'];
			$data['measure_id'] = $this->request->post['measure_id'];
			$data['source_id'] = $this->request->post['source_id'];
			$data['object_id'] = $this->request->post['object_id'];
			$data['station_id'] = $this->request->post['station_id'];
			$data['payment_method_id'] = $this->request->post['payment_method_id'];

			$data['payments_cash'] = $this->request->post['payments_cash'];
			$data['payments_bank'] = $this->request->post['payments_bank'];
			$data['payments_card'] = $this->request->post['payments_card'];
			$data['payments_cod'] = $this->request->post['payments_cod'];
			$data['payments_mt'] = $this->request->post['payments_mt'];
			$data['vat_percent'] = $this->request->post['vat_percent'];

			$data['payments_codes'] = isset($this->session->data['ss_payments_codes'])?$this->session->data['ss_payments_codes']:'';

			//$data['error_warning'] = $this->language->get('text_success');
			//$this->session->data['success'] = $this->language->get('text_success');
			//$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));

		}elseif ($this->request->server['REQUEST_METHOD'] == 'GET')  { // && $this->validate())
			$check_api = $this->check_api();

			$data['api_user'] = $this->API_USER;
			$data['api_key'] = $this->API_KEY;
			$data['debug_mode'] = ($this->DEBUG_MODE == 1)?'checked':'';
			$data['add_to_catalog'] = ($this->ADD_TO_CATALOG == 1)?'checked':'';
			$data['product_code_field'] = $this->PRODUCT_CODE_FIELD;
			$data['measure_id'] = $this->MEASURE_ID;
			$data['source_id'] = $this->SOURCE_ID;
			$data['object_id'] = $this->OBJECT_ID;
			$data['station_id'] = $this->STATION_ID;
			$data['payment_method_id'] = $this->PAYMENT_METHOD_ID;

			$data['payments_cash'] = $this->PAYMENTS_CASH;
			$data['payments_bank'] = $this->PAYMENTS_BANK;
			$data['payments_card'] = $this->PAYMENTS_CARD;
			$data['payments_cod'] = $this->PAYMENTS_COD;
			$data['payments_mt'] = $this->PAYMENTS_MT;

			$data['vat_percent'] = $this->VAT_PERCENT;

			$pcount = count($this->PAYMENT_CODES);
			if($pcount > 0){
				$codes = array();
				for($i =0; $i < $pcount; $i++){
					$codes[]= $this->PAYMENT_CODES[$i]['code'];
				}
				$data['payments_codes'] = join(', ', $codes);
			}else{
				$data['payments_codes'] = '<не са инсталирани начини на плащане>';
			}

			$this->session->data['ss_payments_codes'] = $data['payments_codes'];

			if(!$check_api){
				$data['result_text'] = $this->language->get('text_license_order').' <a href="https://www.fakturirane.eu/license/?remote=1&api=1&supto=1" target="_blank">https://www.fakturirane.eu/license/</a>';
				$data['result_class'] = 'danger';
			}

			$op = isset($this->request->get['op'])?$this->request->get['op']:0; // 1 - create 5 - delete 10 - preview 20 - download
			if($op == 0){
				$msg = isset($this->session->data['error_message'])?$this->session->data['error_message']:'';
				if($msg != ''){
					$data['result_text'] = $msg;
					$data['result_class'] = isset($this->session->data['error_class'])?$this->session->data['error_class']:'info';
					$this->session->data['error_message'] = '';
					$this->session->data['error_class'] = '';
				}

			}elseif($op == 1){
				if($check_api){
					try{
						include(DIR_SYSTEM.'library/fakturirane/ClassFAPI.php');
						$FAPI = new FAPI($this->API_USER, $this->API_KEY);
						$date = $FAPI->license_expire();
						$this->display_info($this->language->get('text_license_valid_to').$this->format_date($date).' г.');
					}catch (Exception $ex) {
						$this->display_warning($ex->getMessage());
					}
				}
			}elseif($op == 2){
				if($check_api){
					include(DIR_SYSTEM.'library/fakturirane/ClassFAPI.php');
					$order_id = isset($this->request->get['order_id'])?$this->request->get['order_id']:0;
					if($order_id > 0){
						try{
							$FAPI = new FAPI($this->API_USER, $this->API_KEY);

							$order_data = $this->model_extension_supto_fakturirane_eu->getOrder($order_id);
							if($order_data['customer_id'] == 0){
								$eik = '000000000';
							}else{
								$r = $this->model_extension_supto_fakturirane_eu->getCustomerNumber($order_data['customer_id']);
								if(isset($r) and isset($r['tax']) and ($r['tax'] != '')){
									$eik = $r['tax'];
								}else{
									$eik = '000000000';
								}
							}

							$sale = new StdClass();
							$sale->order_id = $order_id;

							if($order_data['payment_code'] == ''){
								$sale->payment_method_id = $this->PAYMENT_METHOD_ID;
							}else{
								if (strpos($this->PAYMENTS_CASH, $order_data['payment_code']) !== false) {
									$sale->payment_method_id = 1;
								}elseif (strpos($this->PAYMENTS_BANK, $order_data['payment_code']) !== false) {
									$sale->payment_method_id = 2;
								}elseif (strpos($this->PAYMENTS_CARD, $order_data['payment_code']) !== false) {
									$sale->payment_method_id = 3;
								}elseif (strpos($this->PAYMENTS_COD, $order_data['payment_code']) !== false) {
									$sale->payment_method_id = 7;
								}elseif (strpos($this->PAYMENTS_MT, $order_data['payment_code']) !== false) {
									$sale->payment_method_id = 8;
								}else{
									$sale->payment_method_id = $this->PAYMENT_METHOD_ID;
								}
							}
							$sale->total = $order_data['total'];

							$sale->source_id = $this->SOURCE_ID;
							$sale->object_id = $this->OBJECT_ID;
							$sale->station_id = $this->STATION_ID;
							$sale->add_to_catalog = $this->ADD_TO_CATALOG;
							$sale->vat_percent = $this->VAT_PERCENT;

							$sale->autoload_measure = 1; 

							$sale->rows = array();

							$products = $this->model_extension_supto_fakturirane_eu->getOrderProducts($order_id);
		
							$products_c = count($products);
							for($i = 0; $i < $products_c; $i++){
								$row = new StdClass();
								$row->name = $products[$i]['name'];
								$row->quantity = $products[$i]['quantity'];
								$row->measure_id = $this->MEASURE_ID; // https://fakturirane.eu/help/api/misc-units-list.php
								$row->discount_percent = 0;
								$row->price = $products[$i]['price'];

								$row->code = '';
								if($this->PRODUCT_CODE_FIELD == 0){
									$row->code = 'S'.$sale->source_id.'-'.$products[$i]['product_id']; 
								}elseif($this->PRODUCT_CODE_FIELD == 1){
									$row->code = 'S'.$sale->source_id.'-'.$products[$i]['model'];
								}else{
									$l = $this->model_extension_supto_fakturirane_eu->getProductCodes($products[$i]['product_id']);
									if($this->PRODUCT_CODE_FIELD == 2){
										$row->code = $l['sku'];
									}elseif($this->PRODUCT_CODE_FIELD == 3){
										$row->code = $l['upc'];
									}elseif($this->PRODUCT_CODE_FIELD == 4){
										$row->code = $l['ean'];
									}elseif($this->PRODUCT_CODE_FIELD == 5){
										$row->code = $l['jan'];
									}elseif($this->PRODUCT_CODE_FIELD == 6){
										$row->code = $l['isbn'];
									}elseif($this->PRODUCT_CODE_FIELD == 7){
										$row->code = $l['mpn'];
									}
								}
								if($row->code == ''){
									$row->code = 'S'.$sale->source_id.'-'.$products[$i]['product_id'];
								}

								$sale->rows[] = $row;
							}

							$new_sale = $FAPI->sale_create($eik, $sale);
							if(isset($new_sale)){
								$this->model_extension_supto_fakturirane_eu->saveSUPTOSaleID($sale->order_id, $new_sale->id);
								if($this->DEBUG_MODE == 1){
									$this->display_info($this->language->get('text_sale_registered'));
								}
							}
						}catch (Exception $ex) {
							if($this->DEBUG_MODE == 1){
								$this->display_warning($ex->getMessage());
							}
						}
					}
				}
			}elseif($op == 3){
				$sale_id = isset($this->request->get['sale_id'])?$this->request->get['sale_id']:0;
				if($sale_id > 0){
					if($check_api){
						try{
							include(DIR_SYSTEM.'library/fakturirane/ClassFAPI.php');
							$FAPI = new FAPI($this->API_USER, $this->API_KEY);
							$status = $FAPI->sale_get_status($sale_id);
							if(isset($status)){
								$this->model_extension_supto_fakturirane_eu->save_sale_status($sale_id, $status->number, $status->anul, $status->completed);
							}else{
								$this->display_info($this->language->get('text_sale_not_completed_message'));
							}
						}catch (Exception $ex) {
							$this->display_warning($ex->getMessage());
						}
					}
				}
			}
		}

		if (isset($this->request->get['filter_order_id'])) {
			$filter_order_id = $this->request->get['filter_order_id'];
		} else {
			$filter_order_id = '';
		}

		if (isset($this->request->get['filter_supto_status'])) {
			$filter_supto_status = $this->request->get['filter_supto_status'];
		} else {
			$filter_supto_status = 0;
		}
		$data['filter_supto_status'] = $filter_supto_status;

		if (isset($this->request->get['filter_customer'])) {
			$filter_customer = $this->request->get['filter_customer'];
		} else {
			$filter_customer = '';
		}

		if (isset($this->request->get['filter_order_status'])) {
			$filter_order_status = $this->request->get['filter_order_status'];
		} else {
			$filter_order_status = '';
		}
		
		if (isset($this->request->get['filter_order_status_id'])) {
			$filter_order_status_id = $this->request->get['filter_order_status_id'];
		} else {
			$filter_order_status_id = '';
		}
		
		if (isset($this->request->get['filter_total'])) {
			$filter_total = $this->request->get['filter_total'];
		} else {
			$filter_total = '';
		}

		if (isset($this->request->get['filter_date_added'])) {
			$filter_date_added = $this->request->get['filter_date_added'];
		} else {
			$filter_date_added = '';
		}

		$filter_data = array(
			'filter_order_id'        => $filter_order_id,
			'filter_customer'	     => $filter_customer,
			'filter_order_status'    => '',
			'filter_order_status_id' => '',
			'filter_supto_status' => $filter_supto_status,
			'filter_total'           => '',
			'filter_date_added'      => $filter_date_added,
			'sort'                   => 'o.order_id',
			'order'                  => 'DESC',
			'start'                  => 0, // ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                  => 10 // $this->config->get('config_limit_admin')
		);

		$results = $this->model_extension_supto_fakturirane_eu->getOrders($filter_data);
		foreach ($results as $result) {
			if($result['supto_sale_id'] == 0){
				$result['supto_sale_id'] = '';
			}

			if(($result['supto_completed'] == 1) or ($result['supto_anul'] == 1)){
				$result['action_status'] = '';
			}elseif($result['supto_sale_id'] == 0){
				$result['action_status'] = '<a href="'.$this->url->link('extension/module/fakturirane_eu', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'].'&op=2', true).'">'.$this->language->get('text_sale_register').'</a>';
			}else{
				$result['action_status'] = '<a href="'.$this->url->link('extension/module/fakturirane_eu', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'].'&op=3&sale_id='.$result['supto_sale_id'], true).'">'.$this->language->get('text_sale_get_status').'</a>';
			}

			if($result['supto_sale_id'] == 0){
				$supto_status = $this->language->get('text_sale_not_imported');
			}elseif($result['supto_completed'] == 1){
				$supto_status = $this->language->get('text_sale_completed');
			}elseif($result['supto_anul'] == 1){
				$supto_status = $this->language->get('text_sale_anul');
			}elseif($result['supto_unp'] != ''){
				$supto_status = $this->language->get('text_sale_process');
			}else{
				$supto_status = $this->language->get('text_sale_not_process');
			}

			$data['orders'][] = array(
				'order_id'      => $result['order_id'],
				'sale_id'      => $result['supto_sale_id'],
				'supto_status' => $supto_status,
				'unp'      => $result['supto_unp'],
				'action_status'      => $result['action_status'],
				'customer'      => $result['customer'],
				'order_status'  => $result['order_status'] ? $result['order_status'] : $this->language->get('text_missing'),
				'total'         => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'op_url'          => $this->url->link('extension/module/fakturirane_eu', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'], true),
			);
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$data['action'] = $this->url->link('extension/module/fakturirane_eu', 'user_token=' . $this->session->data['user_token'], true);
		$data['settings'] = $this->url->link('extension/module/fakturirane_eu', 'user_token=' . $this->session->data['user_token'] . '&settings=1', true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$data['user_token'] = $this->session->data['user_token'];

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/fakturirane_eu', 'user_token=' . $this->session->data['user_token'], true)
		);

		$this->response->setOutput($this->load->view('extension/module/fakturirane_eu', $data));
	
	}


	public function export() {
		
		$this->load->model('catalog/product');
		$returns= array();

		$data = array();

		$results = $this->model_catalog_product->getProducts($data);

		foreach ($results as $result) {

			$special = false;

			$product_specials = $this->model_catalog_product->getProductSpecials($result['product_id']);

			
			foreach ($product_specials  as $product_special) {
				if (($product_special['date_start'] == '0000-00-00' || $product_special['date_start'] < date('Y-m-d')) && ($product_special['date_end'] == '0000-00-00' || $product_special['date_end'] > date('Y-m-d'))) {
					$result['price'] = $product_special['price'];
					break;
				}
			}

			$this->load->model("extension/supto/fakturirane_eu");
			$array = $this->model_extension_supto_fakturirane_eu->getSettings();
			$product_code_field = $array['product_code_field'];
			$default_measure_id = $array['measure_id'];
			$source_id = $array['source_id'];

			if($result['manufacturer_id'] > 0){
				$manufacturer = $this->model_extension_supto_fakturirane_eu->getOrderManufacturer($result['manufacturer_id']);
			}else{
				$manufacturer = '';
			}

			$product_code = '';
			if($product_code_field == 0){
				$product_code = 'S'.$this->SOURCE_ID.'-'.$result['product_id']; 
			}elseif($product_code_field == 1){
				$product_code = 'S'.$source_id.'-'.$result['model'];
			}else{
				$l = $this->model_extension_supto_fakturirane_eu->getProductCodes($result['product_id']);
				if($product_code_field == 2){
					$product_code = $l['sku'];
				}elseif($product_code_field == 3){
					$product_code = $l['upc'];
				}elseif($product_code_field == 4){
					$product_code = $l['ean'];
				}elseif($product_code_field == 5){
					$product_code = $l['jan'];
				}elseif($product_code_field == 6){
					$product_code = $l['isbn'];
				}elseif($product_code_field == 7){
					$product_code = $l['mpn'];
				}
			}

		//	$product_code = $this->PRODUCT_CODE_FIELD;

			$products[] = array(
				'product_id' => $product_code,
				'name' => html_entity_decode($result['name']),
				'price' => $result['price'],
				'price_base' => '0',
				'quantity' => $result['quantity'],
				'measure_id'=> 'бр.',
				'supplier' => $manufacturer,
				'group' => '',
				'notes' => ''
			);
		
			$options = $this->model_catalog_product->getProductOptions($result['product_id']);
			$new_options = array();
			if(isset($options)){
				for($i = 0; $i < count($options); $i++){
					if(($options[$i]['type'] == 'select') or ($options[$i]['type'] == 'checkbox') or ($options[$i]['type'] == 'radio')){
						$new_options[] = $options[$i];
					}
				}
			}
			$count = count($new_options);
			if($count == 1){
				$values = $new_options[0]['product_option_value'];
				for($i = 0; $i < count($values); $i++){
					$option = $this->model_catalog_product->getProductOptionValue($result['product_id'], $values[$i]['product_option_value_id']);
					$option_name = $option['name'];
					$products[] = array(
						'product_id' => $product_code.'-'.$values[$i]['option_value_id'],
						'name' => html_entity_decode($result['name']).' ('.$option_name.')',
						'price' => $result['price'] + $values[$i]['price'],
						'price_base' => '0',
						'quantity' => $values[$i]['quantity'],
						'measure_id'=> 'бр.',
						'supplier' => $manufacturer,
						'group' => '',
						'notes' => ''
					);
				}
			}elseif($count == 2){
				$values1 = $new_options[0]['product_option_value'];
				$values2 = $new_options[1]['product_option_value'];

				for($i = 0; $i < count($values1); $i++){
					$option1 = $this->model_catalog_product->getProductOptionValue($result['product_id'], $values1[$i]['product_option_value_id']);
					$option_name1 = $option1['name'];

					for($j = 0; $j < count($values2); $j++){
						$option2 = $this->model_catalog_product->getProductOptionValue($result['product_id'], $values2[$j]['product_option_value_id']);
						$option_name2 = $option2['name'];
						$products[] = array(
							'product_id' => $product_code.'-'.$values1[$i]['option_value_id'].'-'.$values2[$j]['option_value_id'],
							'name' => html_entity_decode($result['name']).' ('.$option_name1.', '.$option_name2.')',
							'price' => $result['price'] + $values1[$i]['price'] + $values2[$j]['price'],
							'price_base' => '0',
							'quantity' => '-',
							'measure_id'=> 'бр.',
							'supplier' => $manufacturer,
							'group' => '',
							'notes' => ''
						);
					}

				}

			}elseif($count == 3){
				$values1 = $new_options[0]['product_option_value'];
				$values2 = $new_options[1]['product_option_value'];
				$values3 = $new_options[2]['product_option_value'];

				for($i = 0; $i < count($values1); $i++){
					$option1 = $this->model_catalog_product->getProductOptionValue($result['product_id'], $values1[$i]['product_option_value_id']);
					$option_name1 = $option1['name'];

					for($j = 0; $j < count($values2); $j++){
						$option2 = $this->model_catalog_product->getProductOptionValue($result['product_id'], $values2[$j]['product_option_value_id']);
						$option_name2 = $option2['name'];

						for($k = 0; $k < count($values3); $k++){
							$option3 = $this->model_catalog_product->getProductOptionValue($result['product_id'], $values3[$k]['product_option_value_id']);
							$option_name3 = $option3['name'];
							$products[] = array(
								'product_id' => $product_code.'-'.$values1[$i]['option_value_id'].'-'.$values2[$j]['option_value_id'].'-'.$values3[$k]['option_value_id'],
								'name' => html_entity_decode($result['name']).' ('.$option_name1.', '.$option_name2.', '.$option_name3.')',
								'price' => $result['price'] + $values1[$i]['price'] + $values2[$j]['price'] + $values3[$k]['price'],
								'price_base' => '0',
								'quantity' => '-',
								'measure_id'=> 'бр.',
								'supplier' => $manufacturer,
								'group' => '',
								'notes' => ''
							);
						}


					}

				}

			}

				//SELECT option_id FROM oc_product_option WHERE  product_id = $product_id ORDER BY option_id
				//SELECT option_value_id, quantity, price FROM oc_product_option_value WHERE (product_option_id = $o_id) AND (product_id = $p_id)


		}

		$products_data = array(array('Арт. номер', 'Наименование', 'Ед. цена без ДДС', 'Дост. цена без ДДС', 'Количество', 'Мярка', 'Доставчик', 'Група', 'Бележки'));

		foreach($products as $products_row){
			$products_data[]= $products_row;
		}

		require_once(DIR_SYSTEM . 'library/fakturirane/PHPExcel.php');
		$filename='product_list_'.date('Y-m-d _ H:i:s');
		$filename = preg_replace('/[^aA-zZ0-9\_\-]/', '', $filename);
		// Create new PHPExcel object

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getActiveSheet()->fromArray($products_data, null, 'A1');
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);

		// Save Excel 95 file

		$callStartTime = microtime(true);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		
		//Setting the header type
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition: attachment; filename=\"" . $filename . ".xls\"");
		
		header('Cache-Control: max-age=0');

		$objWriter->save('php://output');
	}

	public function install() {
		$this->load->model("extension/supto/fakturirane_eu");
		$this->model_extension_supto_fakturirane_eu->createSchema();
	}
	public function uninstall() {
		$this->load->model("extension/supto/fakturirane_eu");
		$this->model_extension_supto_fakturirane_eu->deleteSchema();
	}
}
?>