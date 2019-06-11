<?php
/**
 * FAPI - PHP клас за API достъп
 *
 * Функции за работа с информация до базите данни на лицензите от тип "Облачна база дани",
 * предлагани от фирма "Лиценз" ЕООД чрез уеб сайта на програма "Фактуране EU" - https://fakturirane.eu/
 * @copyright  2019 Licenz Ltd.
 * @version    Release: 3.17
 * @link       https://fakturirane.eu/help/api/
**/

class FAPI {
	protected $eik;
	protected $key;
	protected $ch;
	protected $session;

	const API_VERSION = 3.17;
	const API_URL = 'https://fakturirane.eu/api/fakapi.php';

	public function __construct ($eik, $key){
		$this->eik = $eik;
		$this->key = $key;

		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_URL, self::API_URL);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_POST, 1);
	}

	private function send_data($data){
		$data['session'] = $this->session;
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
		$result = curl_exec($this->ch);
		$result = json_decode($result);
		if($result->error == 0){ 
			return $result;
		}else{
			$this->display_error($result->error, $data['function']);
			return null;
		}
	}

	public function login(){
		$result = $this->send_data(array('function' => 'login', 'eik' => $this->eik, 'key' => $this->key, 'host'=>$_SERVER['HTTP_HOST'], 'user_agent'=>$_SERVER['HTTP_USER_AGENT'], 'ip'=>$_SERVER['REMOTE_ADDR'], 'api_version'=>self::API_VERSION));
		if(isset($result)){
			$this->session = $result->session;
			return true;
		}else{
			$this->session = null;
			return false;
		}
	}

	public function logout(){
		$this->send_data(array('function' => 'logout'));
		$this->session = null;
	}

	public function welcome($return = false){
		if($return){
			return 'Добре дошли във Фактуриране API - версия '.self::API_VERSION.'!';
		}else{
			echo 'Добре дошли във Фактуриране API - версия '.self::API_VERSION.'!';
		}
	}

	public function display_error($error, $function){
		$result = 'Възникна грешка ('.$error.')! ';
		switch ($error) {
			case    1: $result .= 'Неразпознато име на функция - '.$function.'.'; break;
			case    2: $result .= 'Не е посочена функция. Използвайте параметъра function.'; break;
			case    3: $result .= 'Невалиден вид на документ. Използвайте 1 за фактура/и, 2 за проформа/и, 3 за стокови разписка/и или 4 за протокол/и.'; break;
			case    4: $result .= 'Грешка при изпълнение на заявка.'; break;

			case    10: $result .= 'Не сте се идентифицирали с ЕИК и ключ.'; break;

			case    51: $result .= 'Не е въведен ЕИК за достъп.'; break;
			case    52: $result .= 'Не е въведен API ключ за достъп.'; break;
			case    53: $result .= 'Грешка при осъществяване на достъп или изтекъл абонамент. Моля свържете се с нас за повече подробности.'; break;
			case    54: $result .= 'Грешен ключ за достъп.'; break;
			case    55: $result .= 'Вече сте осъществили връзка. Моля използвайте някоя функция от FAPI.'; break;
			case    56: $result .= 'Вашият лиценз е изтекъл. Необходимо е да го подновите от <a href="https://fakturirane.eu/license/renew/" target="_blank">https://fakturirane.eu/license/renew/</a>.'; break;
			case    57: $result .= 'Изтекъл абонамент за API функционалност.'; break;

			case 101: $result .= 'Не е изпратен обект като първи параметър на функцията.'; break;
			case 102: $result .= 'Не е попълнено полето eik на обекта, който е подаден като параметър.'; break;
			case 103: $result .= 'Не е попълнено полето name на обекта, който е подаден като параметър.'; break;
			case 104: $result .= 'С този ЕИК вече съществува клиент в базата данни.'; break;

			case 111: $result .= 'Не е въведен ЕИК или ID.'; break;
			case 112: $result .= 'Клиентът не е открит.'; break;

			case 121: $result .= 'Не е посочен ID номер на клиент.'; break;
			case 122: $result .= 'Не е изпратен обект като втори параметър на функцията.'; break;
			case 123: $result .= 'Информацията за клиенти, за които е въведено 000000000, 111111111 или 999999999 за ЕИК номер, може да бъде обновявана само чрез ID.'; break;
			case 124: $result .= 'Не е изпратена информация за обновяване.'; break;
			case 125: $result .= 'Има повече от един клиент с този ЕИК/ID.'; break;

			case 131: $result .= 'Клиенти, за които е въведено 000000000, 111111111 или 999999999 за ЕИК номер могат да бъдат изтривани само чрез ID.'; break;

			case 141: $result .= 'Не e въведен текст за търсене. Въведете част от името или телефонен номер.'; break;
			case 142: $result .= 'Не са открити съвпадения.'; break; 

			case 151: $result .= 'Нямате въведени клиенти.'; break;

			case 204: $result .= 'С този артикулен номер вече съществува запис в базата данни.'; break; 
			case 211: $result .= 'Не е въведен артикулен номер или ID.'; break; 
			case 212: $result .= 'Стоката не е открита.'; break; 
			case 225: $result .= 'Има повече от един артикул с този номер/ID.'; break;

			case 241: $result .= 'Не e въведен текст за търсене. Въведете част от името или артикулния номер.'; break;
			case 242: $result .= 'Не са открити съвпадения.'; break;

			case 501: $result .= 'Не е въведен ЕИК. Можете да използвате 000000000, 111111111 или 999999999 ако не знаете ЕИК на клиента!'; break;
			case 502: $result .= 'Не е подаден последния параметър с детайлите на документа и редовете!'; break;
			case 503: $result .= 'Клиент с този ЕИК не е открит. Моля, въведете го в базата данни преди да продължите.'; break;
			case 551: $result .= 'След този документ има издадени други и затова не може да бъде изтрит!'; break;

			case 571: $result .= 'Не e въведен текст за търсене!'; break;
			case 572: $result .= 'Не e въведен тип за търсене!'; break;

			case 581: $result .= 'Не е посочен ID номер на документ.'; break;
			case 582: $result .= 'Документът не е открит.'; break;

			case 591: $result .= 'Невалидна година! Въведете число за година - напр. 2018.'; break;
			case 592: $result .= 'Невалиден начин на плащане! Въведете цифра от 1 до 5 или 0 за всички.'; break;
			case 593: $result .= 'Протоколите и стоковите разписки не поддържат начин на плащане.'; break;

			case 601: $result .= 'Клиентът няма въведен email адрес.'; break;
			case 602: $result .= 'Документът не е асоцииран с клиент.'; break;

			case 801: $result .= 'Не е посочен артикулен номер.'; break;
			case 802: $result .= 'Не е открит продукт с този артикулен номер.'; break;
			case 803: $result .= 'Не е изпратен обект като втори параметър на функцията.'; break;
			case 804: $result .= 'Не е зададено к-во ($q->quantity) в обекта, който се изпраща като втори параметър на функцията.'; break;
			case 805: $result .= 'Не е зададена цена ($q->price) в обекта, който се изпраща като втори параметър на функцията.'; break;

			case 1001: $result .= 'Не е открита профилна таблица в базата с данни.'; break;

			case 2001: $result .= 'Не е изпратен номер на продажба.'; break; // TODO - НОВО - да се опише!

			default:
				$result .= 'Грешка ('.$error.')!';
		}

		throw new Exception($result);
	}

	public function close_connection(){
		curl_close($this->ch);
	}

	// API PUBLIC FUNCTIONS


	public function license_expire(){
		$result = $this->send_data(array('function' => 'license_expire'));
		if(isset($result)){
			return $result->license_expire;
		}else{
			return null;
		}
	}

	// PROFILE  >>>
	public function profile_retrieve(){
		$result = $this->send_data(array('function' => 'profile_retrieve'));
		if(isset($result)){
			return $result->profile;
		}else{
			return null;
		}
	}

	public function profile_update($profile){
		$data = array('function'=>'profile_update', 'profile'=>json_encode($profile));
		$result = $this->send_data($data);
		if(isset($result)){
			return $result->id;
		}else{
			return 0;
		}
	}
	// PROFILE <<<

	// CLIENTS  >>>
	public function client_create($client){
		$result = $this->send_data(array('function' => 'client_create', 'client'=>json_encode($client)));
		if(isset($result)){
			return $result->client_id;
		}else{
			return null;
		}
	}

	public function client_list(){
		$result = $this->send_data(array('function' => 'client_list'));
		if(isset($result)){
			return $result->clients;
		}else{
			return null;
		}
	}

	public function client_retrieve($id, $type = 1){
		$result = $this->send_data(array('function' => 'client_retrieve', 'id'=>$id, 'type'=>$type));
		if(isset($result)){
			return $result->client;
		}else{
			return null;
		}
	}

	public function client_update($id, $client, $type = 1){
		$data = array('function'=>'client_update', 'id'=>$id, 'type'=>$type, 'client'=>json_encode($client));
		$result = $this->send_data($data);
		if(isset($result)){
			return $result->id;
		}else{
			return 0;
		}
	}

	public function client_delete($id, $type = 1){
		$data = array('function'=>'client_delete', 'id'=>$id, 'type'=>$type);
		return $this->send_data($data);
	}

	public function client_search($search){
		$result = $this->send_data(array('function' => 'client_search', 'search'=>$search));
		if(isset($result)){
			return $result->clients;
		}else{
			return null;
		}
	}
	// CLIENTS <<<

	// ITEMS >>>
	public function item_create($item){
		$result = $this->send_data(array('function' => 'item_create', 'item'=>json_encode($item)));
		if(isset($result)){
			return $result->item_id;
		}else{
			return null;
		}
	}

	public function item_retrieve($id, $type = 1){
		$result = $this->send_data(array('function' => 'item_retrieve', 'id'=>$id, 'type'=>$type));
		if(isset($result)){
			return $result->item;
		}else{
			return null;
		}
	}

	public function item_update($id, $item, $type = 1){
		$data = array('function'=>'item_update', 'id'=>$id, 'type'=>$type, 'item'=>json_encode($item));
		$result = $this->send_data($data);
		if(isset($result)){
			return $result->id;
		}else{
			return 0;
		}
	}

	public function item_delete($id, $type = 1){
		$data = array('function'=>'item_delete', 'id'=>$id, 'type'=>$type);
		return $this->send_data($data);
	}

	public function item_search($search){
		$result = $this->send_data(array('function' => 'item_search', 'search'=>$search));
		if(isset($result)){
			return $result->items;
		}else{
			return null;
		}
	}
	// ITEMS <<<

	// DOCUMENTS >>>
	public function document_create($eik, $type, $document){
		$data = array('function'=>'document_create', 'type'=>$type, 'eik'=>$eik, 'document'=>json_encode($document));
		$result = $this->send_data($data);
		if(isset($result)){
			return $result->document;
		}else{
			return null;
		}
	}

	public function document_retrieve($type, $id){
		$data = array('function'=>'document_retrieve', 'type'=>$type, 'id'=>$id);
		$result = $this->send_data($data);
		if(isset($result)){
			return $result->document;
		}else{
			return null;
		}
	}

	public function document_list($type = 1, $eik = '', $limit = 5){
		$data = array('function'=>'document_list', 'type'=>$type, 'eik'=>$eik, 'limit'=>$limit);
		$result = $this->send_data($data);
		if(isset($result)){
			if(isset($result->documents)){
				return $result->documents;
			}else{
				return null;
			}
		}else{
			return null;
		}
	}

	public function document_search($type = 1, $search = '', $search_type = 0){
		$data = array('function'=>'document_search', 'type'=>$type, 'search_type'=>$search_type, 'search'=>$search);
		$result = $this->send_data($data);
		if(isset($result)){
			if(isset($result->documents)){
				return $result->documents;
			}else{
				return null;
			}
		}else{
			return null;
		}
	}

	public function document_update($type, $id, $document){
		$data = array('function'=>'document_update', 'id'=>$id, 'type'=>$type, 'document'=>json_encode($document));
		$result = $this->send_data($data);
		if(isset($result)){
			return $result->document;
		}else{
			return 0;
		}
	}

	public function document_delete($type, $id, $numbers_id = 0){
		$data = array('function'=>'document_delete', 'type'=>$type, 'id'=>$id, 'numbers_id' => $numbers_id);
		$result = $this->send_data($data);
		if(isset($result)){
			return $result->id;
		}else{
			return 0;
		}
	}

	public function document_clone($type, $id){
		$data = array('function'=>'document_clone', 'type'=>$type, 'id'=>$id);
		$result = $this->send_data($data);
		if(isset($result)){
			return $result->document;
		}else{
			return null;
		}
	}

	public function document_print_pdf($type, $id, $copy = 0, $language = 0){
		$data = array('function'=>'document_print_pdf', 'type'=>$type, 'id'=>$id, 'copy'=>$copy, 'language'=>$language);
		$result = $this->send_data($data);
		if(isset($result)){
			return urldecode($result->document);
		}else{
			return null;
		}
	}

	public function document_send($type, $id, $copy = 0, $language = 0, $email = '', $settings){
		$data = array('function'=>'document_send', 'type'=>$type, 'id'=>$id, 'copy'=>$copy, 'language'=>$language, 'email' => $email, 'settings'=>json_encode($settings));
		$result = $this->send_data($data);
		if(isset($result)){
			return $result->error;
		}else{
			return null;
		}
	}

	public function document_create_and_send($eik, $type, $document, $copy = 0, $language = 0, $email = '', $settings){
		$data = array('function'=>'document_create_and_send', 'type'=>$type, 'eik'=>$eik, 'document'=>json_encode($document), 'copy'=>$copy, 'language'=>$language, 'email' => $email, 'settings'=>json_encode($settings));
		$result = $this->send_data($data);
		if(isset($result)){
			return $result->document;
		}else{
			return null;
		}
	}

	public function document_statistics($type = 1, $year = 0, $payment_method = 0){
		$result = $this->send_data(array('function' => 'document_statistics', 'type'=>$type, 'year'=>$year, 'payment_method'=>$payment_method));
		if(isset($result)){
			return $result->count;
		}else{
			return null;
		}
	}
	// DOCUMENTS <<<

	// SALES  >>>
	public function sale_create($eik, $sale){
		$data = array('function'=>'sale_create', 'eik'=>$eik, 'sale'=>json_encode($sale));
		$result = $this->send_data($data);
		if(isset($result)){
			return $result->sale;
		}else{
			return null;
		}
	}

	public function sale_get_status($sale_id){
		$data = array('function'=>'sale_get_status', 'sale_id'=>$sale_id);
		$result = $this->send_data($data);
		if(isset($result)){
			return $result->status;
		}else{
			return null;
		}
	}

	// SALES <<<

	// WAREHOUSE >>>
	public function warehouse_item_quantity($code, $quantity){
		$data = array('function'=>'warehouse_item_quantity', 'code'=>$code, 'quantity'=> json_encode($quantity));
		$result = $this->send_data($data);
		if(isset($result)){
			return $result->count;
		}else{
			return null;
		}
	}

	public function warehouse_item_status($code){
		$data = array('function'=>'warehouse_item_status', 'code'=>$code);
		$result = $this->send_data($data);
		if(isset($result)){
			return $result->count;
		}else{
			return null;
		}
	}
	// WAREHOUSE <<<

}
?>