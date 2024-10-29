<?php

/**
 * Description of EBDN_EbaySite
 *
 * @author Geometrix
 */
if (!class_exists('EBDN_EbaySite')):

    class EBDN_EbaySite {

        public $id = "";
        public $language = "";
        public $country = "";
        public $siteid = "";
        public $sitecode = "";
        public $sitename = "";

        public function __construct($data = array()) {
            if ($data) {
                foreach ($data as $field => $value) {
                    if (property_exists(get_class($this), $field)) {
                        $this->$field = $value;
                    }
                }
            }
        }

        public static function load_sites() {
            global $wpdb;
            $result = array();
            $db_res = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . EBDN_TABLE_EBAY_SITES);
            if ($db_res) {
                foreach ($db_res as $row) {
                    $result[] = new EBDN_EbaySite($row, true);
                }
            }

            return $result;
        }

    }

    

    

endif;