<?php
class ModelExtensionSuptoFakturiraneEu extends Model {
	public function getSettings() {
		$query = $this->db->query("SELECT api_source, api_user, api_key, object_id, station_id, debug_mode, add_to_catalog, product_code_field, measure_id, source_id, payment_method_id, payments_cash, payments_bank, payments_card, payments_cod, payments_mt, vat_percent FROM `" . DB_PREFIX . "fakturirane_eu` LIMIT 1");
		if ($query->row) {
			return $query->row; // json_decode(, true);
		}else{
			return array('api_source'=>1, 'api_user'=>'', 'api_key'=>'', 'object_id'=>0, 'station_id'=>0, 'debug_mode'=>0, 'add_to_catalog'=>1, 'product_code_field'=>0, 'source_id'=>1, 'payment_method_id'=>1, 'payments_cash'=>'', 'payments_bank'=>'', 'payments_card'=>'', 'payments_cod'=>'', 'payments_mt'=>'', 'measure_id'=>70 /* в брой */, 'vat_percent'=>0);
		}
	}

	public function getPaymentCodes() {
		$query = $this->db->query("SELECT code FROM " . DB_PREFIX . "extension WHERE type = 'payment' ORDER BY code");
		return $query->rows;
	}

	public function getProductCodes($product_id) {
		$query = $this->db->query("SELECT sku, upc, ean, jan, isbn, mpn FROM " . DB_PREFIX . "product WHERE product_id = $product_id");
		return $query->row;
	}

	public function getCustomerNumber($customer_id) {
		$query = $this->db->query("SELECT tax FROM " . DB_PREFIX . "customer_affiliate WHERE customer_id = $customer_id");
		return $query->row;
	}



	public function editSetting($post) {
		$api_source = isset($post['api_source'])?$post['api_source']:1;
		$api_user = isset($post['api_user'])?$post['api_user']:'';
		$api_key = isset($post['api_key'])?$post['api_key']:'';
		$debug_mode = isset($post['debug_mode'])?$post['debug_mode']:0;
		$add_to_catalog = isset($post['add_to_catalog'])?$post['add_to_catalog']:0;
		$product_code_field = isset($post['product_code_field'])?$post['product_code_field']:0;
		$measure_id = isset($post['measure_id'])?$post['measure_id']:0;
		$source_id = isset($post['source_id'])?$post['source_id']:0;
		$object_id = isset($post['object_id'])?$post['object_id']:0;
		$station_id = isset($post['station_id'])?$post['station_id']:0;

		$payment_method_id = isset($post['payment_method_id'])?$post['payment_method_id']:1;

		$payments_cash = isset($post['payments_cash'])?$post['payments_cash']:'';
		$payments_bank = isset($post['payments_bank'])?$post['payments_bank']:'';
		$payments_card = isset($post['payments_card'])?$post['payments_card']:'';
		$payments_cod = isset($post['payments_cod'])?$post['payments_cod']:'';
		$payments_mt = isset($post['payments_mt'])?$post['payments_mt']:'';
		$vat_percent = isset($post['vat_percent'])?$post['vat_percent']:0;

		$payments_cash = str_replace(' ', '', $payments_cash);
		$payments_bank = str_replace(' ', '', $payments_bank);
		$payments_card = str_replace(' ', '', $payments_card);
		$payments_cod = str_replace(' ', '', $payments_cod);
		$payments_mt = str_replace(' ', '', $payments_mt);

		$api_user = $this->db->escape($api_user);
		$api_user = $this->db->escape($api_user);
		$api_key = $this->db->escape($api_key);

		$payments_cash = $this->db->escape($payments_cash);
		$payments_bank = $this->db->escape($payments_bank);
		$payments_card = $this->db->escape($payments_card);
		$payments_cod = $this->db->escape($payments_cod);
		$payments_mt = $this->db->escape($payments_mt);

		$this->db->query("UPDATE " . DB_PREFIX . "fakturirane_eu SET api_source = $api_source, api_user = '$api_user', api_key = '$api_key', debug_mode = $debug_mode, add_to_catalog = $add_to_catalog, object_id = $object_id, station_id = $station_id, product_code_field = $product_code_field, source_id = $source_id, payment_method_id = $payment_method_id, payments_cash = '$payments_cash', payments_bank = '$payments_bank', payments_card = '$payments_card', payments_cod = '$payments_cod', payments_mt = '$payments_mt', measure_id = $measure_id, vat_percent = $vat_percent");
	}

	public function saveSUPTOSaleID($order_id, $supto_sale_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "order SET supto_sale_id = $supto_sale_id WHERE order_id = $order_id");
	}

	public function save_sale_status($sale_id, $number, $anul, $completed) {
		$this->db->query("UPDATE " . DB_PREFIX . "order SET supto_unp = '$number', supto_anul = $anul, supto_completed = $completed WHERE  supto_sale_id = $sale_id");
	}


	public function getOrders($data = array()) {
		$sql = "SELECT o.order_id, CONCAT(o.firstname, ' ', o.lastname) AS customer, (SELECT os.name FROM " . DB_PREFIX . "order_status os WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') AS order_status, o.shipping_code, o.total, o.currency_code, o.currency_value, o.date_added, o.supto_sale_id, o.supto_unp, o.supto_anul, o.supto_completed FROM `" . DB_PREFIX . "order` o";

		if (!empty($data['filter_order_status'])) {
			$implode = array();

			$order_statuses = explode(',', $data['filter_order_status']);

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "o.order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " WHERE (" . implode(" OR ", $implode) . ")";
			}
		} elseif (isset($data['filter_order_status_id']) && $data['filter_order_status_id'] !== '') {
			$sql .= " WHERE o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE o.order_status_id > '0'";
		}

		if (!empty($data['filter_order_id'])) {
			$sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
		}

		if ($data['filter_supto_status'] != 0) {
			if ($data['filter_supto_status'] == 1) { // Неимпортирана
				$sql .= " AND o.supto_sale_id = 0";
			}elseif ($data['filter_supto_status'] == 2) { // Приключени
				$sql .= " AND o.supto_completed = 1";
			}elseif ($data['filter_supto_status'] == 3) { // Анулирани
				$sql .= " AND o.supto_anul = 1";
			}elseif ($data['filter_supto_status'] == 4) { // Обработващи се
				$sql .= " AND (o.supto_unp <> '') AND (o.supto_anul = 0) AND (o.supto_completed = 0)";
			}elseif ($data['filter_supto_status'] == 5) { // Необработени
				$sql .= " AND (o.supto_sale_id <> 0) AND (o.supto_unp = '') AND (o.supto_anul = 0) AND (o.supto_completed = 0)";
			}
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND CONCAT(o.firstname, ' ', o.lastname) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if (!empty($data['filter_total'])) {
			$sql .= " AND o.total = '" . (float)$data['filter_total'] . "'";
		}

		$sort_data = array(
			'o.order_id',
			'customer',
			'order_status',
			'o.date_added',
			'o.total'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY o.order_id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getOrder($order_id) {
		$order_query = $this->db->query("SELECT *, (SELECT CONCAT(c.firstname, ' ', c.lastname) FROM " . DB_PREFIX . "customer c WHERE c.customer_id = o.customer_id) AS customer, (SELECT os.name FROM " . DB_PREFIX . "order_status os WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') AS order_status FROM `" . DB_PREFIX . "order` o WHERE o.order_id = '" . (int)$order_id . "'");

		if ($order_query->num_rows) {
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['payment_country_id'] . "'");

			if ($country_query->num_rows) {
				$payment_iso_code_2 = $country_query->row['iso_code_2'];
				$payment_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$payment_iso_code_2 = '';
				$payment_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['payment_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$payment_zone_code = $zone_query->row['code'];
			} else {
				$payment_zone_code = '';
			}

			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['shipping_country_id'] . "'");

			if ($country_query->num_rows) {
				$shipping_iso_code_2 = $country_query->row['iso_code_2'];
				$shipping_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$shipping_iso_code_2 = '';
				$shipping_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['shipping_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$shipping_zone_code = $zone_query->row['code'];
			} else {
				$shipping_zone_code = '';
			}

			$reward = 0;

			$order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

			foreach ($order_product_query->rows as $product) {
				$reward += $product['reward'];
			}
			
			$this->load->model('customer/customer');

			$affiliate_info = $this->model_customer_customer->getCustomer($order_query->row['affiliate_id']);

			if ($affiliate_info) {
				$affiliate_firstname = $affiliate_info['firstname'];
				$affiliate_lastname = $affiliate_info['lastname'];
			} else {
				$affiliate_firstname = '';
				$affiliate_lastname = '';
			}

			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($order_query->row['language_id']);

			if ($language_info) {
				$language_code = $language_info['code'];
			} else {
				$language_code = $this->config->get('config_language');
			}

			return array(
				'order_id'                => $order_query->row['order_id'],
				'invoice_no'              => $order_query->row['invoice_no'],
				'invoice_prefix'          => $order_query->row['invoice_prefix'],
				'store_id'                => $order_query->row['store_id'],
				'store_name'              => $order_query->row['store_name'],
				'store_url'               => $order_query->row['store_url'],
				'customer_id'             => $order_query->row['customer_id'],
				'customer'                => $order_query->row['customer'],
				'customer_group_id'       => $order_query->row['customer_group_id'],
				'firstname'               => $order_query->row['firstname'],
				'lastname'                => $order_query->row['lastname'],
				'email'                   => $order_query->row['email'],
				'telephone'               => $order_query->row['telephone'],
				'custom_field'            => json_decode($order_query->row['custom_field'], true),
				'payment_firstname'       => $order_query->row['payment_firstname'],
				'payment_lastname'        => $order_query->row['payment_lastname'],
				'payment_company'         => $order_query->row['payment_company'],
				'payment_address_1'       => $order_query->row['payment_address_1'],
				'payment_address_2'       => $order_query->row['payment_address_2'],
				'payment_postcode'        => $order_query->row['payment_postcode'],
				'payment_city'            => $order_query->row['payment_city'],
				'payment_zone_id'         => $order_query->row['payment_zone_id'],
				'payment_zone'            => $order_query->row['payment_zone'],
				'payment_zone_code'       => $payment_zone_code,
				'payment_country_id'      => $order_query->row['payment_country_id'],
				'payment_country'         => $order_query->row['payment_country'],
				'payment_iso_code_2'      => $payment_iso_code_2,
				'payment_iso_code_3'      => $payment_iso_code_3,
				'payment_address_format'  => $order_query->row['payment_address_format'],
				'payment_custom_field'    => json_decode($order_query->row['payment_custom_field'], true),
				'payment_method'          => $order_query->row['payment_method'],
				'payment_code'            => $order_query->row['payment_code'],
				'shipping_firstname'      => $order_query->row['shipping_firstname'],
				'shipping_lastname'       => $order_query->row['shipping_lastname'],
				'shipping_company'        => $order_query->row['shipping_company'],
				'shipping_address_1'      => $order_query->row['shipping_address_1'],
				'shipping_address_2'      => $order_query->row['shipping_address_2'],
				'shipping_postcode'       => $order_query->row['shipping_postcode'],
				'shipping_city'           => $order_query->row['shipping_city'],
				'shipping_zone_id'        => $order_query->row['shipping_zone_id'],
				'shipping_zone'           => $order_query->row['shipping_zone'],
				'shipping_zone_code'      => $shipping_zone_code,
				'shipping_country_id'     => $order_query->row['shipping_country_id'],
				'shipping_country'        => $order_query->row['shipping_country'],
				'shipping_iso_code_2'     => $shipping_iso_code_2,
				'shipping_iso_code_3'     => $shipping_iso_code_3,
				'shipping_address_format' => $order_query->row['shipping_address_format'],
				'shipping_custom_field'   => json_decode($order_query->row['shipping_custom_field'], true),
				'shipping_method'         => $order_query->row['shipping_method'],
				'shipping_code'           => $order_query->row['shipping_code'],
				'comment'                 => $order_query->row['comment'],
				'total'                   => $order_query->row['total'],
				'reward'                  => $reward,
				'order_status_id'         => $order_query->row['order_status_id'],
				'order_status'            => $order_query->row['order_status'],
				'affiliate_id'            => $order_query->row['affiliate_id'],
				'affiliate_firstname'     => $affiliate_firstname,
				'affiliate_lastname'      => $affiliate_lastname,
				'commission'              => $order_query->row['commission'],
				'language_id'             => $order_query->row['language_id'],
				'language_code'           => $language_code,
				'currency_id'             => $order_query->row['currency_id'],
				'currency_code'           => $order_query->row['currency_code'],
				'currency_value'          => $order_query->row['currency_value'],
				'ip'                      => $order_query->row['ip'],
				'forwarded_ip'            => $order_query->row['forwarded_ip'],
				'user_agent'              => $order_query->row['user_agent'],
				'accept_language'         => $order_query->row['accept_language'],
				'date_added'              => $order_query->row['date_added'],
				'date_modified'           => $order_query->row['date_modified']
			);
		} else {
			return;
		}
	}



	public function getOrderProducts($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = " . (int)$order_id . " ORDER BY order_product_id");

		return $query->rows;
	}

	public function getOrderManufacturer($id) {
		$query = $this->db->query("SELECT name FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = " . (int)$id);
		return $query->row['name'];
	}

	public function createSchema() {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "fakturirane_eu (
				api_source TINYINT NULL DEFAULT 1 ,
				api_user VARCHAR(100) NULL DEFAULT '' ,
				api_key VARCHAR(250) NULL DEFAULT '',
				object_id INT NULL DEFAULT 0,
				station_id INT NULL DEFAULT 0,
				debug_mode TINYINT NULL DEFAULT 0,
				add_to_catalog TINYINT NULL DEFAULT 1,
				product_code_field TINYINT NULL DEFAULT 0,
				measure_id INT NULL DEFAULT 70,
				source_id INT NULL DEFAULT 1,
				payment_method_id TINYINT NULL DEFAULT 1,
				payments_cash VARCHAR(60) NOT NULL,
				payments_bank VARCHAR(60) NOT NULL,
				payments_card VARCHAR(60) NOT NULL,
				payments_cod VARCHAR(60) NOT NULL,
				payments_mt VARCHAR(60) NOT NULL,
				vat_percent TINYINT NULL DEFAULT 0
			) DEFAULT CHARSET=utf8
");

		$this->db->query("TRUNCATE " . DB_PREFIX . "fakturirane_eu");
		$this->db->query("INSERT INTO " . DB_PREFIX . "fakturirane_eu (api_user, api_key, object_id, station_id, debug_mode, add_to_catalog, product_code_field, source_id, payment_method_id, payments_cash, payments_bank, payments_card, payments_cod, payments_mt, measure_id, vat_percent) VALUES ('', '', 0, 0, 0, 1, 0, 1, 1, '', 'bank_transfer', '', 'cod', '', 70, 0)");

		$this->db->query("ALTER TABLE " . DB_PREFIX . "order 
		ADD IF NOT EXISTS supto_sale_id INT NULL DEFAULT 0,
		ADD IF NOT EXISTS supto_unp VARCHAR(50) NULL DEFAULT '',
		ADD IF NOT EXISTS supto_anul TINYINT NULL DEFAULT 0,
		ADD IF NOT EXISTS supto_completed TINYINT NULL DEFAULT 0
		");
		// тези няма да се дропват при деинсталиране - нарочно
	}
	public function deleteSchema() {
		$this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "fakturirane_eu");
	}
}
?>