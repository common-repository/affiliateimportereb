<?php
if (!class_exists('EBDN_Log')) {

    class EBDN_Log
    {
        private $module;

        public function __construct($module)
        {
            $this->module = $module;
        }

        /**
         * @param int $start_id
         * @param string|array $type
         * @return array|null|object
         */
        public function load($start_id = 0, $type = 'message')
        {
            /** @var wpdb $wpdb */
            global $wpdb;

            if (!is_array($type)) {
                $type = array($type);
            }
            foreach ($type as $key => $val) {
                $type[$key] = "'" . $wpdb->_real_escape($val) . "'";
            }

            $type_sql = implode(',', $type);

            $results = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . EBDN_TABLE_LOG . " WHERE `module` = '{$this->module}' AND `type` IN ({$type_sql}) AND `id` > {$start_id}");

            return $results;
        }

        public function add($text, $type = 'message')
        {
            /** @var wpdb $wpdb */
            global $wpdb;
            $wpdb->insert($wpdb->prefix . EBDN_TABLE_LOG, array('text' => $text, 'type' => $type, 'module' => $this->module, 'time' => date('Y-m-d H:i:s', time())));
        }

        public function clear()
        {
            /** @var wpdb $wpdb */
            global $wpdb;
            $wpdb->delete($wpdb->prefix . EBDN_TABLE_LOG, array('module' => $this->module));

        }

    }

}
