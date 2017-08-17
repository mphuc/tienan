<?php
class ModelAccountCustomer extends Model {
	
	public function get_all_username() {
		
		$query = $this -> db -> query("
			SELECT * FROM " . DB_PREFIX . "customer
			");

		return $query -> rows;
	}

	public function get_last_tienan() {
		$query = $this -> db -> query("
			SELECT week FROM " . DB_PREFIX . "tienan ORDER BY week DESC LIMIT 1 
			");

		return $query -> row;
	}

	public function insert_tienan($week,$customer_id) {
		$query = $this -> db -> query("
			INSERT INTO " . DB_PREFIX . "tienan SET
			week = '".$week."',
			customer_id = '".$customer_id."'
			");
	}

	public function update_tienan($week,$thu,$number,$account_thu,$customer_id,$dicho) {

		$amount = 0;

		if ($dicho == $customer_id)
		{
			$amount = $account_thu;
		}
		
		$query = $this -> db -> query("	
			UPDATE " . DB_PREFIX . "tienan SET
			thu".$thu." = '".$number."',
			amount_thu".$thu." = '".$amount."'
			WHERE customer_id = '".$customer_id."' AND
			week = '".$week."' 
		");
	}

	public function check_week($week) {
		$query = $this -> db -> query("
			SELECT count(*) as number FROM " . DB_PREFIX . "tienan WHERE week = '".$week."'
			");

		return $query -> row['number'];
	}


	public function get_data_number($customer_id,$week) {
		$query = $this -> db -> query("
			SELECT * FROM " . DB_PREFIX . "tienan 
			WHERE week = '".$week."' AND customer_id = '".$customer_id."'
			");
		return $query -> row;
	}

	
	public function total_week($week,$customer_id){
		$query = $this -> db -> query("
			SELECT amount_thu2 + amount_thu3 + amount_thu4 + amount_thu5 + amount_thu6 + amount_thu7 + amount_thu8 as tolal_di_cho FROM " . DB_PREFIX . "tienan 
			WHERE week = '".$week."' AND customer_id = '".$customer_id."'
			");
		return $query -> row['tolal_di_cho'];
		
	}


	public function calue_total($week,$customer_id,$thu)
	{
		$query = $this -> db -> query("
			SELECT sum(thu".$thu.") as number FROM " . DB_PREFIX . "tienan 
			WHERE week = '".$week."'  AND thu".$thu." > 0
		");
		$total_all = $query -> row['number'];

		$queryss = $this -> db -> query("
			SELECT thu".$thu." FROM " . DB_PREFIX . "tienan 
			WHERE week = '".$week."' AND customer_id = '".$customer_id."'
		");
		$solanan = $queryss -> row['thu'.$thu.''];

		$queryssss = $this -> db -> query("
			SELECT amount_thu".$thu." FROM " . DB_PREFIX . "tienan 
			WHERE week = '".$week."' AND amount_thu".$thu." > 0 
		");
		if (count($queryssss -> row) > 0){
			$tienantotal = $queryssss -> row['amount_thu'.$thu.''];
		}
		else
		{
			$tienantotal = 0;
		}
		if ($total_all == 0)
		{
			$thu22 = 0;
		}
		else
		{
			$thu22 = ($tienantotal/$total_all)*$solanan;
		}
		return $thu22;
	}


	public function count_p_binary($p_binary){
		$query = $this -> db -> query("
			SELECT `left`,`right` FROM ". DB_PREFIX ."customer_ml WHERE `customer_id` ='".$p_binary."' AND status <> -1
		");
		return $query -> row;
	}
	public function getUserName() {
		$query = $this -> db -> query("SELECT username FROM " . DB_PREFIX . "customer WHERE customer_id = '" . $this -> session -> data['customer_id'] . "'");

		return $query -> row['username'];
	}
	public function get_all_customer_signup($id_customer){
		$query = $this -> db -> query("
			SELECT *
			FROM  ".DB_PREFIX."customer
			WHERE p_node = '".$this -> db -> escape($id_customer)."' AND check_signup = 2
		");
		return $query -> rows;
	}
	public function update_R_Wallet_add($amount, $customer_id, $wallet,$pakacge,$percent){
		
			$query = $this -> db -> query("
			INSERT INTO " . DB_PREFIX . "customer_r_wallet_payment SET
				amount = ".$amount.",
				customer_id = ".$customer_id.",
				addres_wallet = '".$wallet."',
				date_end = DATE_ADD( NOW(), INTERVAL + 120 DAY),
				pakacge = '".$pakacge."',
				percent = '".$percent."'
			");
		
		return $query === true ? true : false;
	}




	public function getCustomer_Pd_last(){
		$query = $this -> db -> query("
			SELECT pd.amount, c.customer_id FROM sm_customer_invoice_pd AS pd JOIN sm_customer AS c ON c.customer_id = pd.customer_id WHERE `confirmations` = 3
		");
		return $query -> rows;
	}

	public function addCustomer($data) {
		$this -> event -> trigger('pre.customer.add', $data);

		if (isset($data['customer_group_id']) && is_array($this -> config -> get('config_customer_group_display')) && in_array($data['customer_group_id'], $this -> config -> get('config_customer_group_display'))) {
			$customer_group_id = $data['customer_group_id'];
		} else {
			$customer_group_id = $this -> config -> get('config_customer_group_id');
		}

		$this -> load -> model('account/customer_group');

		$customer_group_info = $this -> model_account_customer_group -> getCustomerGroup($customer_group_id);

		$this -> db -> query("INSERT INTO " . DB_PREFIX . "customer SET customer_group_id = '" . (int)$customer_group_id . "', store_id = '" . (int)$this -> config -> get('config_store_id') . "', firstname = '" . $this -> db -> escape($data['firstname']) . "', lastname = '" . $this -> db -> escape($data['lastname']) . "', email = '" . $this -> db -> escape($data['email']) . "', telephone = REPLACE('" . $this -> db -> escape($data['telephone']) . "', ' ', ''), cmnd = '" . $this -> db -> escape($data['cmnd']) . "', account_bank = '" . $this -> db -> escape($data['account_bank']) . "', address_bank = '" . $this -> db -> escape($data['address_bank']) . "', p_node = '" . $this -> db -> escape($data['p_node']) . "', custom_field = '" . $this -> db -> escape(isset($data['custom_field']['account']) ? serialize($data['custom_field']['account']) : '') . "', salt = '" . $this -> db -> escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this -> db -> escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "', newsletter = '" . (isset($data['newsletter']) ? (int)$data['newsletter'] : 0) . "', ip = '" . $this -> db -> escape($this -> request -> server['REMOTE_ADDR']) . "', status = '1', approved = '" . (int)!$customer_group_info['approval'] . "', date_added = NOW()");

		$customer_id = $this -> db -> getLastId();

		
		$this -> event -> trigger('post.customer.add', $customer_id);

		return $customer_id;
	}


	public function get_payment_blockchain($customer_id){
		$query = $this -> db -> query("
			SELECT customer_id 
			FROM ".DB_PREFIX."customer_payment_blockhain
			WHERE customer_id = ".$customer_id."
		");
		return $query -> row;
	}

	public function insert_payment_blockain($customer_id){
		$query = $this -> db -> query("
			INSERT INTO ".DB_PREFIX."customer_payment_blockhain
			SET customer_id = ".$customer_id."
		");
		return $query;
	}

	public function getInfoUsers_binary($id_id){

		$query = $this->db->query("select u.*,ml.level, l.name_vn as level_member, u.firstname from ". DB_PREFIX . "customer_ml as ml Left Join " . DB_PREFIX . "customer as u ON ml.customer_id = u.customer_id Left Join " . DB_PREFIX . "member_level as l ON l.id = ml.level Where ml.customer_id = " . $id_id);
		$return  = $query->row;
		return $return;
	}

	public function saveTranstionHistory($customer_id, $wallet, $text_amount, $system_decsription,$type,$balance, $url = ''){

		$date_added= date('Y-m-d H:i:s') ;

		$query = $this -> db -> query("
			INSERT INTO ".DB_PREFIX."customer_transaction_history SET
			customer_id = '".$customer_id."',
			wallet = '".$wallet."',
			text_amount = '".$text_amount."',
			system_decsription = '".$system_decsription."',
			type = '".$type."',
			balance = '".$balance."',
			url = '".$url."',
			date_added = '".$date_added."'
		");
		$id = $this -> db -> getLastId();
		
		$this -> db -> query("
			UPDATE " . DB_PREFIX . "customer_transaction_history 
			SET code = '".hexdec( crc32($id) ).$id."'
			WHERE id = ".$id."
			
		");

		return $query;
	}
	public function Update_url_History_id($url,$id){
		$query = $this -> db -> query("
			UPDATE ".DB_PREFIX."customer_transaction_history SET
			url = '".$url."'
			WHERE id = '".$id."'
		");
		return $this->db->getLastId();
	}
	public function getGdFromTransferList($gd_id){
		$query = $this -> db -> query("
			SELECT ctl.* , c.username
			FROM ". DB_PREFIX . "customer_transfer_list AS ctl
			JOIN ". DB_PREFIX ."customer AS c
				ON ctl.pd_id_customer = c.customer_id
			WHERE ctl.gd_id = '".$this->db->escape($gd_id)."'
		");
		return $query -> rows;
	}
	public function updateCheck_R_WalletPD($pd_id){
		$query = $this -> db -> query("
			UPDATE " . DB_PREFIX . "customer_provide_donation SET
				check_R_Wallet = 1
				WHERE id = '".$pd_id."'
			");
		return $query;
	}

	public function getGDTranferByID($transacion_id){

		$query = $this -> db -> query("
			SELECT c.username, ctl.*
			FROM ". DB_PREFIX . "customer_transfer_list AS ctl
			JOIN ". DB_PREFIX ."customer AS c
				ON ctl.pd_id_customer = c.customer_id
			WHERE ctl.id = '".$this->db->escape($transacion_id)."' AND gd_id_customer = ".$this -> session -> data['customer_id']."
		");
		return $query -> row;
	}

	public function getPNode($customer_id){
		$query = $this -> db -> query("
			SELECT * FROM sm_customer_provide_donation pd JOIN sm_customer_get_donation gd on pd.customer_id = gd.customer_id WHERE pd.customer_id in 
			(SELECT customer_id FROM sm_customer WHERE p_node = ".$customer_id.") AND pd.status = 2 AND gd.status = 2 GROUP BY pd.customer_id		");
		return $query -> rows;
	}
	
	public function getPDByTranferID($transacion_id){
		$query = $this -> db -> query("
			SELECT id
			FROM ". DB_PREFIX . "customer_provide_donation
			WHERE id = '".$transacion_id."'
		");
		return $query -> row;
	}
	public function countStatusPDTransferList($pd_id){
		$query = $this -> db -> query("
			SELECT COUNT(*) AS number
			FROM ". DB_PREFIX ."customer_transfer_list
			WHERE pd_id = '". $pd_id ."' AND pd_status = 0
			");
		return $query -> row;
	}
	public function countStatusGDTransferList($pd_id){
		$query = $this -> db -> query("
			SELECT COUNT(*) AS number
			FROM ". DB_PREFIX ."customer_transfer_list
			WHERE gd_id = '". $pd_id ."' AND pd_status = 0
			");
		return $query -> row;
	}
	public function updateStusPD($pd_id){
		$query = $this -> db -> query("
			UPDATE " . DB_PREFIX . "customer_provide_donation SET
				status = 2
				WHERE id = '".$pd_id."'
			");
		return $query;
	}
	public function updateStusPDActive($pd_id){
		$query = $this -> db -> query("
			UPDATE " . DB_PREFIX . "customer_provide_donation SET
				status = 1
				WHERE id = '".$pd_id."'
			");
		return $query;
	}
	public function updateStusGDActive($pd_id){
		$query = $this -> db -> query("
			UPDATE " . DB_PREFIX . "customer_get_donation SET
				status = 1
				WHERE id = '".$pd_id."'
			");
		return $query;
	}
	
	public function updateStusGD($gd_id){
		$query = $this -> db -> query("
			UPDATE " . DB_PREFIX . "customer_get_donation SET
				status = 2
				WHERE id = '".$gd_id."'
			");
		return $query;
	}

	public function updateStatusPDTransferList($transferID, $transaction_hash,$input_transaction_hash){
		$query = $this -> db -> query("
			UPDATE " . DB_PREFIX . "customer_transfer_list SET
				pd_status = 1,
				gd_status = 1,
				
				transaction_hash ='".$transaction_hash."',
				input_transaction_hash ='".$input_transaction_hash."'
				WHERE transfer_code = '".$this->db->escape($transferID)."'
		");
		return $query;
	}

	public function getPDTranferByID($transacion_id){
		$query = $this -> db -> query("
			SELECT c.*, ctry.name, ctl.*
			FROM ". DB_PREFIX . "customer_transfer_list AS ctl
			JOIN ". DB_PREFIX ."customer AS c
				ON ctl.gd_id_customer = c.customer_id
			JOIN ". DB_PREFIX ."country AS ctry
				ON ctry.country_id = c.country_id
			WHERE ctl.id = '".$this->db->escape($transacion_id)."'
		");
		return $query -> row;
	}
	public function getCountryByID($id){
		$query = $this -> db -> query("
			SELECT *
			FROM ". DB_PREFIX ."country 
			WHERE country_id = '".$this->db->escape($id)."'
		");
		return $query -> row;
	}

	public function getPdFromTransferList($pd_id){

		$query = $this -> db -> query("
			SELECT ctl.* , c.username, c.wallet
			FROM ". DB_PREFIX . "customer_transfer_list AS ctl
			JOIN ". DB_PREFIX ."customer AS c
				ON ctl.gd_id_customer = c.customer_id
			WHERE ctl.pd_id = '".$this->db->escape($pd_id)."'
		");
		return $query -> rows;
	}
	
	public function getGDByCustomerIDAndToken($customer_id, $token){
		$query = $this -> db -> query("
			SELECT COUNT(*) AS number
			FROM ". DB_PREFIX ."customer_get_donation
			WHERE customer_id = '". $customer_id ."' AND id = '".$token."'
			");
		return $query -> row;
	}
	public function getPD($iod_customer){
		$query = $this -> db -> query("
			SELECT *
			FROM ". DB_PREFIX . "customer_provide_donation
			WHERE customer_id = '".$this->db->escape($iod_customer)."'
		");
		return $query -> rows;
	}
	public function getPDById($id_customer, $limit, $offset){

		$query = $this -> db -> query("
			SELECT pd.*, c.username
			FROM  ".DB_PREFIX."customer_provide_donation AS pd
			JOIN ". DB_PREFIX ."customer AS c
			ON pd.customer_id = c.customer_id
			WHERE pd.customer_id = '".$this -> db -> escape($id_customer)."' AND pd.status = 1
			ORDER BY pd.date_added DESC
			LIMIT ".$limit."
			OFFSET ".$offset."
		");
		
		return $query -> rows;
	}

	public function getPDByCustomerIDAndToken($customer_id, $token){
		$query = $this -> db -> query("
			SELECT COUNT(*) AS number
			FROM ". DB_PREFIX ."customer_provide_donation
			WHERE customer_id = '". $customer_id ."' AND id = '".$token."'
			");
		return $query -> row;
	}
	public function getPDConfirm($id){
		
		$query = $this -> db -> query("
			SELECT *
			FROM ". DB_PREFIX . "customer_provide_donation
			WHERE id = '".$this->db->escape($id)."'
		");
		return $query -> row;
	}
	public function createPD($amount, $max_profit){

		$date_added= date('Y-m-d H:i:s') ;
		$date_finish = strtotime ( '+30 day' , strtotime ( $date_added ) ) ;
		$date_finish= date('Y-m-d H:i:s',$date_finish) ;

		$this -> db -> query("
			INSERT INTO ". DB_PREFIX . "customer_provide_donation SET 
			customer_id = '".$this -> session -> data['customer_id']."',
			date_added = NOW(),
			filled = '".$amount."',
			date_finish = '".$date_finish."',
			max_profit = '".$max_profit."',
			status = 1
		");
		//update max_profit and pd_number
		$pd_id = $this->db->getLastId();

		//$max_profit = (float)($amount * $this->config->get('config_pd_profit')) / 100;
		
		$pd_number = hexdec( crc32($pd_id) );
		$query = $this -> db -> query("
			UPDATE " . DB_PREFIX . "customer_provide_donation SET 
				max_profit = '".$max_profit."',
				pd_number = '".$pd_number."'
				WHERE id = '".$pd_id."'
			");
		$data['query'] = $query ? true : false;
		$data['pd_number'] = $pd_number;
		$data['pd_id'] = $pd_id;
		return $data;
	}

	public function updatePD($amount, $customer_id){
		$this -> db -> query("
			UPDATE ". DB_PREFIX . "customer_provide_donation SET 
			date_finish = DATE_ADD(NOW(), INTERVAL + 30 DAY) ,
			status = 1
			WHERE customer_id = '".$customer_id."' AND filled = '".$amount."'
		");

	}


	public function createShare($amount, $count_share){
		$this -> db -> query("
			INSERT INTO ". DB_PREFIX . "customer_share SET 
			customer_id = '".$this -> session -> data['customer_id']."',
			date_added = NOW(),
			filled = '".$amount."',
			number_share = '".$count_share."',
			date_finish = DATE_ADD(NOW(), INTERVAL + 45 DAY) ,
			status = 0
		");
	}


	public function insertR_Wallet($id_customer){
		$query = $this -> db -> query("
			INSERT INTO " . DB_PREFIX . "customer_r_wallet SET
			customer_id = '".$this -> db -> escape($id_customer)."',
			amount = '0.0'
		");
		return $query;
	}
	public function insertR_WalletR($amount, $id_customer){
		$query = $this -> db -> query("
			INSERT INTO " . DB_PREFIX . "customer_r_wallet SET
			customer_id = '".$this -> db -> escape($id_customer)."',
			amount = ".$amount."
		");
		return $query;
	}

	public function insertC_Wallet($id_customer){
		$query = $this -> db -> query("
			INSERT INTO " . DB_PREFIX . "customer_c_wallet SET
			customer_id = '".$this -> db -> escape($id_customer)."',
			amount = '0'
		");
		return $query;
	}
	public function insertCN_Wallet($id_customer){
		$query = $this -> db -> query("
			INSERT INTO " . DB_PREFIX . "customer_cn_wallet SET
			customer_id = '".$this -> db -> escape($id_customer)."',
			amount = '0'
		");
		return $query;
	}
	public function checkR_Wallet($id_customer){
		$query = $this -> db -> query("
			SELECT COUNT(*) AS number
			FROM  ".DB_PREFIX."customer_r_wallet
			WHERE customer_id = '".$this -> db -> escape($id_customer)."'
		");
		return $query -> row;
	}
	public function checkCN_Wallet_payment($id_customer){
		$query = $this -> db -> query("
			SELECT COUNT(*) AS number
			FROM  ".DB_PREFIX."customer_cn_wallet_payment
			WHERE customer_id = '".$this -> db -> escape($id_customer)."'
		");
		return $query -> row;
	}
	public function updateR_Wallet($id_customer, $amount){
		$query = $this -> db -> query("
			UPDATE " . DB_PREFIX . "customer_r_wallet SET
			amount = '" . $this -> db -> escape((float)$amount) . "'
			WHERE customer_id = '" . (int)$id_customer . "'");

		return $query;
	}

	public function checkC_Wallet($id_customer){
		$query = $this -> db -> query("
			SELECT COUNT(*) AS number
			FROM  ".DB_PREFIX."customer_c_wallet
			WHERE customer_id = '".$this -> db -> escape($id_customer)."'
		");
		return $query -> row;
	}
	public function checkCN_Wallet($id_customer){
		$query = $this -> db -> query("
			SELECT COUNT(*) AS number
			FROM  ".DB_PREFIX."customer_cn_wallet
			WHERE customer_id = '".$this -> db -> escape($id_customer)."'
		");
		return $query -> row;
	}

	public function getmaxPD($id_customer){
		$query = $this -> db -> query("
			SELECT max(filled) AS number
			FROM  ".DB_PREFIX."customer_provide_donation
			WHERE customer_id = '".$this -> db -> escape($id_customer)."' AND status = 1
		");

		return $query -> row;
	}
	public function getmax_PD($id_customer){
		$query = $this -> db -> query("
			SELECT MAX(filled) AS number
			FROM  ".DB_PREFIX."customer_provide_donation
			WHERE customer_id = '".$this -> db -> escape($id_customer)."' AND status = 1 AND count_profit <= 80
		");

		return $query -> row;
	}
	public function getTotalPD($id_customer){
		$query = $this -> db -> query("
			SELECT sum(filled) AS number
			FROM  ".DB_PREFIX."customer_provide_donation 
			WHERE status = 1 AND customer_id = '".$this -> db -> escape($id_customer)."'
		");

		return $query -> row;
	}
	public function getTableCustomerMLByUsername($customer_id){
		$query = $this -> db -> query("
			SELECT *
			FROM  ".DB_PREFIX."customer_ml
			WHERE customer_id = '".$customer_id."'
		");

		return $query -> row;
	}


	public function update_pd_binary($left = true, $customer_id, $total_pd){
		if($left){
			$query = $this -> db -> query("
				UPDATE ".DB_PREFIX."customer
				SET total_pd_left = total_pd_left + ".$total_pd."
				WHERE customer_id = '".$customer_id."'
			");
		}else{
			$query = $this -> db -> query("
				UPDATE ".DB_PREFIX."customer
				SET total_pd_right = total_pd_right + ".$total_pd."
				WHERE customer_id = '".$customer_id."'
			");
		}
		return $query;
	}

	public function update_pd_mattroi($customer_id, $total_pd){
		
		$query = $this -> db -> query("
			UPDATE ".DB_PREFIX."customer
			SET total_pd_node	 = total_pd_node + ".$total_pd."
			WHERE customer_id = '".$customer_id."'
		");

		$query = $this -> db -> query("
			UPDATE ".DB_PREFIX."customer
			SET p_node_pd = p_node_pd + ".$total_pd."
			WHERE customer_id = '".$customer_id."'
		");
		
		return $query;
	}

	public function getR_Wallet_payment($id_customer){
		$query = $this -> db -> query("
			SELECT sum(amount) as amount
			FROM  ".DB_PREFIX."customer_r_wallet_payment
			WHERE customer_id = '".$this -> db -> escape($id_customer)."' AND date_end >= NOW()
		");
		return $query -> row;
	}

	public function getC_Wallet($id_customer){

		$query = $this -> db -> query("
			SELECT amount
			FROM  ".DB_PREFIX."customer_c_wallet
			WHERE customer_id = '".$this -> db -> escape($id_customer)."'
		");
		return $query -> row;
	}
	public function getCN_Wallet($id_customer){
		$query = $this -> db -> query("
			SELECT amount
			FROM  ".DB_PREFIX."customer_cn_wallet
			WHERE customer_id = '".$this -> db -> escape($id_customer)."'
		");
		return $query -> row;
	}

	public function getLikeMember($name = '', $idUserLogin){
		if($name === ''){
			$customer_query = $this->db->query("
				SELECT username AS name , customer_id AS code FROM " . DB_PREFIX . "customer WHERE customer_id <> ". $this->db->escape($idUserLogin) ."
				LIMIT 8");
			return $customer_query -> rows;
		}
		if($name !== ''){
			$customer_query = $this->db->query("
				SELECT username AS name , customer_id AS code FROM " . DB_PREFIX . "customer
				WHERE customer_id <> ". $idUserLogin ." AND username Like '%".$this->db->escape($name)."%'
				LIMIT 8");
			return $customer_query -> rows;
		}
	}

	public function getPasswdTransaction($password=''){
		if($password !== ''){
			$customer_query = $this->db->query("
				SELECT COUNT(*) AS number FROM " . DB_PREFIX . "customer
				WHERE customer_id = '". $this -> session -> data['customer_id'] ."' AND transaction_password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('" . $this->db->escape($password) . "'))))) AND status <> 0 ");
			return $customer_query -> row;
		}
	}

	public function countGdOfDay($month, $year, $day){
		$query = $this -> db -> query("
			SELECT COUNT(*) AS number
			FROM ". DB_PREFIX . "customer_get_donation
			WHERE customer_id = '".$this -> session -> data['customer_id']."'
				  AND MONTH(date_added) = '".$month."'
				  AND YEAR(date_added) = '".$year."'
				  AND DAY(date_added) = '".$day."'
		");

		return $query -> row;
	}

	public function update_C_Wallet($amount , $customer_id, $add = false){
		if(!$add){
			$query = $this -> db -> query("
			UPDATE " . DB_PREFIX . "customer_c_wallet SET
				amount = amount - ".floatval($amount)."
				WHERE customer_id = '".$customer_id."'
			");
			
		}else{

			$query = $this -> db -> query("
			UPDATE " . DB_PREFIX . "customer_c_wallet SET
				amount = amount + ".floatval($amount).",
				date_added = NOW()
				WHERE customer_id = '".$customer_id."'
			");
		}
		
		return $query === true ? true : false;
	}
	public function inser_history($text_amount, $wallet,$system_decsription,$customer_id){
		$query = $this -> db -> query("
			INSERT INTO ". DB_PREFIX . "customer_transaction_history SET
			text_amount = '".$text_amount."',
			date_added = NOW(),
			wallet = '".$wallet."',
			system_decsription = '".$system_decsription."',
			customer_id = '".$customer_id."'
		");
		return $this->db->getLastId();
	}
	public function update_R_Wallet($amount , $customer_id, $add = false){
		if(!$add){
			$query = $this -> db -> query("
			UPDATE " . DB_PREFIX . "customer_r_wallet SET
				amount = amount - ".floatval($amount)."
				WHERE customer_id = '".$customer_id."'
			");
		}else{
			$query = $this -> db -> query("
			UPDATE " . DB_PREFIX . "customer_r_wallet SET
				amount = amount + ".floatval($amount)."
				WHERE customer_id = '".$customer_id."'
			");
		}
		return $query === true ? true : false;
	}

	public function createGD($amount){
		$this -> db -> query("
			INSERT INTO ". DB_PREFIX . "customer_get_donation SET
			customer_id = '".$this -> session -> data['customer_id']."',
			date_added = NOW(),
			amount = '".$amount."',
			status = 0
		");

		$gd_id = $this->db->getLastId();

		$gd_number = hexdec(crc32($gd_id));

		$query = $this -> db -> query("
			UPDATE " . DB_PREFIX . "customer_get_donation SET
				gd_number = '".$gd_number."'
				WHERE id = '".$gd_id."'
			");
		$data['query'] = $query ? true : false;
		$data['gd_number'] = $gd_number;
		return $data;
	}

	public function editPasswordCustomForEmail($data, $password) {
		$this -> event -> trigger('pre.customer.edit.password');
		$customer_id = $data['customer_id'];
		$salt = $data['salt'];

		$this -> db -> query("UPDATE " . DB_PREFIX . "customer SET
			password = '" . $this -> db -> escape(sha1($salt . sha1($salt . sha1($password)))) . "'
			WHERE customer_id = '" . $this -> db -> escape($customer_id) . "'");

		$this -> event -> trigger('post.customer.edit.password');
	}
	public function getCustomLike($name, $id_user) {
		$listId = '';
		$query = $this -> db -> query("
			SELECT c.username AS name, c.customer_id AS code FROM ". DB_PREFIX ."customer AS c
			JOIN ". DB_PREFIX ."customer_ml AS ml
			ON ml.customer_id = c.customer_id
			WHERE ml.p_node = ". $id_user ." AND c.username Like '%".$this->db->escape($name)."%'");
		$array_id = $query -> rows;
		foreach ($array_id as $item) {
			$listId .= ',' . $item['name'];
			$listId .= $this -> getCustomLike($name,$item['code']);
		}
		return $listId;
	}
	public function checkUserName($id_user) {
		$listId = '';
		$query = $this -> db -> query("
			SELECT c.username AS name, c.customer_id AS code FROM ". DB_PREFIX ."customer AS c
			JOIN ". DB_PREFIX ."customer_ml AS ml
			ON ml.customer_id = c.customer_id
			WHERE ml.p_node = ". $id_user ."");
		$array_id = $query -> rows;
		foreach ($array_id as $item) {
			$listId .= ',' . $item['name'];
			$listId .= $this -> checkUserName($item['code']);
		}
		return $listId;
	}


	public function getTotalGD($id_customer){
		$query = $this -> db -> query("
			SELECT COUNT( * ) AS number
			FROM  ".DB_PREFIX."customer_get_donation
			WHERE customer_id = '".$this -> db -> escape($id_customer)."'
		");

		return $query -> row;
	}
	
	public function getGDById($id_customer, $limit, $offset){
		$query = $this -> db -> query("
			SELECT *
			FROM  ".DB_PREFIX."customer_get_donation
			WHERE customer_id = '".$this -> db -> escape($id_customer)."'
			ORDER BY date_added DESC
			LIMIT ".$limit."
			OFFSET ".$offset."
		");

		return $query -> rows;
	}

	public function checkpasswd($password=''){
		if($password !== ''){
			$customer_query = $this->db->query("
				SELECT COUNT(*) AS number FROM " . DB_PREFIX . "customer
				WHERE customer_id = '". $this -> session -> data['customer_id'] ."' AND password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('" . $this->db->escape($password) . "'))))) AND status <> 0 ");
			return $customer_query -> row;
		}
	}

	public function updatePin($id_customer, $pin){

		$this -> event -> trigger('pre.customer.edit', $data);
		$this -> db -> query("
			UPDATE " . DB_PREFIX . "customer SET
			ping = '" . $this -> db -> escape((int)$pin) . "'
			WHERE customer_id = '" . (int)$id_customer . "'");

		$this -> event -> trigger('post.customer.edit', $id_customer);

	}

	public function updateStatus($id_customer,  $status){
		if($id_customer && $status){
			$query =  $this -> db -> query("
				UPDATE " . DB_PREFIX . "customer SET
				status = '" . $this -> db -> escape((int)$status) . "'
				WHERE customer_id = '" . (int)$id_customer. "'");
			if($query){
				$query =  $this -> db -> query("
				UPDATE " . DB_PREFIX . "customer_ml SET
				status = '" . $this -> db -> escape((int)$status) . "'
				WHERE customer_id = '" . (int)$id_customer. "'");
			}else{
				$query = false;
			}

			return $query;
		}
	}

	public function getLevel($customer_id, $level){
		$query =  $this -> db -> query("
			SELECT * 
					FROM " . DB_PREFIX . "customer_ml
					WHERE customer_id
					IN ( SELECT customer_id FROM " . DB_PREFIX . "customer WHERE p_node = ".$customer_id." )
					AND level = ".$level."
					GROUP BY customer_id");
		return $query -> rows;
	}

	public function updateLevel($customer_id, $level){
		$query =  $this -> db -> query("
				UPDATE " . DB_PREFIX . "customer_ml SET
				level = ".$level."
				WHERE customer_id = '" . (int)$customer_id. "'");
		return $query;
	}

	public function updateCheckNEwuser($id_customer){
		if($id_customer){
			$query =  $this -> db -> query("
				UPDATE " . DB_PREFIX . "customer SET
				check_Newuser = 0
				WHERE customer_id = '" . (int)$id_customer. "'");
			return $query;
		}
	}
	


	public function saveHistoryPin($id_customer, $amount, $user_description, $type , $system_description){
		$date_added= date('Y-m-d H:i:s') ;
		$date_finish = strtotime ( '+30 day' , strtotime ( $date_added ) ) ;
		$date_finish= date('Y-m-d H:i:s',$date_finish) ;

		$this -> db -> query("INSERT INTO " . DB_PREFIX . "ping_history SET
			id_customer = '" . $this -> db -> escape($id_customer) . "',
			amount = '" . $this -> db -> escape( $amount ) . "',
			date_added = '".$date_added."',
			user_description = '" .$this -> db -> escape($user_description). "',
			type = '" .$this -> db -> escape($type). "',
			system_description = '" .$this -> db -> escape($system_description). "'
		");
		return $this -> db -> getLastId();
	}

	public function getTotalRefferalByID($id_customer){

		$query = $this -> db -> query("
			SELECT COUNT( * ) AS number
			FROM ".DB_PREFIX."customer_ml
			WHERE p_node =  '".$this -> db -> escape($id_customer)."'
		");

		return $query -> row;
	}

	public function getRefferalByID($id_customer ,$limit, $offset){
		$query = $this -> db -> query("
			SELECT c.email , c.username,c.telephone,c.cmnd,c.wallet,c.country_id, c.customer_id, ml.level, c.date_added
			FROM ".DB_PREFIX."customer_ml AS ml
			JOIN ". DB_PREFIX ."customer AS c
			ON ml.customer_id = c.customer_id
			WHERE ml.p_node =  '".$this -> db -> escape($id_customer)."'
			ORDER BY ml.level DESC
			LIMIT ".$limit."
			OFFSET ".$offset."
		");

		return $query -> rows;
	}

	public function getRefferalByID_customer($id_customer){
		$query = $this -> db -> query("
			SELECT c.email , c.username,c.telephone,c.cmnd,c.wallet,c.country_id, c.customer_id, ml.level, c.date_added
			FROM ".DB_PREFIX."customer_ml AS ml
			JOIN ". DB_PREFIX ."customer AS c
			ON ml.customer_id = c.customer_id
			WHERE ml.customer_id =  '".$this -> db -> escape($id_customer)."'
		");

		return $query -> rows;
	}

	public function getTotalTokenHistory($id_customer){
		$query = $this -> db -> query("
			SELECT COUNT( * ) AS number
			FROM  ".DB_PREFIX."ping_history
			WHERE id_customer = ".$this -> db -> escape($id_customer)." AND amount <> '- 0' AND amount <> '+ 0'
		");

		return $query -> row;
	}

	public function getTokenHistoryById($id_customer, $limit, $offset){
		$query = $this -> db -> query("
			SELECT *
			FROM  ".DB_PREFIX."ping_history
			WHERE id_customer = ".$this -> db -> escape($id_customer)." AND amount <> '- 0' AND amount <> '+ 0'
			ORDER BY date_added DESC
			LIMIT ".$limit."
			OFFSET ".$offset."

		");

		return $query -> rows;
	}

	public function editCustomerWallet($wallet) {



		$data['wallet'] = $wallet;
		$this -> event -> trigger('pre.customer.edit', $data);
		$customer_id = $this -> customer -> getId();

		$getCustomer = $this -> getCustomer($customer_id);
		$this -> db -> query("UPDATE " . DB_PREFIX . "customer 
				SET wallet = '". $wallet ."'
				WHERE customer_id = '" . (int)$customer_id . "'");
		
		
		$this -> event -> trigger('post.customer.edit', $customer_id);
	}

	public function editCustomerBanks($data) {

		$data_arr = $data;
		$this -> event -> trigger('pre.customer.edit', $data_arr);
		$customer_id = $this -> customer -> getId();
		$this -> db -> query("UPDATE " . DB_PREFIX . "customer SET account_holder = '". $data_arr['account_holder'] ."',bank_name = '". $data_arr['bank_name'] ."',account_number = '". $data_arr['account_number'] ."',branch_bank = '". $data_arr['branch_bank'] ."' WHERE customer_id = '" . (int)$customer_id . "'");
		$this -> event -> trigger('post.customer.edit', $customer_id);
	}
	public function editCustomerProfile($data_arr) {
		$this -> event -> trigger('pre.customer.edit', $data_arr);
		$customer_id = $this -> customer -> getId();
		$this -> db -> query("UPDATE " . DB_PREFIX . "customer SET country_id = '". $data_arr['country_id'] ."',email = '". $data_arr['email'] ."',telephone = '". $data_arr['telephone'] ."',account_holder = '". $data_arr['account_holder'] ."',branch_bank = '". $data_arr['branch_bank'] ."' WHERE customer_id = '" . (int)$customer_id . "'");
		$this -> event -> trigger('post.customer.edit', $customer_id);
	}

	public function editCustomer($data) {

		$this -> event -> trigger('pre.customer.edit', $data);

		$customer_id = $this -> customer -> getId();

		$this -> db -> query("UPDATE " . DB_PREFIX . "customer SET firstname = '" . $this -> db -> escape($data['firstname']) . "', lastname = '" . $this -> db -> escape($data['lastname']) . "', email = '" . $this -> db -> escape($data['email']) . "', telephone = '" . $this -> db -> escape($data['telephone']) . "', account_bank = '" . $this -> db -> escape($data['account_bank']) . "', address_bank = '" . $this -> db -> escape($data['address_bank']) . "', custom_field = '" . $this -> db -> escape(isset($data['custom_field']) ? serialize($data['custom_field']) : '') . "' WHERE customer_id = '" . (int)$customer_id . "'");

		$this -> event -> trigger('post.customer.edit', $customer_id);
	}

	public function editCustomerCusotm($data) {


		$this -> event -> trigger('pre.customer.edit', $data);

		$customer_id = $this -> customer -> getId();
		$this -> db -> query("
			UPDATE " . DB_PREFIX . "customer SET
			email = '" . $this -> db -> escape($data['email']) . "',
			telephone = '" . $this -> db -> escape($data['telephone']) . "'
			WHERE customer_id = '" . (int)$customer_id . "'");

		$this -> event -> trigger('post.customer.edit', $customer_id);
	}

	public function editPasswordCustom($password) {
		$this -> event -> trigger('pre.customer.edit.password');
		$customer_id = $this -> customer -> getId();

		$salt = $this -> getCustomer($customer_id);
		$salt = $salt['salt'];

		$this -> db -> query("UPDATE " . DB_PREFIX . "customer SET
			password = '" . $this -> db -> escape(sha1($salt . sha1($salt . sha1($password)))) . "'
			WHERE customer_id = '" . $this -> db -> escape($customer_id) . "'");

		$this -> event -> trigger('post.customer.edit.password');
	}

	public function editPasswordTransactionCustom($password) {
		$this -> event -> trigger('pre.customer.edit.password');
		$customer_id = $this -> customer -> getId();

		$salt = $this -> getCustomer($customer_id);
		$salt = $salt['salt'];

		$this -> db -> query("UPDATE " . DB_PREFIX . "customer SET
			transaction_password = '" . $this -> db -> escape(sha1($salt . sha1($salt . sha1($password)))) . "'
			WHERE customer_id = '" . $this -> db -> escape($customer_id) . "'");

		$this -> event -> trigger('post.customer.edit.password');
	}

	public function editPassword($email, $password) {
		$this -> event -> trigger('pre.customer.edit.password');

		$this -> db -> query("UPDATE " . DB_PREFIX . "customer SET salt = '" . $this -> db -> escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this -> db -> escape(sha1($salt . sha1($salt . sha1($password)))) . "' WHERE LOWER(email) = '" . $this -> db -> escape(utf8_strtolower($email)) . "'");

		$this -> event -> trigger('post.customer.edit.password');
	}

	public function editNewsletter($newsletter) {
		$this -> event -> trigger('pre.customer.edit.newsletter');

		$this -> db -> query("UPDATE " . DB_PREFIX . "customer SET newsletter = '" . (int)$newsletter . "' WHERE customer_id = '" . (int)$this -> customer -> getId() . "'");

		$this -> event -> trigger('post.customer.edit.newsletter');
	}

	public function getCustomer($customer_id) {
		$query = $this -> db -> query("SELECT c.* FROM " . DB_PREFIX . "customer c  WHERE c.customer_id = '" . (int)$customer_id . "'");
		return $query -> row;
	}
	public function getCustomer_by_ml($customer_id) {
		$query = $this -> db -> query("SELECT c.*,A.position,F.name as name_country,F.iso_code_2 as code_country FROM " . DB_PREFIX . "customer c INNER JOIN " . DB_PREFIX  ."customer_ml A ON A.customer_id = c.customer_id LEFT JOIN " . DB_PREFIX . "country F ON c.country_id = F.country_id WHERE c.customer_id = '" . (int)$customer_id . "'");
		return $query -> row;
	}
	public function getCustomerbyCode($customer_id) {
		$query = $this -> db -> query("SELECT c.* FROM " . DB_PREFIX . "customer c  WHERE c.customer_code = '" . $customer_id . "'");
		return $query -> row;
	}

	public function getCustomerPDForPD($p_node) {
		$query = $this -> db -> query("
			SELECT c.customer_id 
			FROM " . DB_PREFIX . "customer c  
			JOIN sm
			WHERE c.p_node = '" . (int)$p_node . "'"
		);
		return $query -> row;
	}

	public function getTotalHistory($customer_id){
		$query = $this -> db -> query("
			SELECT count(*) AS number 
			FROM ".DB_PREFIX."customer_transaction_history
			WHERE customer_id = '".intval($customer_id)."' AND wallet LIKE 'Weekly profit'
		");

		return $query -> row;
	}

	

	public function getTotalHistory_reffernal($customer_id){
		$query = $this -> db -> query("
			SELECT count(*) AS number 
			FROM ".DB_PREFIX."customer_transaction_history
			WHERE customer_id = '".intval($customer_id)."' AND wallet = 'Hoa hồng trực tiếp'
		");

		return $query -> row;
	}
	public function getTransctionHistory_reffernal($id_customer, $limit, $offset){
		$query = $this -> db -> query("
			SELECT *
			FROM  ".DB_PREFIX."customer_transaction_history
			WHERE customer_id = '".$this -> db -> escape($id_customer)."' AND wallet = 'Hoa hồng trực tiếp' 
			ORDER BY date_added DESC
			LIMIT ".$limit."
			OFFSET ".$offset."
		");
		
		return $query -> rows;
	}


	

	public function get_history_active_package($id_customer){
		$query = $this -> db -> query("
			SELECT *
			FROM  ".DB_PREFIX."customer_transaction_history
			WHERE customer_id = '".$this -> db -> escape($id_customer)."' AND wallet = 'Active Package' 
			ORDER BY date_added DESC
			
		");
		
		return $query -> rows;
	}

	public function getTotalHistory_matching($customer_id){
		$query = $this -> db -> query("
			SELECT count(*) AS number 
			FROM ".DB_PREFIX."customer_transaction_history
			WHERE customer_id = '".intval($customer_id)."' AND wallet = 'Commision Resonance'
		");

		return $query -> row;
	}
	public function getTransctionHistory_matching($id_customer, $limit, $offset){
		$query = $this -> db -> query("
			SELECT *
			FROM  ".DB_PREFIX."customer_transaction_history
			WHERE customer_id = '".$this -> db -> escape($id_customer)."' AND wallet = 'Commision Resonance' 
			ORDER BY date_added DESC
			LIMIT ".$limit."
			OFFSET ".$offset."
		");
		
		return $query -> rows;
	}

	public function getTotalHistory_binary($customer_id){
		$query = $this -> db -> query("
			SELECT count(*) AS number 
			FROM ".DB_PREFIX."customer_transaction_history
			WHERE customer_id = '".intval($customer_id)."' AND wallet LIKE 'Hoa hồng đội nhóm'
		");

		return $query -> row;
	}

	public function getTransctionHistory_binary($id_customer, $limit, $offset){
		$query = $this -> db -> query("
			SELECT *
			FROM  ".DB_PREFIX."customer_transaction_history
			WHERE customer_id = '".$this -> db -> escape($id_customer)."' AND wallet LIKE 'Hoa hồng đội nhóm'
			ORDER BY date_added DESC
			LIMIT ".$limit."
			OFFSET ".$offset."
		");
		
		return $query -> rows;
	}
	public function getTotalHistory_binary_new($customer_id){
		$query = $this -> db -> query("
			SELECT count(*) AS number 
			FROM ".DB_PREFIX."customer_transaction_history
			WHERE customer_id = '".intval($customer_id)."' AND wallet LIKE 'System Commission'
		");

		return $query -> row;
	}
	public function getTransctionHistory_binary_new($id_customer, $limit, $offset){
		$query = $this -> db -> query("
			SELECT *
			FROM  ".DB_PREFIX."customer_transaction_history
			WHERE customer_id = '".$this -> db -> escape($id_customer)."' AND wallet LIKE 'System Commission' 
			ORDER BY date_added DESC
			LIMIT ".$limit."
			OFFSET ".$offset."
		");
		
		return $query -> rows;
	}
	public function getTransctionHistory($id_customer, $limit, $offset){
		$query = $this -> db -> query("
			SELECT *
			FROM  ".DB_PREFIX."customer_transaction_history
			WHERE customer_id = '".$this -> db -> escape($id_customer)."' AND wallet = 'Weekly profit' 
			ORDER BY date_added DESC
			LIMIT ".$limit."
			OFFSET ".$offset."
		");
		
		return $query -> rows;
	}

	public function getCustomerCustom($customer_id) {
		$query = $this -> db -> query("SELECT c.username, c.telephone, c.customer_id , ml.level FROM ". DB_PREFIX ."customer AS c
				JOIN ". DB_PREFIX ."customer_ml AS ml
				ON ml.customer_id = c.customer_id
				WHERE c.customer_id = '" . (int)$customer_id . "'");
		return $query -> row;
	}

	public function getCustomerBank($customer_id) {
		$query = $this -> db -> query("SELECT *  FROM ". DB_PREFIX ."customer WHERE customer_id = '" . (int)$customer_id . "'");
		return $query -> row;
	}

	public function editPasswordTransactionCustomForEmail($data, $password) {
		$this -> event -> trigger('pre.customer.edit.password');
		$customer_id = $data['customer_id'];
		$salt = $data['salt'];
		$this -> db -> query("UPDATE " . DB_PREFIX . "customer SET
			transaction_password = '" . $this -> db -> escape(sha1($salt . sha1($salt . sha1($password)))) . "'
			WHERE customer_id = '" . $this -> db -> escape($customer_id) . "'");

		$this -> event -> trigger('post.customer.edit.password');
	}

	public function getCustomerCustomFormSetting($customer_id) {
		$query = $this -> db -> query("SELECT c.firstname,c.address_cmnd,
			ip.date_added as date_add_login,ip.ip, date(c.date_added) as date_added,c.username, 
			c.telephone , c.email , wl.wallet , ml.level,ct.name as countryname 
			FROM ". DB_PREFIX ."customer AS c
				JOIN ". DB_PREFIX ."customer_ml AS ml 
				ON ml.customer_id = c.customer_id JOIN ". DB_PREFIX ."customer_activity ip ON ip.customer_id = c.customer_id
				JOIN sm_country ct ON ct.country_id = c.country_id JOIN ". DB_PREFIX ."customer_wallet_btc_ wl ON c.customer_id = wl.customer_id
				WHERE c.customer_id = '" . (int)$customer_id . "'");
		return $query -> row;
	}

	public function getUserOff($listIdChild) {
		if ($listIdChild != '') {
			$query = $this -> db -> query("SELECT c.* FROM " . DB_PREFIX . "customer c  WHERE c.customer_id IN (" . $listIdChild . ") AND c.status = 0");
			return $query -> rows;
		}
		return array();
	}

	public function getUserNotHP($listIdChild) {
		if ($listIdChild != '') {
			$date = strtotime(date('Y-m-d'));
			$month = date('m', $date);
			$year = date('Y', $date);
			$arrNotHP = array();
			$query = $this -> db -> query("SELECT c.* FROM " . DB_PREFIX . "customer c  WHERE c.customer_id IN (" . $listIdChild . ") AND c.status = 1");
			$arrUser = $query -> rows;
			foreach ($arrUser as $user) {
				$query = $this -> db -> query("SELECT * FROM " . DB_PREFIX . "profit WHERE  user_id = " . $user['customer_id'] . " and type_profit = 1 and year = '" . $year . "' AND month = '" . $month . "'");
				if (!$query -> row) {
					array_push($arrNotHP, $user);
				}
			}
			return $arrNotHP;
		} else {
			return array();
		}
	}

	public function getListChild($id_package) {
		$query = $this -> db -> query("SELECT cm.*,c.username,c.telephone,c.status AS status_cus,c.firstname,c.cmnd,CONCAT(c.firstname, ' ', c.lastname) as name_customer,ml.name_vn as package_vn FROM " . DB_PREFIX . "customer_ml cm LEFT JOIN " . DB_PREFIX . "customer c ON (c.customer_id = cm.customer_id) LEFT JOIN " . DB_PREFIX . "member_level ml ON (cm.level = ml.id)  WHERE cm.p_node = '" . (int)$id_package . "'");

		return $query -> rows;
	}

	public function getListChildCustom($id_package) {
		$query = $this -> db -> query("
				SELECT cm.level, c.username, c.telephone , c.customer_id
				FROM ". DB_PREFIX ."customer_ml cm LEFT JOIN ". DB_PREFIX ."customer c ON (c.customer_id = cm.customer_id)
				WHERE cm.p_node = '2'
			");

		return $query -> rows;
	}

	public function getListChildNotPackage($id_user) {
		$id_user = $id_user * (-1);
		$query = $this -> db -> query("SELECT cm.*,c.username,c.firstname,c.cmnd,CONCAT(c.firstname, ' ', c.lastname) as name_customer,ml.name_vn as package_vn FROM " . DB_PREFIX . "customer_ml cm LEFT JOIN " . DB_PREFIX . "customer c ON (c.customer_id = cm.customer_id) LEFT JOIN " . DB_PREFIX . "member_level ml ON (cm.level = ml.id)  WHERE cm.p_node = '" . $id_user . "'");

		return $query -> rows;
	}

	public function getCustomerByEmail($email) {
		$query = $this -> db -> query("SELECT * FROM " . DB_PREFIX . "customer WHERE LOWER(email) = '" . $this -> db -> escape(utf8_strtolower($email)) . "'");

		return $query -> row;
	}

	public function getCustomerByUsername($username) {
		$query = $this -> db -> query("SELECT * FROM " . DB_PREFIX . "customer WHERE LOWER(username) = '" . $this -> db -> escape(utf8_strtolower($username)) . "'");

		return $query -> row;
	}

	public function getCustomerByToken($token) {
		$query = $this -> db -> query("SELECT * FROM " . DB_PREFIX . "customer WHERE token = '" . $this -> db -> escape($token) . "' AND token != ''");

		$this -> db -> query("UPDATE " . DB_PREFIX . "customer SET token = ''");

		return $query -> row;
	}

	public function getTotalCustomersById($customer_id) {
		$query = $this -> db -> query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customer_id . "'");

		return $query -> row['total'];
	}

	public function getTotalCustomersByEmail($email) {
		$query = $this -> db -> query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer WHERE LOWER(email) = '" . $this -> db -> escape(utf8_strtolower($email)) . "'");

		return $query -> row['total'];
	}

	public function getTotalCustomersByTelephone($telephone) {
		$query = $this -> db -> query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer WHERE LOWER(telephone) = REPLACE('" . $this -> db -> escape(utf8_strtolower($telephone)) . "'" . ", ' ', '')");

		return $query -> row['total'];
	}

	public function getIps($customer_id) {
		$query = $this -> db -> query("SELECT * FROM `" . DB_PREFIX . "customer_ip` WHERE customer_id = '" . (int)$customer_id . "'");

		return $query -> rows;
	}

	public function isBanIp($ip) {
		$query = $this -> db -> query("SELECT * FROM `" . DB_PREFIX . "customer_ban_ip` WHERE ip = '" . $this -> db -> escape($ip) . "'");

		return $query -> num_rows;
	}

	public function addLoginAttempt($email) {
		$query = $this -> db -> query("SELECT * FROM " . DB_PREFIX . "customer_login WHERE email = '" . $this -> db -> escape(utf8_strtolower((string)$email)) . "' AND ip = '" . $this -> db -> escape($this -> request -> server['REMOTE_ADDR']) . "'");

		if (!$query -> num_rows) {
			$this -> db -> query("INSERT INTO " . DB_PREFIX . "customer_login SET email = '" . $this -> db -> escape(utf8_strtolower((string)$email)) . "', ip = '" . $this -> db -> escape($this -> request -> server['REMOTE_ADDR']) . "', total = 1, date_added = '" . $this -> db -> escape(date('Y-m-d H:i:s')) . "', date_modified = '" . $this -> db -> escape(date('Y-m-d H:i:s')) . "'");
		} else {
			$this -> db -> query("UPDATE " . DB_PREFIX . "customer_login SET total = (total + 1), date_modified = '" . $this -> db -> escape(date('Y-m-d H:i:s')) . "' WHERE customer_login_id = '" . (int)$query -> row['customer_login_id'] . "'");
		}
	}

	public function getLoginAttempts($email) {
		$query = $this -> db -> query("SELECT * FROM `" . DB_PREFIX . "customer_login` WHERE email = '" . $this -> db -> escape(utf8_strtolower($email)) . "'");

		return $query -> row;
	}

	public function deleteLoginAttempts($email) {
		$this -> db -> query("DELETE FROM `" . DB_PREFIX . "customer_login` WHERE email = '" . $this -> db -> escape(utf8_strtolower($email)) . "'");
	}

	public function getPackages($customer_id) {
		$query = $this -> db -> query("SELECT cm.*,ml.name_vn AS package_vn FROM " . DB_PREFIX . "customer_ml cm LEFT JOIN " . DB_PREFIX . "member_level ml ON (cm.level = ml.id) WHERE cm.customer_id = '" . (int)$customer_id . "' ORDER BY cm.date_added");

		return $query -> rows;
	}

	public function getInfoPackages($id_package) {
		$query = $this -> db -> query("SELECT cm.*,ml.name_vn AS package_vn,c.username,c.firstname FROM " . DB_PREFIX . "customer_ml cm LEFT JOIN " . DB_PREFIX . "customer c ON (c.customer_id = cm.customer_id) LEFT JOIN " . DB_PREFIX . "member_level ml ON (cm.level = ml.id) WHERE cm.id_package = '" . (int)$id_package . "'");

		return $query -> row;
	}

	public function getNameParent($customer_id) {
		$query = $this -> db -> query("SELECT c.firstname AS name_parent FROM " . DB_PREFIX . "customer_ml cm LEFT JOIN " . DB_PREFIX . "customer c ON (c.customer_id = cm.customer_id) WHERE cm.customer_id = '" . (int)$customer_id . "'");
		if (isset($query -> row['name_parent'])) {
			return $query -> row['name_parent'];
		} else
			return "";
	}

	public function getMonthRegister($customer_id) {
		$date = strtotime(date('Y-m-d'));
		$yearNow = date('Y', $date);
		$monthNow = date('m', $date);
		$query = $this -> db -> query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customer_id . "'");
		$rowCus = $query -> row;
		$dateRegis = strtotime($rowCus['date_added']);
		$yearRegis = date('Y', $dateRegis);
		$monthRegis = date('m', $dateRegis);
		$numYear = $yearNow - $yearRegis;
		if ($numYear > 0) {
			$monthNow = $monthNow + (12 * $numYear);
		}
		return $monthNow - $monthRegis;
	}

	public function getAllProfitByType($user_id, $type) {
		$query = $this -> db -> query("SELECT SUM(receive) AS total FROM " . DB_PREFIX . "profit WHERE user_id = '" . (int)$user_id . "' and type_profit in (" . $type . ")");
		return $query -> row['total'];
	}

	public function countProfitByType($user_id, $type) {
		$query = $this -> db -> query("SELECT count(*) AS total FROM " . DB_PREFIX . "profit WHERE user_id = '" . (int)$user_id . "' and type_profit in (" . $type . ")");
		return $query -> row['total'];
	}

	function getBParent($id) {
		$query = $this -> db -> query("select p_binary from " . DB_PREFIX . "customer as u1 INNER join " . DB_PREFIX . "customer_ml AS u2 ON u1.customer_id = u2.customer_id where u1.customer_id = " . (int)$id);
		return $query -> row['p_binary'];
	}

	function getInfoUsers($id_ids) {
		if (!is_array($id_ids))
			$ids = array($id_ids);
		else
			$ids = $id_ids;
		$array_id = "( " . implode(',', $ids) . " )";
		$query = $this -> db -> query("select u.*,mlm.level, l.name_vn as level_member from " . DB_PREFIX . "customer as u Left Join " . DB_PREFIX . "customer_ml as mlm ON mlm.customer_id = u.customer_id  Left Join " . DB_PREFIX . "member_level as l ON l.id = mlm.level  Where u.customer_id IN " . $array_id);
		if (!is_array($id_ids)) {
			$return = $query -> row;
		} else {
			$return = $query -> rows;
		}
		return $return;
	}

	//	lay tong so thanh vien
	function getSumNumberMember($node) {
		$result = 0;
		return $result;
	}

	function getLeftO($id) {
		$query = $this -> db -> query('select u2.email,u2.firstname, u2.telephone, u2.date_added, mlm.customer_id as id, mlm.level,CONCAT(u2.firstname," (ĐT: ",u2.telephone,")") as text, CONCAT( "level1"," left") as iconCls,CONCAT(u2.firstname," (ĐT: ",u2.telephone,")") as name,l.name_vn as level_user,u2.username,u2.status,u2.date_added,u2.p_node  from ' . DB_PREFIX . 'customer AS u2 LEFT join ' . DB_PREFIX . 'customer_ml AS mlm ON u2.customer_id = mlm.customer_id INNER join ' . DB_PREFIX . 'customer_ml AS u1 ON u1.left = mlm.customer_id left Join ' . DB_PREFIX . 'member_level as l ON l.id = mlm.level where mlm.p_binary = ' . (int)$id);
		//	return json_decode(json_encode($query->row), false);
		return $query -> row;
	}

	function getRightO($id) {
		$query = $this -> db -> query('select u2.email, u2.telephone,u2.date_added,u2.firstname, mlm.customer_id as id, mlm.level,CONCAT(u2.firstname," (ĐT: ",u2.telephone,")") as text, CONCAT( "level1"," right") as iconCls,CONCAT(u2.firstname," (ĐT: ",u2.telephone,")") as name,l.name_vn as level_user,u2.username,u2.status,u2.date_added,u2.p_node from ' . DB_PREFIX . 'customer AS u2 LEFT join ' . DB_PREFIX . 'customer_ml AS mlm ON u2.customer_id = mlm.customer_id INNER join ' . DB_PREFIX . 'customer_ml AS u1 ON u1.right = mlm.customer_id left Join ' . DB_PREFIX . 'member_level as l ON l.id = mlm.level where mlm.p_binary = ' . (int)$id);
		//return json_decode(json_encode($query->row), false);
		return $query -> row;
	}

	function getLeft($id) {
		$query = $this -> db -> query("select u2.left from " . DB_PREFIX . "customer as u1 
			INNER JOIN " . DB_PREFIX . "customer_ml AS u2 ON u1.customer_id = u2.customer_id 
			where u1.customer_id = " . (int)$id);
		return null;
	}

	function getRight($id) {
		$query = $this -> db -> query("select u2.right from " . DB_PREFIX . "customer as u1 INNER JOIN " . DB_PREFIX . "customer_ml AS u2 ON u1.customer_id = u2.customer_id where u1.customer_id = " . (int)$id);
		return null;
	}

	function getSumLeft($id) {
		$result = 0;
		$left = $this -> getLeft($id);
		if ($left) {
			$result += 1;
			$result += $this -> getSumMember($left);
		}
		return $result;
	}

	//Get sum right node binarytree
	function getSumRight($id) {
		$result = 0;
		$right = $this -> getRight($id);
		if ($right) {
			$result += 1;
			$result += $this -> getSumMember($right);
		}
		return $result;
	}

	//Get sum left node and right node for any node bynary
	function getSumMember($id) {

		$result = 0;
		$left = $this -> getLeft($id);
		$right = $this -> getRight($id);
		if ($left) {
			$result += 1;
			$result += $this -> getSumMember($left);
		}
		if ($right) {
			$result += 1;
			$result += $this -> getSumMember($right);
		}

		//print_r($result);
		return $result;
	}

	function getSumFloor($arrId) {
		$floor = 0;
		$query = $this -> db -> query("select mlm.customer_id from " . DB_PREFIX . "customer as u Left Join " . DB_PREFIX . "customer_ml as mlm ON mlm.customer_id = u.customer_id  Where mlm.p_binary IN (" . $arrId . ")");
		$arrChild = $query -> rows;

		if (!empty($arrChild)) {
			$floor += 1;
			$arrId = '';
			foreach ($arrChild as $child) {
				$arrId .= ',' . $child['customer_id'];
			}
			$arrId = substr($arrId, 1);
			$floor += $this -> getSumFloor($arrId);
		}
		return $floor;
	}



	function checkActiveUser($id_user = 0) {
		$query = $this -> db -> query("select u1.status from " . DB_PREFIX . "customer as u1 where u1.customer_id = " . (int)$id_user);
		return $query -> row['status'];
	}

	function getCountTreeCustom($id_user) {
		$listId = 0;
		$query = $this -> db -> query("select customer_id from " . DB_PREFIX . "customer_ml where p_node = " . (int)$id_user);
		$array_id = $query -> rows;
		foreach ($array_id as $item) {
			$listId ++;
			$listId = $listId + $this -> getCountTreeCustom($item['customer_id']);
		}
		return $listId;
	}

	function getCountBinaryTreeCustom($id_user) {
		$listId =0 ;
		$query = $this -> db -> query("select customer_id from " . DB_PREFIX . "customer_ml where p_binary = " . (int)$id_user);
		$array_id = $query -> rows;
		foreach ($array_id as $item) {
			$listId ++;
			$listId = $listId + $this -> getCountBinaryTreeCustom($item['customer_id']);
		}
		return $listId;
	}


	function getCount_ID_BinaryTreeCustom($id_user) {
		$listId = '';
		$query = $this -> db -> query("select customer_id from " . DB_PREFIX . "customer_ml where p_binary = " . (int)$id_user);
		$array_id = $query -> rows;
		foreach ($array_id as $item) {
			$listId .= ','.$item['customer_id'];
			$listId .= $this -> getCount_ID_BinaryTreeCustom($item['customer_id']);
		}
		return $listId;
	}



	function getCountLevelCustom($id_user, $level) {
		$listId = 0;

		$query = $this -> db -> query("select customer_id , level from " . DB_PREFIX . "customer_ml where p_node = " . (int)$id_user);
		$array_id = $query -> rows;

		foreach ($array_id as $item) {
			intval($item['level']) === intval($level) && $listId ++;
			$listId = $listId + $this -> getCountLevelCustom($item['customer_id'], $level);
		}
		return $listId;
	}

	function getListIdChild($id_user) {
		$listId = '';
		$query = $this -> db -> query("select customer_id from " . DB_PREFIX . "customer_ml 
			where p_binary = " . (int)$id_user);
		$array_id = $query -> rows;

		foreach ($array_id as $item) {
			$listId .= ',' . $item['customer_id'];
			$listId .= $this -> getListIdChild($item['customer_id']);
		}
		return $listId;
	}

	function getListCTP($id_user) {
		$dateEnd = date("Y-m-d H:i:s");
		$monthEnd = date('m', strtotime($dateEnd));
		$yearEnd = date('Y', strtotime($dateEnd));
		$arrCTP = array();
		$query = $this -> db -> query("select * from " . DB_PREFIX . "customer where customer_id = " . (int)$id_user);
		$infoUser = $query -> row;
		$dateStar = $infoUser['date_added'];

		$monthRegister = $this -> getMonthRegister($id_user);
		$numHP = $this -> countProfitByType($id_user, 1);
		$config_congtacphi = $this -> config -> get('config_congtacphi');
		for ($n = 1; $n <= 12; $n++) {
			$monthStar = date('m', strtotime($dateStar));
			$yearStar = date('Y', strtotime($dateStar));
			if ($monthStar == "12") {
				$monthNext = 1;
				$yearNext = $yearStar + 1;
			} else {
				$monthNext = $monthStar + 1;
				$yearNext = $yearStar;
			}
			$dateNext = date("Y-m-d", strtotime("01-" . $monthNext . "-" . $yearNext));
			if (strtotime($dateNext) <= strtotime($dateEnd)) {
				$node = new stdClass();
				$queryHVTT = $this -> db -> query("select count(*) AS total from " . DB_PREFIX . "customer_ml where p_binary = " . (int)$id_user . " AND date_added >= '" . $dateStar . "' AND date_added < '" . $dateNext . "'");
				$numHVTT = $queryHVTT -> row['total'];
				$CTP_HVTT = $numHVTT * $config_congtacphi;
				$node -> numHVTT = $numHVTT;
				$node -> CTP_HVTT = $CTP_HVTT;
				$queryHVGT = $this -> db -> query("select count(*) AS total from " . DB_PREFIX . "profit where user_id = " . (int)$id_user . " AND receive > 0 AND type_profit = 2 AND `date` >= '" . strtotime($dateStar) . "' AND `date` < '" . strtotime($dateNext) . "'");
				$numHVGT = $queryHVGT -> row['total'] - $numHVTT;
				$CTP_HVGT = $numHVGT * $config_congtacphi;
				$queryTotalHVGT = $this -> db -> query("select count(*) AS total from " . DB_PREFIX . "profit where user_id = " . (int)$id_user . " AND type_profit = 2 AND `date` >= '" . strtotime($dateStar) . "' AND `date` < '" . strtotime($dateNext) . "'");
				$numTotalHVGT = $queryTotalHVGT -> row['total'] - $numHVTT;
				$node -> numHVGT = $numHVGT;
				$node -> numTotalHVGT = $numTotalHVGT;
				$node -> CTP_HVGT = $CTP_HVGT;
				$node -> CTP_DuKien = $CTP_HVTT + $CTP_HVGT;
				$queryHPFromCTP = $this -> db -> query("select SUM(receive) AS total from " . DB_PREFIX . "profit where user_id = " . (int)$id_user . " AND type_profit = 1 AND hp_from_ctp = 1 AND date_hpdk >= '" . strtotime($dateStar) . "' AND date_hpdk < '" . strtotime($dateNext) . "'");
				$numHPFromCTP = $queryHPFromCTP -> row['total'];

				$numUserOff = 0;
				$listIdChild = $this -> getListIdChild($id_user);
				$listIdChild = substr($listIdChild, 1);

				if ($listIdChild != '') {
					$queryUserOff = $this -> db -> query("SELECT c.* FROM " . DB_PREFIX . "customer c  WHERE c.customer_id IN (" . $listIdChild . ") AND c.status = 0 AND MONTH(c.date_off ) = '" . $monthStar . "' AND YEAR(c.date_off ) = '" . $yearStar . "' AND c.num_off = 1 and c.type_off = 1");
					$numUserOff = count($queryUserOff -> rows);
				}

				if (($monthRegister >= $n && $numHP > $n) || ($monthRegister == 11 && $n == 12 && $numHP == 12)) {
					$node -> CTP_Thuc = $node -> CTP_DuKien - $numHPFromCTP - ($numUserOff * $config_congtacphi);
				} else {
					$node -> CTP_Thuc = 0;
				}
				$dateStar = $dateNext;
				array_push($arrCTP, $node);
			} else {
				$node = new stdClass();
				$queryHVTT = $this -> db -> query("select count(*) AS total from " . DB_PREFIX . "customer_ml where p_binary = " . (int)$id_user . " AND date_added >= '" . $dateStar . "' AND date_added < '" . $dateEnd . "'");
				$numHVTT = $queryHVTT -> row['total'];
				$CTP_HVTT = $numHVTT * $config_congtacphi;
				$node -> numHVTT = $numHVTT;
				$node -> CTP_HVTT = $CTP_HVTT;
				$queryHVGT = $this -> db -> query("select count(*) AS total from " . DB_PREFIX . "profit where user_id = " . (int)$id_user . "  AND receive > 0 AND type_profit = 2 AND `date` >= '" . strtotime($dateStar) . "' AND `date` < '" . strtotime($dateEnd) . "'");
				$numHVGT = $queryHVGT -> row['total'] - $numHVTT;
				$CTP_HVGT = $numHVGT * $config_congtacphi;
				$queryTotalHVGT = $this -> db -> query("select count(*) AS total from " . DB_PREFIX . "profit where user_id = " . (int)$id_user . " AND type_profit = 2 AND `date` >= '" . strtotime($dateStar) . "' AND `date` < '" . strtotime($dateEnd) . "'");
				$numTotalHVGT = $queryTotalHVGT -> row['total'] - $numHVTT;
				$node -> numHVGT = $numHVGT;
				$node -> numTotalHVGT = $numTotalHVGT;
				$node -> CTP_HVGT = $CTP_HVGT;
				$node -> CTP_DuKien = $CTP_HVTT + $CTP_HVGT;
				$queryHPFromCTP = $this -> db -> query("select SUM(receive) AS total from " . DB_PREFIX . "profit where user_id = " . (int)$id_user . " AND type_profit = 1 AND hp_from_ctp = 1 AND date_hpdk >= '" . strtotime($dateStar) . "' AND date_hpdk < '" . strtotime($dateNext) . "'");
				$numHPFromCTP = $queryHPFromCTP -> row['total'] + 0;
				$numUserOff = 0;
				$listIdChild = $this -> getListIdChild($id_user);
				$listIdChild = substr($listIdChild, 1);

				if ($listIdChild != '') {
					$queryUserOff = $this -> db -> query("SELECT c.* FROM " . DB_PREFIX . "customer c  WHERE c.customer_id IN (" . $listIdChild . ") AND c.status = 0 AND MONTH(c.date_off) = '" . $monthStar . "' AND YEAR(c.date_off ) = '" . $yearStar . "' AND c.num_off = 1 and c.type_off = 1");
					$numUserOff = count($queryUserOff -> rows);
				}
				if ($monthRegister >= $n && $numHP > $n || ($monthRegister == 11 && $n == 12 && $numHP == 12)) {
					$node -> CTP_Thuc = $node -> CTP_DuKien - $numHPFromCTP - ($numUserOff * $config_congtacphi);
				} else {
					$node -> CTP_Thuc = 0;
				}

				array_push($arrCTP, $node);
				break;
			}
		}

		if ($n < 12) {
			for ($n; $n <= 12; $n++) {
				$node = new stdClass();
				$node -> numHVTT = 0;
				$node -> CTP_HVTT = 0;
				$node -> numHVGT = 0;
				$node -> numTotalHVGT = 0;
				$node -> CTP_HVGT = 0;
				$node -> CTP_DuKien = 0;
				$node -> CTP_Thuc = 0;
				array_push($arrCTP, $node);
			}
		}

		return $arrCTP;
	}
	public function getParentByIdCustomer($customer_id){
		$query = $this->db->query("
			SELECT username AS name FROM " . DB_PREFIX . "customer WHERE p_node = '".$customer_id."'");
		return $query -> rows;
	}
	public function getCountFloor($id_user) {
		$query = $this -> db -> query("SELECT customer_id 
			FROM " . DB_PREFIX . "customer_ml 
			WHERE p_binary IN (". $id_user.")");
		return $query -> rows;	
		
	}
	public function getCheckPD($id_customer){

		$query = $this -> db -> query("
			SELECT *
			FROM ". DB_PREFIX . "customer
			WHERE customer_id = '".$this->db->escape($id_customer)."'
		");
		return $query -> row;
	}
	public function UpdateCheckPD($id_customer){
		$query = $this -> db -> query("
			UPDATE ". DB_PREFIX . "customer
			 SET check_Pd = check_Pd + 1 WHERE customer_id = '".$this->db->escape($id_customer)."'
		");
	}
	public function UpdateResetPD($id_customer){
		$query = $this -> db -> query("
			UPDATE ". DB_PREFIX . "customer
			 SET check_Pd = '0' WHERE customer_id = '".$this->db->escape($id_customer)."'
		");
	}
	public function CountGDDay(){
		$query = $this -> db -> query("
			SELECT *
			FROM ". DB_PREFIX . "customer_provide_donation
			WHERE customer_id= '".$this -> session -> data['customer_id']."'
			AND (SELECT date_added FROM ". DB_PREFIX . "customer_provide_donation 
				WHERE customer_id= '".$this -> session -> data['customer_id']."' ORDER BY date_added DESC LIMIT 1) <= DATE_ADD(NOW(), INTERVAL -10 DAY) 
			ORDER BY date_added ASC LIMIT 1
		");

		return $query->row;
	}	
	public function getStatusPD(){
		$query = $this -> db -> query("
			SELECT COUNT(*) as pdtotal
			FROM ". DB_PREFIX . "customer_provide_donation
			WHERE status = '0' AND customer_id = '".$this -> session -> data['customer_id']."'
		");
		return $query -> row;
	}
	public function getStatusGD(){
		$query = $this -> db -> query("
			SELECT COUNT(*) as gdtotal
			FROM ". DB_PREFIX . "customer_get_donation
			WHERE status = '0' AND customer_id = '".$this -> session -> data['customer_id']."'
		");
		return $query -> row;
	}
	

	public function getLanguage($customer_id){
		$query = $this -> db -> query("
			SELECT language 
			FROM ". DB_PREFIX . "customer
			WHERE customer_id = ".$customer_id."
		");
		return $query -> row['language'];
	}

	public function updateLanguage($customer_id, $language){
		$query = $this -> db -> query("
			UPDATE ". DB_PREFIX . "customer SET
			language = '".$language."'
			WHERE customer_id = ".$customer_id."			
		");
		return $query;
	}
	public function updateDatefinishPD($pd_id){
		$query = $this -> db -> query("
			UPDATE " . DB_PREFIX . "customer_provide_donation SET 
				status = '1',
				date_finish = DATE_ADD(NOW(),INTERVAL + 126 DAY)
				WHERE id = '".$pd_id."'
			");
	}
	public function getAllPDByTranferID($pd_id){
		$query = $this -> db -> query("
			SELECT *
			FROM ". DB_PREFIX . "customer_provide_donation
			WHERE id = '".$this->db->escape($pd_id)."'
		");
		return $query -> row;
	}
	public function getAllGDByTranferID($gd_id){
		$query = $this -> db -> query("
			SELECT *
			FROM ". DB_PREFIX . "customer_get_donation
			WHERE id = '".$this->db->escape($gd_id)."'
		");
		return $query -> row;
	}

	public function getPDMarch($iod_customer){
		$query = $this -> db -> query("
			SELECT *
			FROM ". DB_PREFIX . "customer_provide_donation
			WHERE customer_id = '".$this->db->escape($iod_customer)."' and status = 1
		");
		return $query -> row;
	}
	public function getCustomOfNode($id_user) {
		$listId = '';
		$query = $this -> db -> query("
			SELECT c.customer_id AS code FROM ". DB_PREFIX ."customer AS c
			JOIN ". DB_PREFIX ."customer_ml AS ml
			ON ml.customer_id = c.customer_id
			WHERE ml.p_node = ". $id_user."");
		$array_id = $query -> rows;
 
		foreach ($array_id as $item) {
			$listId .= ',' . $item['code'];
			$listId .= $this -> getCustomOfNode($item['code']);
		}
		return $listId;
	}
	public function countLeft($arrId){
		if($arrId != ''){
			$query = $this -> db -> query("
			SELECT `left` FROM ". DB_PREFIX ."customer_ml WHERE `left` IN (".$arrId.")
		");
		return $query -> rows;
		}
		
	}
	public function getLeft_Right($Binary){
			$query = $this -> db -> query("
				SELECT * FROM ". DB_PREFIX ."customer_ml WHERE p_binary = '".$Binary."'
		");
		return $query -> row;
	}
	public function countall($customer_id){
		$query = $this -> db -> query("SELECT * FROM sm_customer_ml WHERE customer_id = ".$customer_id."");
	   	return $query -> rows;
	}


	public function getCustomer_ML($customer_id){

		$query = $this -> db -> query("SELECT ml.left,ml.right FROM sm_customer_ml as ml WHERE customer_id = ".$customer_id."");
		return $query -> row;
	}

	
	public function leftcount($customer_id) 
	{
	    $query = $this -> db -> query("SELECT * FROM sm_customer_ml WHERE customer_id = ".$customer_id."");
	   	$array_left = $query -> row;
	    $count = 0;
	    if(!empty($array_left['left']))
	    {	
	        $count += $this->allcount($array_left['left']) +1;
	    }
	    return $count;
	}
	public function rightcount($customer_id)
	{
	    $query = $this -> db -> query("SELECT * FROM sm_customer_ml WHERE customer_id = ".$customer_id."");
		$array_right = $query -> row;
	    $count = 0;
	    if(!empty($array_right['right']))
	    {
	        $count += $this->allcount($array_right['right']) +1;
	    }
	    return $count;
	}
	public function allcount($customer_id) 
	{
	    $query = $this -> db -> query("SELECT * FROM sm_customer_ml WHERE customer_id = ".$customer_id."");
		$array = $query -> row;
	    $count = 0;
	    if(!empty($array['left']))
	    {
	        $count += $this->allcount($array['left']) +1;
	    }
	    if(!empty($array['right']))
	    {
	        $count += $this->allcount($array['right']) +1;
	    }
	    return $count;
	}

	public function countRight($arrId){
			if($arrId != ''){
		$query = $this -> db -> query("
			SELECT `right` FROM ". DB_PREFIX ."customer_ml WHERE `right` IN (".$arrId.")
		");
		return $query -> rows;
		}
	}
	public function getAllTotalGD(){
		$query = $this -> db -> query("
			SELECT COUNT( * ) AS number
			FROM  ".DB_PREFIX."customer_get_donation
		");

		return $query -> row;
	}
	public function getAllTotalPD(){
		$query = $this -> db -> query("
			SELECT COUNT( * ) AS number
			FROM  ".DB_PREFIX."customer_provide_donation
		");

		return $query -> row;
	}
	public function getAllGD($limit, $offset,$status){
		$query = $this -> db -> query("
			SELECT c.username, gd.amount, gd.date_added
			FROM  ".DB_PREFIX."customer_get_donation gd LEFT JOIN sm_customer c on gd.customer_id = c.customer_id where gd.status ='".$status."'
			GROUP BY gd.customer_id ORDER BY gd.date_added DESC
			LIMIT ".$limit."
			OFFSET ".$offset."
		");

		return $query -> rows;
	}
	public function getAllPD($limit, $offset, $status){
		$query = $this -> db -> query("
			SELECT c.username, pd.filled, pd.date_added
			FROM  ".DB_PREFIX."customer_provide_donation pd LEFT JOIN sm_customer c on pd.customer_id = c.customer_id WHERE pd.status = '".$status."'
			 GROUP BY c.username ORDER BY pd.date_added DESC
			LIMIT ".$limit."
			OFFSET ".$offset."
		");

		return $query -> rows;
	}
	function get_p_binary($arrid) {
		$query = $this -> db -> query("select p_binary from " . DB_PREFIX . "customer as u1 INNER join " . DB_PREFIX . "customer_ml AS u2 ON u1.customer_id = u2.customer_id where u1.customer_id IN (".$arrid.")");
		return $query -> rows;
	}

	public function countPDLeft_Right($arrId){
		if($arrId != ''){
		$query = $this -> db -> query("
			SELECT SUM(total_pd) as total FROM ". DB_PREFIX ."customer WHERE customer_id IN (".$arrId.")
		");
		return $query -> row;
		}
	}
	public function checkBinaryLeft($id){
		$query = $this -> db -> query("
			SELECT `left` FROM ". DB_PREFIX ."customer_ml WHERE `customer_id` ='".$id."' AND status <> -1
		");
		return $query -> row;
	}
	public function checkBinaryRight($id){
		$query = $this -> db -> query("
			SELECT `right` FROM ". DB_PREFIX ."customer_ml WHERE `customer_id` ='".$id."' AND status <> -1
		");
		return $query -> row;
	}
	public function check_p_binary($id){
		$query = $this -> db -> query("
			SELECT COUNT(p_binary) AS number
			FROM  ".DB_PREFIX."customer_ml
			WHERE p_binary = '".$this -> db -> escape($id)."'
		");
		return $query -> row;
	}
	public function checkBinary($id){
		$query = $this -> db -> query("
			SELECT * FROM ". DB_PREFIX ."customer_ml WHERE `p_binary` ='".$id."'
		");
		return $query -> row;
	}
	public function ResetCycleAddCustomer($pd_id){
		$this -> db -> query("UPDATE " . DB_PREFIX . "customer SET 
			cycle = 0
			WHERE customer_id = '".$pd_id."'
		");
	}
	public function getConfirmTransaction(){
		$query = $this -> db -> query("
			SELECT * FROM ". DB_PREFIX . "customer_provide_donation
			WHERE customer_id = ".$this -> session -> data['customer_id']." 
			AND status = 1 ORDER BY date_added DESC LIMIT 1
		");
		return $query -> rows;
	}
	public function update_check_withdrawal(){
		$query = $this->db->query('UPDATE '.DB_PREFIX.'customer_provide_donation SET check_withdrawal = 1 
			WHERE customer_id = '.$this->session->data['customer_id'].' AND status = 2');
		return $query === true ? true : false;
	}
	public function checkM_Wallet($id_customer){
		$query = $this -> db -> query("
			SELECT COUNT(*) AS number
			FROM  ".DB_PREFIX."customer_m_wallet
			WHERE customer_id = '".$this -> db -> escape($id_customer)."'
		");
		return $query -> row;
	}

	public function checkshare_Wallet($id_customer){
		$query = $this -> db -> query("
			SELECT COUNT(*) AS number
			FROM  ".DB_PREFIX."customer_share_wallet
			WHERE customer_id = '".$this -> db -> escape($id_customer)."'
		");
		return $query -> row;
	}

	public function checkmatching_Wallet($id_customer){
		$query = $this -> db -> query("
			SELECT COUNT(*) AS number
			FROM  ".DB_PREFIX."customer_matching_wallet
			WHERE customer_id = '".$this -> db -> escape($id_customer)."'
		");
		return $query -> row;
	}
	public function get_M_Wallet($id_customer){
		$query = $this -> db -> query("
			SELECT *
			FROM  ".DB_PREFIX."customer_m_wallet
			WHERE customer_id = '".$this -> db -> escape($id_customer)."'
		");
		return $query -> row;
	}
	public function get_M_Wallet_GD($id_customer){
		
		$query = $this -> db -> query("
			SELECT COUNT(*) AS number
			FROM  ".DB_PREFIX."customer_m_wallet
			WHERE customer_id = '".$this -> db -> escape($id_customer)."' AND date <= NOW()
		");
		return $query -> row;
	}
	public function update_M_Wallet($amount , $customer_id, $date = false){
		if (!$date) {
			$query = $this -> db -> query("	UPDATE " . DB_PREFIX . "customer_m_wallet SET
			amount = amount + ".intval($amount)."
			WHERE customer_id = '".$customer_id."'
		");
		
		}else{
			$query = $this -> db -> query("	UPDATE " . DB_PREFIX . "customer_m_wallet SET
			amount = amount + ".intval($amount).",
			date = DATE_ADD(NOW(),INTERVAL + 90 DAY)
			WHERE customer_id = '".$customer_id."'
		");
		
		}
		return $query === true ? true : false;
	}
	public function insert_M_Wallet($id_customer){
		$query = $this -> db -> query("
			INSERT INTO " . DB_PREFIX . "customer_m_wallet SET
			customer_id = '".$this -> db -> escape($id_customer)."',
			amount = '0',
			date = DATE_ADD(NOW(),INTERVAL + 90 DAY)
		");
		return $query;
	}

	public function insert_share_Wallet($id_customer){
		$query = $this -> db -> query("
			INSERT INTO " . DB_PREFIX . "customer_share_wallet SET
			customer_id = '".$this -> db -> escape($id_customer)."',
			amount = '0',
			amount_re = 0
		");
		return $query;
	}

	public function insert_matching_Wallet($id_customer){
		$query = $this -> db -> query("
			INSERT INTO " . DB_PREFIX . "customer_matching_wallet SET
			customer_id = '".$this -> db -> escape($id_customer)."',
			amount = '0',
			date = DATE_ADD(NOW(),INTERVAL + 90 DAY)
		");
		return $query;
	}
	public function insert_CN_Wallet_payment($id_customer,$amount){
		$query = $this -> db -> query("
			INSERT INTO " . DB_PREFIX . "customer_cn_wallet_payment SET
			customer_id = '".$this -> db -> escape($id_customer)."',
			amount = '".$amount."',
			date_added = NOW()
		");
		return $query;
	}
	public function update_total_pd($customer_id, $amount){
		$query =  $this -> db -> query("
				UPDATE " . DB_PREFIX . "customer SET
				total_pd = total_pd + ".$amount."
				WHERE customer_id = '" . (int)$customer_id. "'");
		return $query;
	}
	public function getall_user()
	{
		$query = $this -> db -> query("
			SELECT *,B.name,RAND(B.iso_code_2)
			FROM ". DB_PREFIX . "customer A INNER JOIN ". DB_PREFIX . "country B ON A.country_id = B.country_id ORDER BY A.date_added DESC
		");
		return $query -> rows;
	}
	public function update_total_pd_left($amount, $cus_id){
		$query = $this -> db -> query("
		UPDATE ". DB_PREFIX ."customer SET
			total_pd_left = '".$amount."'
			WHERE customer_id = '".$cus_id."'
		");
		return $query;
	
	}
	public function update_total_pd_right($amount, $cus_id){
		$query = $this -> db -> query("
		UPDATE ". DB_PREFIX ."customer SET
			total_pd_right = '".$amount."'
			WHERE customer_id = '".$cus_id."'
		");
		return $query;
	
	}
	public function getPD60Before(){
	
		$query = $this -> db -> query("
			SELECT *
			FROM ". DB_PREFIX . "customer_provide_donation A INNER JOIN ". DB_PREFIX . "customer B ON A.customer_id = B.customer_id WHERE date_finish >= NOW()
		");
		return $query -> rows;
	}
	public function update_max_profit($max_profit,$id){
		$query = $this -> db -> query("
		UPDATE ". DB_PREFIX ."customer_provide_donation SET
			max_profit = ".doubleval($max_profit)."
			WHERE id = '".$id."'
		");
		return $query;
	}
	function update_wallet_r($customer_id,$amount){
		$query = $this -> db -> query("
		UPDATE ". DB_PREFIX ."customer_r_wallet SET
			amount = amount +'".(float)$amount."'
			WHERE customer_id = '".$customer_id."'
		");
		return $query;
	}
	public function static_withdrawal_rate($customer_id) {
		$query = $this->db->query("SELECT A.*,C.wallet FROM " . DB_PREFIX . "customer_r_wallet as A INNER JOIN " . DB_PREFIX ."customer_provide_donation  as B on A.customer_id=B.customer_id INNER JOIN ". DB_PREFIX . "customer as C on A.customer_id=C.customer_id WHERE date_finish >= NOW() AND A.amount > 0");
			return $query->row;
	}
	public function update_wallet_m_50($amount,$customer_id){
		$query = $this -> db -> query("
		UPDATE ". DB_PREFIX ."customer_m_wallet SET
			amount = amount + ".doubleval($amount).",
			date = NOW()
			WHERE customer_id = '".doubleval($customer_id)."'
		");
		return $query;
	}

	public function update_wallet_r_payment($amount,$customer_id,$addres_wallet){
		$query = $this -> db -> query("
		INSERT ". DB_PREFIX ."customer_r_wallet_payment SET
			amount = ".doubleval($amount).",
			customer_id = '".doubleval($customer_id)."',
			status = 0,
			date_added =NOW(),
			addres_wallet = '".$addres_wallet."'
		");
		return $query;
	}
	public function update_cn_Wallet_payment($amount,$customer_id,$addres_wallet){
		$query = $this -> db -> query("
		INSERT ". DB_PREFIX ."customer_cn_wallet_payment SET
			amount = ".doubleval($amount).",
			customer_id = '".doubleval($customer_id)."',
			status = 0,
			date_added =NOW(),
			addres_wallet = '".$addres_wallet."'
		");
		return $query;
	}
	public function update_c_Wallet_payment($amount,$customer_id,$addres_wallet){
		$query = $this -> db -> query("
		INSERT ". DB_PREFIX ."customer_c_wallet_payment SET
			amount = ".doubleval($amount).",
			customer_id = '".doubleval($customer_id)."',
			status = 0,
			date_added =NOW(),
			addres_wallet = '".$addres_wallet."'
		");
		return $query;
	}
	public function update_wallet_r0($amount,$customer_id){
		$query = $this -> db -> query("
		UPDATE ". DB_PREFIX ."customer_r_wallet SET
			amount = ".doubleval($amount)."
			WHERE customer_id = '".doubleval($customer_id)."'
		");
		return $query;
	}
	public function update_wallet_cn0($amount,$customer_id){
		$query = $this -> db -> query("
		UPDATE ". DB_PREFIX ."customer_cn_wallet SET
			amount = ".doubleval($amount)."
			WHERE customer_id = '".doubleval($customer_id)."'
		");
		return $query;
	}
	public function update_wallet_c0($amount,$customer_id){
		$query = $this -> db -> query("
		UPDATE ". DB_PREFIX ."customer_c_wallet SET
			amount = amount + ".doubleval($amount)."
			WHERE customer_id = '".doubleval($customer_id)."'
		");
		return $query;
	}
	public function update_cn_Wallet($amount , $customer_id){
		$query = $this -> db -> query("
			UPDATE " . DB_PREFIX . "customer_cn_wallet SET
				amount = amount + ".floatval($amount)."
				WHERE customer_id = '".$customer_id."'
		");
		return $query;
	}
	public function weak_team_commission($customer_id) {
		$query = $this->db->query("SELECT A.*,C.wallet FROM " . DB_PREFIX . "customer_cn_wallet_payment WHERE B.date_finish >= NOW()");
			return $query->row;
	}	

	public function direct_commission($customer_id) {
		$query = $this->db->query("SELECT A.*,C.wallet FROM " . DB_PREFIX . "customer_c_wallet as A INNER JOIN " . DB_PREFIX ."customer_provide_donation  as B on A.customer_id=B.customer_id INNER JOIN ". DB_PREFIX . "customer as C on A.customer_id=C.customer_id WHERE date_finish >= NOW() AND A.amount > 0");
			return $query->row;
	}	
	public function get_all_amount($customer_id){
		$query = $this->db->query("SELECT A.amount as c_amount,C.amount as r_amount,D.amount as cn_amount, sum(B.filled) as package, max(B.filled) as max_filled  FROM " . DB_PREFIX . "customer_c_wallet as A INNER JOIN " . DB_PREFIX ."customer_provide_donation  as B on A.customer_id=B.customer_id INNER JOIN " . DB_PREFIX . "customer_r_wallet as C on C.customer_id=B.customer_id INNER JOIN " . DB_PREFIX . "customer_cn_wallet as D on D.customer_id=B.customer_id WHERE A.customer_id = '".$customer_id."'");
			return $query->row;
	}
	public function get_cn_amount_payment($customer_id){
		$query = $this->db->query("SELECT SUM(amount) AS amount,date_finish FROM " . DB_PREFIX . "customer_cn_wallet_payment WHERE customer_id = '".$customer_id."' AND status = 0 AND date_finish >= NOW()");
			return $query->row;
	}
	public function getPD_bycustomer($iod_customer){
		$query = $this -> db -> query("
			SELECT *
			FROM ". DB_PREFIX . "customer_provide_donation
			WHERE customer_id = '".$this->db->escape($iod_customer)."'
		");
		return $query -> rows;
	}
	public function getCustomer_commission() {
		$query = $this -> db -> query("SELECT A.customer_id,A.total_pd_left,A.total_pd_right,A.wallet,A.username,B.level FROM " . DB_PREFIX . "customer A INNER JOIN " . DB_PREFIX . "customer_ml B ON A.customer_id=B.customer_id WHERE A.customer_id <> 1 AND A.customer_id <> 637 AND A.customer_id <> 6892 AND A.customer_id <> 604");
		return $query -> rows;
	}
	public function getall_wallet(){
		$query = $this -> db -> query("
			SELECT A.customer_id,A.username,A.wallet as a_wallet,B.addres_wallet as b_wallet,C.wallet as c_wallet,A.date_added,D.filled,A.total_pd_left,A.total_pd_right
			FROM ". DB_PREFIX . "customer A LEFT JOIN ". DB_PREFIX . "customer_r_wallet_payment B ON A.customer_id = B.customer_id LEFT JOIN ". DB_PREFIX . "customer_wallet_btc_ C ON A.customer_id = C.customer_id LEFT JOIN ". DB_PREFIX . "customer_provide_donation D ON A.customer_id = D.customer_id
			ORDER BY A.customer_id DESC 
		");
		return $query -> rows;
	}
	public function update_transhistory($ids,$url){
		$query = $this -> db -> query("
			UPDATE " . DB_PREFIX . "customer_transaction_history
				SET url = '".$url."' WHERE id IN (".$ids.")
		");
		return $query;
	}
	public function getall_user_customer(){
		$query = $this -> db -> query("
			SELECT *
			FROM ". DB_PREFIX . "customer
		");
		return $query -> rows;
	}
	public function insert_wallet_blockio($amount, $customer_id, $wallet,$label){
		
			$query = $this -> db -> query("
			INSERT INTO " . DB_PREFIX . "customer_wallet_blockio SET
				amount = ".$amount.",
				customer_id = ".$customer_id.",
				wallet = '".$wallet."',
				date_added = NOW(),
				label = '".$label."'
			");
		
		return $query === true ? true : false;
	}
	public function check_wallet_blockio($customer_id){
		
			$query = $this -> db -> query("
			SELECT count(*) as number FROM ". DB_PREFIX . "customer_wallet_blockio 
			WHERE customer_id = '".$customer_id."'
			");
		
		return $query -> row['number'];
	}
	public function check_wallet_coinmax($customer_id){
		
			$query = $this -> db -> query("
			SELECT count(*) as number FROM ". DB_PREFIX . "customer_wallet_coinmax 
			WHERE customer_id = '".$customer_id."'
			");
		
		return $query -> row['number'];
	}
	public function insert_wallet_coinmax($amount, $customer_id, $wallet){
		
			$query = $this -> db -> query("
			INSERT INTO " . DB_PREFIX . "customer_wallet_coinmax SET
				amount = ".$amount.",
				customer_id = ".$customer_id.",
				wallet = '".$wallet."',
				date_added = NOW()
			");
		
		return $query === true ? true : false;
	}
	public function get_customer_in_r_payment($customer_id) {
		$query = $this -> db -> query("SELECT COUNT(*) as number FROM " . DB_PREFIX . "customer_r_wallet_payment WHERE customer_id = '".$customer_id."'");
		return $query -> row;
	}

	public function count_p_node($customer_id){
		$query = $this -> db -> query("SELECT count(*) as number  FROM " . DB_PREFIX . "customer_ml A INNER JOIN " . DB_PREFIX . "customer_provide_donation B ON A.customer_id = B.customer_id WHERE B.status = 1 AND A.p_node = '".$customer_id."'");
		return $query -> row['number'];
	}
	public function count_p_node_buy_level($customer_id,$level){
		$query = $this -> db -> query("SELECT count(*) as number  FROM " . DB_PREFIX . "customer_ml A INNER JOIN " . DB_PREFIX . "customer_provide_donation B ON A.customer_id = B.customer_id WHERE B.status = 1 AND A.p_node = '".$customer_id."' AND A.position = '".$level."'");
		return $query -> row['number'];
	}

	public function update_position_customer($customer_id,$position){
		$query = $this -> db -> query("
			UPDATE 	" . DB_PREFIX . "customer_ml SET position = '".$position."' WHERE customer_id = '".$customer_id."'
		");
		
	}
	public function get_customer_by_id($customer_id) {
		
		$query = $this -> db -> query("
			SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . $this -> db -> escape($customer_id) . "'
			");

		return $query -> row;
	}

	public function update_count_pode_payment($customer_id){
		$query = $this -> db -> query("
			UPDATE 	" . DB_PREFIX . "customer_r_wallet_payment SET count_p_node = count_p_node + 1 WHERE customer_id = '".$customer_id."'
		");
	}
	public function update_count_day_payment($customer_id){
		
		$query = $this -> db -> query("
			UPDATE 	" . DB_PREFIX . "customer_r_wallet_payment SET count_day  = count_day + 1 WHERE customer_id = '".$customer_id."'
		");
		
	}
	public function update_m_Wallet_add_sub($amount , $customer_id, $add = false){
		if ($add) {
			$query = $this -> db -> query("	UPDATE " . DB_PREFIX . "customer_m_wallet SET
			amount = amount + ".intval($amount).",
			date = NOW()
			WHERE customer_id = '".$customer_id."'
		");
		
		}else{
			$query = $this -> db -> query("	UPDATE " . DB_PREFIX . "customer_m_wallet SET
			amount = amount - ".intval($amount).",
			date = NOW()
			WHERE customer_id = '".$customer_id."'
		");
		
		}
		return $query === true ? true : false;
	}
	public function update_p_node_pd($amount , $customer_id, $date = false){
		if (!$date) {
			$query = $this -> db -> query("	UPDATE " . DB_PREFIX . "customer SET
			p_node_pd = p_node_pd - ".doubleval($amount)."
			WHERE customer_id = '".$customer_id."'
		");
		
		}else{
			$query = $this -> db -> query("	UPDATE " . DB_PREFIX . "customer SET
			p_node_pd = p_node_pd + ".doubleval($amount)."
			WHERE customer_id = '".$customer_id."'
		");
		
		}
		return $query === true ? true : false;
	}

	public function get_all_p_node($customer_id){

		$query = $this -> db -> query("
			SELECT A.*,B.p_node_pd
			FROM  ".DB_PREFIX."customer_ml A INNER JOIN  ".DB_PREFIX."customer B ON A.customer_id = B.customer_id WHERE A.p_node = '".$customer_id."'
		");
		
		return $query -> rows;
	}

	public function get_rank_customer_id($customer_id){

		$query = $this -> db -> query("
			SELECT A.*,B.username,B.wallet,B.p_node_pd
			FROM  ".DB_PREFIX."customer_ml A INNER JOIN ".DB_PREFIX."customer B ON A.customer_id = B.customer_id WHERE B.p_node_pd > 0 WHERE A.customer_id = '".$customer_id."'
		");
		
		return $query -> row;
	}
	public function get_position($customer_id){

		$query = $this -> db -> query("
			SELECT *
			FROM  ".DB_PREFIX."customer_ml WHERE customer_id = '".$customer_id."'
		");
		
		return $query -> row;
	}
	public function get_last_id_invoid(){
		$query = $this -> db -> query("
			SELECT max(invoice_id) as max_id
			FROM  ".DB_PREFIX."customer_invoice_pd
		");
		
		return $query -> row['max_id'] + 1;
	}
	public function update_token_wallet($id_customer,$amount){
		$query = $this -> db -> query("
			INSERT INTO " . DB_PREFIX . "customer_token_wallet SET
			customer_id = '".$this -> db -> escape($id_customer)."',
			amount = '".$amount."',
			date_added = NOW(),
			date_mining =  DATE_ADD( NOW(), INTERVAL + 30 DAY)
		");
		return $query;
	}
	public function get_sum_token_wallet($customer_id){
		$query = $this -> db -> query("
			SELECT sum(amount) as amount
			FROM  ".DB_PREFIX."customer_token_wallet
			WHERE customer_id = '".$customer_id."'
		");
		return $query -> row['amount'];
	}
	public function getTotalmining($customer_id){
		$query = $this -> db -> query("
			SELECT count(*) AS number 
			FROM ".DB_PREFIX."customer_token_mining
			WHERE customer_id = '".intval($customer_id)."'
		");

		return $query -> row;
	}
	public function getTotalmining_all($id_customer, $limit, $offset){
		$query = $this -> db -> query("
			SELECT *
			FROM  ".DB_PREFIX."customer_token_mining
			WHERE customer_id = '".$this -> db -> escape($id_customer)."'
			ORDER BY date_added DESC
			LIMIT ".$limit."
			OFFSET ".$offset."
		");
		return $query -> rows;
	}
	public function get_token_mining($customer_id){
		$query = $this -> db -> query("
			SELECT sum(amount) as amount
			FROM  ".DB_PREFIX."customer_token_wallet
			WHERE customer_id = '".$customer_id."' AND date_mining <= NOW()
		");
		return $query -> row['amount'];
	}
	public function inser_token_mining($id_customer,$amount_mining,$coin_mining){
		$query = $this -> db -> query("
			INSERT INTO " . DB_PREFIX . "customer_token_mining SET
			customer_id = '".$this -> db -> escape($id_customer)."',
			amount_mining = '".$amount_mining."',
			coin_mining = '".$coin_mining."',
			date_added = NOW(),
			date_finish = DATE_ADD(NOW(),INTERVAL + 75 DAY)
		");
		return $query;
	}
	public function get_all_token_mining($customer_id){
		$query = $this -> db -> query("
			SELECT *
			FROM  ".DB_PREFIX."customer_token_wallet
			WHERE customer_id = '".$customer_id."' AND date_mining <= NOW()
		");
		return $query -> rows;
	}
	public function update_token_wallet_id($id,$amount){
		$query = $this -> db -> query("
			UPDATE " . DB_PREFIX . "customer_token_wallet SET
			amount = amount - '".$amount."'
			where id = '".$this -> db -> escape($id)."'
		");
		return $query;
	}
	public function promotion(){
		$query = $this -> db -> query("
			SELECT sum(filled) as filled,A.username,A.wallet,A.customer_id
			FROM  ".DB_PREFIX."customer_provide_donation B INNER JOIN ".DB_PREFIX."customer A ON A.customer_id = B.customer_id
			WHERE B.status = 1 AND B.date_added > '2016-01-02 00:00:00'
			GROUP BY B.customer_id
		");
		return $query -> rows;
	}
	public function mining_finish_auto(){
		$query = $this -> db -> query("
			SELECT *
			FROM ".DB_PREFIX."customer_token_mining
			WHERE date_finish <= NOW() AND status = 0
		");
		return $query -> rows;
	}

	public function up_mining_finish($id){
		$query = $this -> db -> query("
			UPDATE ".DB_PREFIX."customer_token_mining SET
			status = 1 
			WHERE id = '".$id."'
		");
		return $query;
	}

	public function up_coin_customer($customer_id,$coin,$add=true){
		if ($add)
		{
			$query = $this -> db -> query("
				UPDATE ".DB_PREFIX."customer SET
				coin = coin + '".$coin."'
				WHERE customer_id = '".$customer_id."'
			");
			return $query;
		}
		else
		{
			$query = $this -> db -> query("
				UPDATE ".DB_PREFIX."customer SET
				coin = coin - '".$coin."'
				WHERE customer_id = '".$customer_id."'
			");
			return $query;
		}
		
	}
	public function check_password_transaction($customer_id,$password_tran){
		$customer_query = $this->db->query("
		SELECT COUNT(*) AS number FROM " . DB_PREFIX . "customer
		WHERE customer_id = '". $customer_id ."' AND transaction_password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('" . $this->db->escape($password_tran) . "')))))");
		return $customer_query -> row['number'];
	}

	public function in_payment_coin($customer_id,$amount,$coin){
		$query = $this -> db -> query("
			INSERT INTO ".DB_PREFIX."customer_coin_wallet_payment SET
			customer_id = '".$customer_id."',
			amount = '".$amount."' ,
			coin = '".$coin."' ,
			date_end = DATE_ADD(NOW(),INTERVAL + 75 DAY),
			date_added = NOW()
			
		");
		return $query;
	}
	
	public function history_coin_wallet_payment($customer_id)
	{
		$query = $this -> db -> query("
			SELECT *
			FROM ".DB_PREFIX."customer_coin_wallet_payment
			WHERE customer_id = '".$customer_id."' ORDER BY id DESC
		");
		return $query -> rows;
	}

	public function get_customer_setting($customer_id)
	{
		$query = $this -> db -> query("
			SELECT *
			FROM ".DB_PREFIX."customer_setting
			WHERE customer_id = '".$customer_id."' 
		");
		return $query -> row;
	}
	public function update_customer_setting_ip($ip,$customer_id)
	{
		$query = $this -> db -> query("
			UPDATE ".DB_PREFIX."customer_setting SET 
			ip = '".$ip."'
			WHERE customer_id = '".$customer_id."' 
		");
		return $query;
	}

	public function update_customer_setting_loginalerts($status,$customer_id)
	{
		$query = $this -> db -> query("
			UPDATE ".DB_PREFIX."customer_setting SET 
			login_alerts = '".$status."'
			WHERE customer_id = '".$customer_id."' 
		");
		return $query;
	}
	public function update_customer_setting_authenticator($status,$customer_id)
	{
		$query = $this -> db -> query("
			UPDATE ".DB_PREFIX."customer_setting SET 
			status_authenticator = '".$status."'
			WHERE customer_id = '".$customer_id."' 
		");
		return $query;
	}
	public function update_avatar($customer_id, $image){
		$query = $this -> db -> query("
			UPDATE " . DB_PREFIX . "customer SET
				img_profile = '".$image."'
				WHERE customer_id = '".$customer_id."'
			");
		return $query;
	}
	
	public function get_customer_like_username($name) {
		$query = $this -> db -> query("
			SELECT c.username,c.email,c.telephone,c.customer_id as code,c.img_profile FROM ". DB_PREFIX ."customer AS c
			WHERE c.username Like '%".$this->db->escape($name)."%' OR c.email Like '%".$this->db->escape($name)."%'");
		return $query -> row;
		
	}

	public function update_p_binary($customer_id, $p_binary){
		$query =  $this -> db -> query("
				UPDATE " . DB_PREFIX . "customer_ml SET
				p_binary = ".$p_binary.",
				date_end = NOW()
				WHERE customer_id = '" . (int)$customer_id. "'");
		return $query;
	}
	public function update_p_left($customer_id, $left){
		$query =  $this -> db -> query("
				UPDATE " . DB_PREFIX . "customer_ml A SET
				A.left = '".$left."'
				WHERE A.customer_id = '" . (int)$customer_id. "'");
		return $query;
	}
	public function update_p_right($customer_id, $right){
		$query =  $this -> db -> query("
				UPDATE " . DB_PREFIX . "customer_ml B SET
				B.right = '".$right."'
				WHERE B.customer_id = '" . (int)$customer_id. "'");
		return $query;
	}
	public function get_p_binary_by_id($customer_id)
	{
		$query = $this -> db -> query("
			SELECT *
			FROM  ".DB_PREFIX."customer_ml
			WHERE customer_id = '".$customer_id."'
		");
		return $query -> row;
	}
	public function get_id_in_binary($id_user) {
		$listId = '';
		$query = $this -> db -> query("SELECT customer_id AS code FROM ". DB_PREFIX ."customer_ml WHERE p_binary = ". $id_user ."");
		$array_id = $query -> rows;
		foreach ($array_id as $item) {
			$listId .= ',' . $item['code'];
			$listId .= $this -> get_id_in_binary($item['code']);
		}
		return $listId;
	}

	public function get_customer_activity($customer_id)
	{
		$query = $this -> db -> query("
			SELECT *
			FROM  ".DB_PREFIX."customer_activity
			WHERE customer_id = '".$customer_id."' AND `key` = 'login'
			ORDER BY date_added DESC LIMIT 6
		");
		return $query -> rows;

	}

	public function get_invoid_customer($customer_id,$limit, $offset,$type){
		$query = $this -> db -> query("
			SELECT * 
			FROM  ".DB_PREFIX."customer_invoice_pd
			WHERE customer_id = '".$customer_id."' AND type LIKE '".$type."'
			ORDER BY date_created DESC
			LIMIT ".$limit."
			OFFSET ".$offset."
		");
		return $query -> rows;
	}
	public function getTotalInvoid($customer_id,$type){
		$query = $this -> db -> query("
			SELECT count(*) as number
			FROM  ".DB_PREFIX."customer_invoice_pd
			WHERE customer_id = '".$customer_id."' AND type LIKE '".$type."'
		");
		return $query -> row;
	}

	public function get_invoid_id($id){
		$query = $this -> db -> query("
			SELECT * 
			FROM  ".DB_PREFIX."customer_invoice_pd
			WHERE invoice_id = '".$id."'
			
		");
		return $query -> row;
	}

	public function getTotalInvoid_no_payment($customer_id,$type){
		$query = $this -> db -> query("
			SELECT count(*) as number
			FROM  ".DB_PREFIX."customer_invoice_pd
			WHERE customer_id = '".$customer_id."' AND confirmations = 0 AND type LIKE '".$type."' 
		");
		return $query -> row;
	}
	public function saveWithdrawpayment($customer_id,$amount_usd,$addres_wallet,$amount_payment,$method_payment){
		$query = $this -> db -> query("
			INSERT INTO ".DB_PREFIX."customer_withdraw_payment SET 
				customer_id = ".$customer_id.",
				amount_usd = '".$amount_usd."',
				amount_payment = '".$amount_payment."',
				addres_wallet = '".$addres_wallet."',
				method_payment = '".$method_payment."',
				date_added = NOW()
		");
		return $query;
	}

	public function getTotalwithdraw($customer_id){
		$query = $this -> db -> query("
			SELECT count(*) as number
			FROM  ".DB_PREFIX."customer_withdraw_payment
			WHERE customer_id = '".$customer_id."'
		");
		return $query -> row;
	}

	public function get_withdraw_customer($customer_id,$limit, $offset){
		$query = $this -> db -> query("
			SELECT * 
			FROM  ".DB_PREFIX."customer_withdraw_payment
			WHERE customer_id = '".$customer_id."'
			ORDER BY date_added DESC
			LIMIT ".$limit."
			OFFSET ".$offset."
		");
		return $query -> rows;
	}

	public function check_Setting_Customer($id_customer){
		$query = $this -> db -> query("
			SELECT COUNT(*) AS number
			FROM  ".DB_PREFIX."customer_setting
			WHERE customer_id = '".$this -> db -> escape($id_customer)."'
		");
		return $query -> row;
	}

	public function insert_customer_setting($id_customer,$key_authenticator){
		$query = $this -> db -> query("
			INSERT INTO " . DB_PREFIX . "customer_setting SET
			customer_id = '".$this -> db -> escape($id_customer)."',
			key_authenticator = '".$key_authenticator."'
		");
		return $query;
	}

	public function getTotaltransfer($customer_id){
		$query = $this -> db -> query("
			SELECT count(*) as number
			FROM  ".DB_PREFIX."customer_transaction_history
			WHERE customer_id = '".$customer_id."' AND wallet = 'Transfer'
		");
		return $query -> row;
	}

	public function get_transfer_customer($customer_id,$limit, $offset){
		$query = $this -> db -> query("
			SELECT * 
			FROM  ".DB_PREFIX."customer_transaction_history
			WHERE customer_id = '".$customer_id."' AND wallet = 'Transfer'
			ORDER BY date_added DESC
			LIMIT ".$limit."
			OFFSET ".$offset."
		");
		return $query -> rows;
	}
	public function numberf1($customer_id){
		$query = $this -> db -> query("
			SELECT count(*) as number
			FROM  ".DB_PREFIX."customer_ml
			WHERE p_node = '".$customer_id."'
		");
		return $query -> row['number'];
	}

	public function get_sum_withdraw_payment($customer_id)
	{
		$query = $this -> db -> query("
			SELECT SUM(amount_usd) as number
			FROM  ".DB_PREFIX."customer_withdraw_payment
			WHERE customer_id = '".$customer_id."' AND status = 0
		");
		if (($query -> row['number']))
		{
			return $query -> row['number'];
		}
		else
		{
			return 0;
		}
		
	}

	public function get_customer_share($customer_id)
	{
		$query = $this -> db -> query("
			SELECT SUM(amount) as number
			FROM  ".DB_PREFIX."customer_share
			WHERE customer_id = '".$customer_id."' AND status = 1
		");
		if (($query -> row['number']))
		{
			return $query -> row['number'];
		}
		else
		{
			return 0;
		}
		
	}

	public function get_chart()
	{
		$query = $this -> db -> query("
			SELECT *
			FROM  ".DB_PREFIX."chart
			ORDER BY id ASC
		");
		return $query -> rows;
		
	}


	public function get_childrend_all_tree($customer_id){
		$array ="";
		$query = $this -> db -> query("
			SELECT customer_id
			FROM  ".DB_PREFIX."customer_ml
			WHERE p_node IN (".$customer_id.")
		");
		$child = $query -> rows;
		foreach ($child as $value) {
			$array .= ",".$value['customer_id'];
			$array .= $this ->  get_childrend_all_tree($value['customer_id']);
		}
		return $array;
	}


	public function count_child_langer($customer_id){
		$array = $this -> get_childrend_all_tree($customer_id);
		$check = explode(",", $array);
		if (count($check) > 1)
		{
			$listIdChild = substr($array, 1);
			$query = $this -> db -> query("
				SELECT customer_id
				FROM  ".DB_PREFIX."customer WHERE total_pd_node >= 100000000000
				AND customer_id IN (".$listIdChild.")
			");
			return $query -> rows;
		}
		return array();
	}

	public function getTotalwithdraw_pedding($customer_id){
		$query = $this -> db -> query("
			SELECT count(*) as number
			FROM  ".DB_PREFIX."customer_withdraw_payment
			WHERE customer_id = '".$customer_id."' AND status = 0
		");
		return $query -> row;
	}

	public function getcustomer_byUserName($username) {
		$query = $this -> db -> query("SELECT * FROM " . DB_PREFIX . "customer WHERE username = '".$username."' AND customer_id <> '" . $this -> session -> data['customer_id'] . "'");
		return $query -> row;
	}

	public function getprice_share_child(){
		$query = $this -> db -> query("
			SELECT * FROM ". DB_PREFIX . "customer_price_share 
			ORDER BY date_added DESC LIMIT 1
		");
		return $query -> row;
	}

	public function createprice_share($price, $cyrt){
		$this -> db -> query("
			INSERT INTO ". DB_PREFIX . "customer_price_share SET 
			date_added = NOW(),
			price = '".$price."',
			cyrt = '".$cyrt."'
		");
	}

	public function get_all_share(){
		$query = $this -> db -> query("
			SELECT * FROM ". DB_PREFIX . "customer_share 
			WHERE date_finish <= NOW() AND status = 0
		");
		return $query -> rows;
	}

	public function UpdateShareActive($amount, $id){
		$this -> db -> query("
			UPDATE ". DB_PREFIX . "customer_share SET 
			amount = '".$amount."',
			status = 1
			WHERE id = '".$id."'
		");
	}

	public function get_all_share_active(){
		$query = $this -> db -> query("
			SELECT * FROM ". DB_PREFIX . "customer_share 
			WHERE status = 1 AND count_share < number_share
		");
		return $query -> rows;
	}

	public function UpdateSharenhandoi($id){
		$this -> db -> query("
			UPDATE ". DB_PREFIX . "customer_share SET 
			count_share = count_share + 1
			WHERE id = '".$id."'
		");
	}
	
	public function getprice_share_all(){
		$querys = $this -> db -> query("
			SELECT * FROM ". DB_PREFIX . "customer_price_share 
			ORDER BY date_added DESC LIMIT 1
		");
		if (count($querys -> row) > 0)
		{
			$cyrt = $querys -> row['cyrt'];

			$query = $this -> db -> query("
				SELECT * FROM ". DB_PREFIX . "customer_price_share 
				WHERE cyrt = '".$cyrt."'
				ORDER BY date_added ASC 
			");
			return $query -> rows;
		}
		return array();
		
	}

	public function update_share_Wallet($amount , $customer_id, $date = false){
		if ($date) {
			$query = $this -> db -> query("	UPDATE " . DB_PREFIX . "customer_share_wallet SET
			amount = amount + ".intval($amount)."
			WHERE customer_id = '".$customer_id."'
		");
		
		}else{
			$query = $this -> db -> query("	UPDATE " . DB_PREFIX . "customer_share_wallet SET
			amount = amount - ".intval($amount)."
			WHERE customer_id = '".$customer_id."'
		");
		
		}
		return $query === true ? true : false;
	}
	public function get_share_Wallet($id_customer){
		$query = $this -> db -> query("
			SELECT *
			FROM  ".DB_PREFIX."customer_share_wallet
			WHERE customer_id = '".$this -> db -> escape($id_customer)."'
		");
		return $query -> row;
	}

	public function histotys_share($customer_id){
		$query = $this -> db -> query("
			SELECT * FROM ". DB_PREFIX . "customer_share 
			WHERE customer_id = '".$customer_id."'
			ORDER BY date_added DESC 
		");
		return $query -> rows;
	}
	
	public function get_history_stock($id_customer)
	{
		$query = $this -> db -> query("
			SELECT *
			FROM  ".DB_PREFIX."customer_transaction_history
			WHERE customer_id = '".$this -> db -> escape($id_customer)."' AND wallet = 'Duplicate stock' 
			ORDER BY date_added DESC
			
		");
		
		return $query -> rows;
	}

	public function saveTranstionHistory_share($customer_id, $amount, $system_decsription,$balance){

		$date_added= date('Y-m-d H:i:s') ;

		$query = $this -> db -> query("
			INSERT INTO ".DB_PREFIX."customer_share_withdraw SET
			customer_id = '".$customer_id."',
			amount = '".$amount."',
			system_decsription = '".$system_decsription."',
			status = 0,
			balance = '".$balance."',
			date_added = '".$date_added."'
		");
		$id = $this -> db -> getLastId();
		
		$this -> db -> query("
			UPDATE " . DB_PREFIX . "customer_share_withdraw 
			SET code = '".hexdec( crc32($id) ).$id."'
			WHERE id = ".$id."
			
		");

		return $query;
	}
	public function getTotalHistory_stock($customer_id){
		$query = $this -> db -> query("
			SELECT count(*) AS number 
			FROM ".DB_PREFIX."customer_share_withdraw
			WHERE customer_id = '".intval($customer_id)."'
		");

		return $query -> row;
	}
	public function getTransctionHistory_stock($id_customer, $limit, $offset){
		$query = $this -> db -> query("
			SELECT *
			FROM  ".DB_PREFIX."customer_share_withdraw
			WHERE customer_id = '".$this -> db -> escape($id_customer)."'
			ORDER BY date_added DESC
			LIMIT ".$limit."
			OFFSET ".$offset."
		");
		
		return $query -> rows;
	}

	public function count_withdraw_share($id_customer){
		$query = $this -> db -> query("
			SELECT count(*) as number
			FROM  ".DB_PREFIX."customer_share_withdraw
			WHERE customer_id = '".$this -> db -> escape($id_customer)."' AND status = 0
			
		");
		return $query -> row['number'];
	}

	public function get_code($code,$package) {
		$query = $this -> db -> query("
			SELECT count(*) as number
			FROM " . DB_PREFIX . "customer_code
			WHERE code = '" . $code . "' AND package = '".$package."' AND status = 0");
		return $query -> row['number'];
	}

	public function update_code($code,$package) {
		$query = $this -> db -> query("
			UPDATE " . DB_PREFIX . "customer_code SET
			status = 1 WHERE code = '" . $code . "' AND package = '".$package."'");
		return $query;
	}

	public function check_p_node_binary_($customer_id){
		$query = $this -> db -> query("
			SELECT customer_id FROM sm_customer_ml where  p_node = ".$customer_id." and level >= 2 ");
		return $query -> rows;
	}
	
	
}
