<?php
class ControllerAccountAccount extends Controller {
	public function index()
	{

		$this -> load -> model('account/customer');
		$data['self'] = $this;

		$data['get_all_username'] = $this -> model_account_customer -> get_all_username();

		$get_last_tienan= $this -> model_account_customer -> get_last_tienan();


		if (count($get_last_tienan) == 0) {
			foreach ($data['get_all_username'] as $value) {
				$this -> model_account_customer -> insert_tienan(1,$value['customer_id']);
			}
		}
		
		if (empty($this -> request -> get['week']))
		{

			$get_last_tienan= $this -> model_account_customer -> get_last_tienan();
			$this -> response -> redirect($this -> url -> link('account/account&week='.intval($get_last_tienan['week']).'', '', 'SSL'));
		}

		
		$this -> response -> setOutput($this -> load -> view($this -> config -> get('config_template') . '/template/account/account.tpl', $data));
		
	}
	
	public function submit()
	{	
		$this -> load -> model('account/customer');
		$data['self'] = $this;

		$week = $this-> request-> get['week'];
		$thu = $this-> request-> get['day'];

		$this -> model_account_customer -> update_tienan($week,$thu,intval($this-> request-> post['trung']),intval($this-> request-> post['amout_total']),1,intval($this-> request-> post['dicho']));

		$this -> model_account_customer -> update_tienan($week,$thu,intval($this-> request-> post['phong']),intval($this-> request-> post['amout_total']),2,intval($this-> request-> post['dicho']));

		$this -> model_account_customer -> update_tienan($week,$thu,intval($this-> request-> post['tai']),intval($this-> request-> post['amout_total']),3,intval($this-> request-> post['dicho']));

		$this -> model_account_customer -> update_tienan($week,$thu,intval($this-> request-> post['tu']),intval($this-> request-> post['amout_total']),4,intval($this-> request-> post['dicho']));

		$this -> model_account_customer -> update_tienan($week,$thu,intval($this-> request-> post['anh']),intval($this-> request-> post['amout_total']),5,intval($this-> request-> post['dicho']));

		$this -> model_account_customer -> update_tienan($week,$thu,intval($this-> request-> post['hien']),intval($this-> request-> post['amout_total']),6,intval($this-> request-> post['dicho']));

		$this -> model_account_customer -> update_tienan($week,$thu,intval($this-> request-> post['thuong']),intval($this-> request-> post['amout_total']),7,intval($this-> request-> post['dicho']));

		$this -> model_account_customer -> update_tienan($week,$thu,intval($this-> request-> post['huong']),intval($this-> request-> post['amout_total']),8,intval($this-> request-> post['dicho']));
		
		$this -> response -> redirect($this -> url -> link('account/account&week='.intval($this -> request -> get['week']).'', '', 'SSL'));
	}

	public function prev_week()
	{
		$this -> load -> model('account/customer');
		$data['self'] = $this;

		$check_week = $this -> model_account_customer -> check_week(intval($this -> request -> get['week']));
		intval($check_week) === 0 && die('<h1>No data!</h1>');
		$this -> response -> redirect($this -> url -> link('account/account&week='.intval($this -> request -> get['week']).'', '', 'SSL'));
	}
	public function next_week()
	{
		$this -> load -> model('account/customer');
		$data['self'] = $this;

		$get_last_tienan = $this -> model_account_customer -> get_last_tienan();
		if (intval($get_last_tienan['week']) ==  intval($this -> request -> get['week']))
		{
			$this -> response -> redirect($this -> url -> link('account/account&week='.intval($this -> request -> get['week']).'', '', 'SSL'));
		}
		else
		{	
			$get_all_username = $this -> model_account_customer -> get_all_username();
			foreach ($get_all_username as $value) {
				$this -> model_account_customer -> insert_tienan(intval($this -> request -> get['week']),$value['customer_id']);
			}
			$this -> response -> redirect($this -> url -> link('account/account&week='.intval($this -> request -> get['week']).'', '', 'SSL'));
		}
	}


	public function get_data_number($customer_id,$week)
	{
		$this -> load -> model('account/customer');
		$data['self'] = $this;

		return $this -> model_account_customer -> get_data_number($customer_id,$week);
	}

	public function get_data_tienan($week)
	{
		$this -> load -> model('account/customer');
		$data['self'] = $this;

		return $this -> model_account_customer -> get_data_tienan($week);
	}

	public function total_week($customer_id,$week){
		$this -> load -> model('account/customer');
		$data['self'] = $this;

		return $this -> model_account_customer -> total_week($week,$customer_id);
	}

	public function calue_total($week,$customer_id,$thu)
	{
		$this -> load -> model('account/customer');
		$data['self'] = $this;
		return $this -> model_account_customer -> calue_total($week,$customer_id,$thu);
	}
}
