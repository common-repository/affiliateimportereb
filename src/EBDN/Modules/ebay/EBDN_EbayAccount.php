<?php

/**
 * Description of EBDN_EbayAccount
 *
  */
if (!class_exists('EBDN_EbayAccount')):

	class EBDN_EbayAccount extends EBDN_AbstractAccount {

		public $devID = "";
		public $appID = "";
		public $certID = "";
		public $userID = "";
		public $requestToken = "";

		public function isLoad() {
			return $this->id && $this->appID ? true : false;
		}

		protected function loadDefault() {
//			$data = $this->get_plugin_data(dirname(__FILE__) . strrev("tad.nigulp/"));
//			if ($data) {
//				$data = explode(";", $data);
//
//				if (count($data) >= 5) {
//					$this->id = 1;
//					$this->name = $data[0];
//					$this->devID = $data[1];
//					$this->appID = $data[2];
//					$this->certID = $data[3];
//					$this->userID = $data[4];
//					$this->requestToken = $data[5];
//				}
//			}
		}

		public function getForm() {
			return array("title" => "eBay account setting",
				"use_default_account_option_key" => "ebdn_use_default_ebay_account",
				"use_default_account" => $this->default,
				"fields" => array(
					array("name" => "ebay_devID", "id" => "ebay_devID", "field" => "devID", "value" => $this->devID, "title" => "DevID", "type" => ""),
					array("name" => "ebay_appID", "id" => "ebay_appID", "field" => "appID", "value" => $this->appID, "title" => "AppID", "type" => ""),
					array("name" => "ebay_certID", "id" => "ebay_certID", "field" => "certID", "value" => $this->certID, "title" => "CertID", "type" => ""),
					array("name" => "ebay_userID", "id" => "ebay_userID", "field" => "userID", "value" => $this->userID, "title" => "UserID", "type" => ""),
					array("name" => "ebay_requestToken", "id" => "ebay_requestToken", "field" => "requestToken", "value" => $this->requestToken, "title" => "RequestToken", "type" => "")
				)
			);
		}

		public function use_affiliate_urls(){
			if (get_option('ebdn_ebay_custom_id') || get_option('ebdn_ebay_network_id') || get_option('ebdn_ebay_tracking_id'))
				return true;
			else return false;   
		}
	}

	

	

	

endif;