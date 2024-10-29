<?php

/**
 * Description of EBDN_EBayConfigurator
 */
if (!defined('EBDN_TABLE_EBAY_SITES')) {
	define('EBDN_TABLE_EBAY_SITES', 'ebdn_ebay_sites');
}
if (!class_exists('EBDN_EbayConfigurator')):

	class EBDN_EbayConfigurator extends EBDN_AbstractConfigurator {

		public function getConfig() {
			return array(
				"version" => "1.22",
				"instaled" => true,
				"type" => "ebay",
				"demo_mode" => false,
				"menu_title" => "Ebay",
				"dashboard_title" => "Ebay",
				"account_class" => "EBDN_EbayAccount",
				"loader_class" => "EBDN_EbayLoader",
				"sort_columns" => array("price", "user_price", "ship", "ship_to_locations", "curr"),
				"promo_title" => 'Ebay & Aliexpress WooCommerce Importer',
				"promo_text" => '<p>It’s a plugin that used to import products from Ebay and Aliexpress to your Wordpress WooCommerce site.</p><p>The plugin is helpful to create a store with specific Ebay & Aliexpress products and use affiliate URLs.</p>',
				"promo_link" => 'http://codecanyon.net/item/ebay-aliexpress-woocommerce-importer/13388576'
			);
		}

		public function saveSetting($data) {
			update_option('ebdn_ebay_custom_id', wp_unslash($data['ebdn_ebay_custom_id']));
			update_option('ebdn_ebay_geo_targeting', isset($data['ebdn_ebay_geo_targeting']));
			update_option('ebdn_ebay_network_id', wp_unslash($data['ebdn_ebay_network_id']));
			update_option('ebdn_ebay_tracking_id', wp_unslash($data['ebdn_ebay_tracking_id']));
			update_option('ebdn_ebay_per_page', intval($data['ebdn_ebay_per_page']) <= 100 ? intval($data['ebdn_ebay_per_page']) : 100);
                        update_option('ebdn_ebay_extends_cats', isset($data['ebdn_ebay_extends_cats']) ? 1 : 0);
		}

		public function modifyColumns($columns) {
			return $columns;
		}

		protected function configureFilters() {

			$this->addFilter("store", "store", 11, array("type" => "edit",
				"label" => "Store name",
				"placeholder" => "Please enter your store name"));

			$this->addFilter("category_id", "category_id", 21, array("type" => "select",
				"label" => "Category",
				"class" => "category_list",
				"data_source" => array($this, 'getCategories')));

                        /*
			$this->add_filter("shipment", array("shipment_min_price", "shipment_max_price"), 31, array("type" => "edit",
				"label" => "Shipment price",
				"shipment_min_price" => array("label" => "from $", "default" => "0.00"),
				"shipment_max_price" => array("label" => " to $", "default" => "0.00")));
                         */

                        $this->addFilter("free_shipping_only", "free_shipping_only", 31, array("type" => "checkbox",
				"label" => "Free Shipping Only",
				"default" => "yes"));

			$this->addFilter("feedback_score", array("min_feedback", "max_feedback"), 32, array("type" => "edit",
				"label" => "Feedback score",
				"min_feedback" => array("label" => "min", "default" => "0"),
				"max_feedback" => array("label" => " max", "default" => "0")));

			$this->addFilter("available_to", "available_to", 33, array("type" => "select",
				"label" => "Shipment Options",
				"class" => "countries_list",
				"data_source" => array($this, 'get_countries')));

			$this->addFilter("condition", "condition", 34, array("type" => "select",
				"label" => "Condition",
				"class" => "sitecode_list",
				"data_source" => array($this, 'getConditionList')));

			$this->addFilter("sitecode", "sitecode", 35, array("type" => "select",
				"label" => "Site",
				"class" => "sitecode_list",
				"data_source" => array($this, 'getSites')));

			$this->addFilter("listing_type", "listing_type", 36, array("type" => "select",
				"label" => "Listing Type",
				"class" => "sitecode_list",
				"multiple" => true,
				"data_source" => array($this, 'get_listing_type')));
		}

		public function getCategories() {
			$result = array();
			$result[] = array("id" => "", "name" => " - ");
                        $xml = simplexml_load_file(dirname(__FILE__) . '/data/ebay_categories.xml');
			foreach ($xml->CategoryArray->Category as $c) {
                            
				if ((strval($c->CategoryLevel) == "1") || (get_option('ebdn_ebay_extends_cats', false) && strval($c->CategoryLevel) == "2") /* || ((String)$c->CategoryLevel) == "3" || ((String)$c->CategoryLevel) == "4" */)
					$result[] = array('id' => (String) $c->CategoryID, 'name' => (String) $c->CategoryName, 'level' => (String) $c->CategoryLevel);
			}

			return $result;
		}

		public function get_countries() {
			$result = array();
			$result[] = array("id" => "", "name" => " - ");
			$handle = @fopen(EBDN_ROOT_PATH . "/data/countries.csv", "r");
			if ($handle) {
				while (($buffer = fgets($handle, 4096)) !== false) {
					$cntr = explode(",", $buffer);
					$result[] = array("id" => $cntr[1], "name" => $cntr[0]);
				}
				if (!feof($handle)) {
					echo "Error: unexpected fgets() fail<br/>";
				}
				fclose($handle);
			}
			return $result;
		}

		public function getConditionList() {
			return array(array("id" => "", "name" => ""),
				array("id" => 1000, "name" => "New"),
				array("id" => 1500, "name" => "New other (see details)"),
				array("id" => 1750, "name" => "New with defects"),
				array("id" => 2000, "name" => "Manufacturer refurbished"),
				array("id" => 2500, "name" => "Seller refurbished"),
				array("id" => 3000, "name" => "Used"),
				array("id" => 4000, "name" => "Very Good"),
				array("id" => 5000, "name" => "Good"),
				array("id" => 6000, "name" => "Acceptable"),
				array("id" => 7000, "name" => "For parts or not working"));
		}

		public function getSites() {
			$result = array();
			$sites = EBDN_EbaySite::load_sites();
			foreach ($sites as $site) {
				$result[] = array("id" => $site->sitecode, "name" => $site->sitename, "code" => $site->siteid);
			}
			return $result;
		}

		public function get_listing_type() {
			return array(array("id" => "All", "name" => "All"),
				array("id" => "Auction", "name" => "Auction"),
				array("id" => "AuctionWithBIN", "name" => "Auction With Buy It Now"),
				array("id" => "FixedPrice", "name" => "Fixed Price"),
				array("id" => "Classified", "name" => "Classified"));
		}

		public function install() {
			/** @var wpdb $wpdb */
			global $wpdb;

			$charset_collate = '';
			if (!empty($wpdb->charset)) {
				$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
			}
			if (!empty($wpdb->collate)) {
				$charset_collate .= " COLLATE {$wpdb->collate}";
			}

			add_option('ebdn_ebay_custom_id', '', '', 'no');
			add_option('ebdn_ebay_geo_targeting', false, '', 'no');
			add_option('ebdn_ebay_network_id', '9', '', 'no');
			add_option('ebdn_ebay_tracking_id', '', '', 'no');
			add_option('ebdn_ebay_per_page', 20, '', 'no');
                        
			$table_name = $wpdb->prefix . EBDN_TABLE_EBAY_SITES;
			$sql = "CREATE TABLE {$table_name} (" .
					"`id` INT(20) UNSIGNED NOT NULL AUTO_INCREMENT," .
					"`language` VARCHAR(255) NULL DEFAULT ''," .
					"`country` VARCHAR(255) NULL DEFAULT ''," .
					"`siteid` VARCHAR(255) NULL DEFAULT ''," .
					"`sitecode` VARCHAR(255) NULL DEFAULT ''," .
					"`sitename` VARCHAR(255) NULL DEFAULT ''," .
					"PRIMARY KEY (`id`) )" .
					" {$charset_collate} ENGINE=InnoDB;";
			dbDelta($sql);

			$sql = "INSERT INTO `{$table_name}` (`language`, `country`, `siteid`, `sitecode`, `sitename`) VALUES
					('en-US', 'US', '0', 'EBAY-US', 'eBay United States'),
			('de-AT', 'AT', '16', 'EBAY-AT', 'eBay Austria'),
			('en-AU', 'AU', '15', 'EBAY-AU', 'eBay Australia'),
			('de-CH', 'CH', '193', 'EBAY-CH', 'eBay Switzerland'),
			('en-DE', 'DE', '77', 'EBAY-DE', 'eBay Germany'),
			('en-CA', 'CA', '2', 'EBAY-ENCA', 'eBay Canada (English)'),
			('en-ES', 'ES', '186', 'EBAY-ES', 'eBay Spain'),
			('fr-FR', 'FR', '71', 'EBAY-FR', 'eBay France'),
			('fr-BE', 'BE', '23', 'EBAY-FRBE', 'eBay Belgium(French)'),
			('fr-CA', 'CA', '210', 'EBAY-FRCA', 'eBay Canada (French)'),
			('en-GB', 'GB', '3', 'EBAY-GB', 'eBay UK'),
			('zh-Hant', 'HK', '201', 'EBAY-HK', 'eBay Hong Kong'),
			('en-IE', 'IE', '205', 'EBAY-IE', 'eBay Ireland'),
			('en-IN', 'IN', '203', 'EBAY-IN', 'eBay India'),
			('it-IT', 'IT', '101', 'EBAY-IT', 'eBay Italy'),
			('en-US', 'US', '100', 'EBAY-MOTOR', 'eBay Motors'),
			('en-MY', 'MY', '207', 'EBAY-MY', 'eBay Malaysia'),
			('nl-NL', 'NL', '146', 'EBAY-NL', 'eBay Netherlands'),
			('nl-BE', 'BE', '123', 'EBAY-NLBE', 'eBay Belgium(Dutch)'),
			('en-PH', 'PH', '211', 'EBAY-PH', 'eBay Philippines'),
			('pl-PL', 'PH', '212', 'EBAY-PL', 'eBay Poland'),
			('en-SG', 'SG', '216', 'EBAY-SG', 'eBay Singapore');";
			dbDelta($sql);
		}

		public function uninstall() {
			/** @var wpdb $wpdb */
			global $wpdb;

			delete_option('ebdn_ebay_custom_id');
			delete_option('ebdn_ebay_geo_targeting');
			delete_option('ebdn_ebay_network_id');
			delete_option('ebdn_ebay_tracking_id');
			delete_option('ebdn_ebay_per_page');

			$sql = "DROP TABLE IF EXISTS " . $wpdb->prefix . EBDN_TABLE_EBAY_SITES . ";";
			$wpdb->query($sql);
		}

        public function modifyColumnData($data, /* @var $item WPEAE_Goods */ $item, $column_name) {
            return $data;
        }
	}

	endif;

new EBDN_EbayConfigurator();
