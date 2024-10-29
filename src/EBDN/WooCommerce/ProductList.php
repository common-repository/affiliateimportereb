<?php

if (!class_exists('EBDN_WooCommerce_ProductList')) {

    class EBDN_WooCommerce_ProductList
    {

        private $bulkActions = array();
        private $bulkActionsText = array();

        public function __construct()
        {
            if (is_admin()) {
                add_action('admin_footer-edit.php', [$this, 'scripts']);
                add_action('load-edit.php', [$this, 'bulkActions']);
                add_action('admin_notices', [$this, 'adminNotices']);
                add_filter('post_row_actions', [$this, 'rowActions'], 2, 150);
                add_action('admin_enqueue_scripts', [$this, 'assets']);
                add_action('admin_init', [$this, 'init']);
            }
        }

        public static function getCount()
        {
            /**
             * @var wpdb $wpdb
             */
            global $wpdb;

            return (int)$wpdb->get_var("SELECT count(*) FROM $wpdb->postmeta WHERE meta_key='ebdn_import'");
        }

        public function init()
        {
            if (get_option('ebdn_price_auto_update', false)) {
                $this->bulkActions[] = 'ebdn_product_update_manual';

                $update_price = get_option('ebdn_regular_price_auto_update', false);
                $text = 'Update stock';
                if ($update_price) {
                    $text = "Update price & stock";
                }

                $this->bulkActionsText['ebdn_product_update_manual'] = $text;
            }

            list($this->bulkActions, $this->bulkActionsText) = apply_filters('ebdn_wcpl_bulk_actions_init', array($this->bulkActions, $this->bulkActionsText));
        }

        public function rowActions($actions, $post)
        {
            if ('product' === $post->post_type) {

                $external_id = get_post_meta($post->ID, "external_id", true);

                if ($external_id) {

                    $actions = array_merge($actions, array(
                        'ebdn_product_info' => sprintf('<a class="ebdn-product-info" id="ebdn-%1$d" href="/">%2$s</a>',
                            $post->ID,
                            'AffiliateImporterEb Info'
                        )
                    ));

                    return $actions;

                } else {
                    return $actions;
                }
            }
            return [];
        }

        public function assets()
        {

            $plugin_data = get_plugin_data(EBDN_FILE_FULLNAME);
            wp_enqueue_style('ebdn-wc-pl-style', plugins_url('assets/css/wc_pl_style.css', EBDN_FILE_FULLNAME), array(), $plugin_data['Version']);
            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_script('ebdn-wc-pl-script', plugins_url('assets/js/wc_pl_script.js', EBDN_FILE_FULLNAME), array(), $plugin_data['Version']);
        }

        public function scripts()
        {
            global $post_type;

            if ($post_type === 'product') {

                foreach ($this->bulkActions as $action) {
                    $text = $this->bulkActionsText[$action];
                    ?>
                    <script type="text/javascript">
                        jQuery(document).ready(function () {
                            jQuery('<option>').val('<?php echo $action;?>').text('<?php _e($text)?>').appendTo("select[name='action']");
                            jQuery('<option>').val('<?php echo $action;?>').text('<?php _e($text)?>').appendTo("select[name='action2']");
                        });
                    </script>
                    <?php
                }

            }
        }

        public function bulkActions()
        {
            global $typenow;
            $post_type = $typenow;

            if ($post_type === 'product') {

                $wp_list_table = _get_list_table('WP_Posts_List_Table');
                $action = $wp_list_table->current_action();

                $allowed_actions = $this->bulkActions;
                if (!in_array($action, $allowed_actions, false)) {
                    return;
                }

                check_admin_referer('bulk-posts');

                // make sure ids are submitted.  depending on the resource type, this may be 'media' or 'ids'
                if (isset($_REQUEST['post'])) {
                    $post_ids = array_map('intval', $_REQUEST['post']);
                }

                if (empty($post_ids)) {
                    return;
                }

                $sendback = remove_query_arg(array_merge($allowed_actions, array('untrashed', 'deleted', 'ids')), wp_get_referer());
                if (!$sendback) {
                    $sendback = admin_url("edit.php?post_type=$post_type");
                }

                $pagenum = $wp_list_table->get_pagenum();
                $sendback = add_query_arg('paged', $pagenum, $sendback);

                if ($action === 'ebdn_product_update_manual') {

                    $updated = 0;
                    $skiped = 0;

                    foreach ($post_ids as $post_id) {
                        $result = $this->performUpdate($post_id);
                        if ($result === -1) {
                            $skiped++;
                        } else if (!$result) {
                            wp_die(__('Error updating product.'));
                        } else {
                            $updated++;
                        }
                    }

                    $sendback = add_query_arg(array('ebdn_updated' => $updated, 'ebdn_skiped' => $skiped, 'ids' => implode(',', $post_ids)), $sendback);
                }

                $sendback = apply_filters('ebdn_wcpl_bulk_actions_perform', $sendback, $action, $post_ids);

                $sendback = remove_query_arg(array('action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status', 'post', 'bulk_edit', 'post_view'), $sendback);

                wp_redirect($sendback);
                exit();
            }
        }

        public function adminNotices()
        {
            global $post_type, $pagenow;

            if ($pagenow === 'edit.php' && $post_type === 'product' && isset($_REQUEST['ebdn_updated']) && (int)$_REQUEST['ebdn_updated']) {


                $message = sprintf(_n('Product updated.', '%s products updated.', $_REQUEST['ebdn_updated']), number_format_i18n($_REQUEST['ebdn_updated']));

                if (isset($_REQUEST['ebdn_skiped']) && (int)$_REQUEST['ebdn_skiped']) {
                    $message .= ' And ' . sprintf(_n('one product skiped.', '%s products skiped.', $_REQUEST['ebdn_skiped']), number_format_i18n($_REQUEST['ebdn_skiped']));
                }

                echo "<div class=\"updated\"><p>{$message}</p></div>";
            }
        }

        public function performUpdate($post_id)
        {
            $external_id = get_post_meta($post_id, "external_id", true);

            if ($external_id) {
                ebdn_update_price_proc($post_id, false);
                return true;
            }
            return -1;
        }
    }
}

new EBDN_WooCommerce_ProductList();