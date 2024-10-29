<?php
if (!class_exists('EBDN_WooCommerce_OrderList')) {

    class EBDN_WooCommerce_OrderList
    {

        public function __construct()
        {
            if (is_admin()) {
                add_action('admin_enqueue_scripts', [$this, 'assets']);
                add_action('manage_shop_order_posts_custom_column', [$this, 'columnsData'], 100);
            }
        }

        public function assets()
        {

            $plugin_data = get_plugin_data(EBDN_FILE_FULLNAME);
            wp_enqueue_style('ebdn-wc-ol-style', plugins_url('assets/css/wc_ol_style.css', EBDN_FILE_FULLNAME), array(), $plugin_data['Version']);
            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_script('ebdn-wc-ol-script', plugins_url('assets/js/wc_ol_script.js', EBDN_FILE_FULLNAME), array(), $plugin_data['Version']);
        }

        public function columnsData($column)
        {
            global $post;

            $actions = array();

            if ($column === 'order_title') {
                $actions = array_merge($actions, array(
                    'ebdn_product_info' => sprintf('<a class="ebdn-order-info" id="ebdn-%1$d" href="/">%2$s</a>', $post->ID, 'AffiliateImporterEb Info')
                ));

            }

            $actions = apply_filters('ebdn_wcol_row_actions', $actions, $column);

            if (count($actions) > 0) {
                echo implode($actions, ' | ');
            }

        }
    }
}
new EBDN_WooCommerce_OrderList();