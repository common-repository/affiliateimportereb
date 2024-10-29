<?php

if (!class_exists('EBDN_SettingsPage')) {

    class EBDN_SettingsPage
    {
        public function render()
        {
            $activePage = 'settings';
            include EBDN_ROOT_PATH . '/layout/toolbar.php';

            do_action('ebdn_before_settings_page');

            if (isset($_POST['setting_form'])) {
                $current_api_module = (isset($_POST['module']) && $_POST['module']) ? sanitize_text_field($_POST['module']) : '';

                if ($current_api_module === 'common') {
                    update_option('ebdn_currency_conversion_factor', isset($_POST['ebdn_currency_conversion_factor']) ? (float)$_POST['ebdn_currency_conversion_factor'] : 1);
                    update_option('ebdn_per_page', isset($_POST['ebdn_per_page']) ? (int)$_POST['ebdn_per_page'] : 1);
                    update_option('ebdn_default_type', isset($_POST['ebdn_default_type']) ? (int)$_POST['ebdn_default_type'] : 1);
                    update_option('ebdn_import_attributes', array_key_exists('ebdn_import_attributes', $_POST));

                    update_option('ebdn_remove_link_from_desc', array_key_exists('ebdn_remove_link_from_desc', $_POST));
                    update_option('ebdn_remove_img_from_desc', array_key_exists('ebdn_remove_img_from_desc', $_POST));

                    update_option('ebdn_import_product_images_limit', isset($_POST['ebdn_import_product_images_limit']) ? sanitize_text_field($_POST['ebdn_import_product_images_limit']) : '');

                    update_option('ebdn_min_product_quantity', isset($_POST['ebdn_min_product_quantity']) ? (int)$_POST['ebdn_min_product_quantity'] : 5);
                    update_option('ebdn_max_product_quantity', isset($_POST['ebdn_max_product_quantity']) ? (int)$_POST['ebdn_max_product_quantity'] : 10);

                    update_option('ebdn_use_proxy', isset($_POST['ebdn_use_proxy']));
                    update_option('ebdn_proxies_list', isset($_POST['ebdn_proxies_list']) ? sanitize_text_field($_POST['ebdn_proxies_list']) : '');

                    if (isset($_POST['ebdn_default_status'])) {
                        update_option('ebdn_default_status', (int)$_POST['ebdn_default_status']);
                    }


                    do_action('ebdn_save_common_settings', $_POST);
                } else {
                    $api_account = ebdn_get_account($current_api_module);
                    if ($api_account) {
                        $api_account->save(filter_input_array(INPUT_POST));
                    }
                    $api = ebdn_get_api($current_api_module);
                    if ($api) {
                        $api->saveSetting(filter_input_array(INPUT_POST));
                        do_action('ebdn_save_module_settings', $api, filter_input_array(INPUT_POST));
                    }
                }
            } else if (isset($_POST['shedule_settings'])) {
                $postData = filter_input_array(INPUT_POST);
                if (array_key_exists('shedule_settings', $postData)) {
                    update_option('ebdn_price_auto_update', isset($postData['ebdn_price_auto_update']));
                }
                update_option('ebdn_regular_price_auto_update', isset($postData['ebdn_regular_price_auto_update']));

                if (isset($postData['ebdn_not_available_product_status'])) {
                    update_option('ebdn_not_available_product_status', sanitize_text_field($postData['ebdn_not_available_product_status']));
                } else {
                    update_option('ebdn_not_available_product_status', 'trash');
                }

                if (isset($postData['ebdn_price_auto_update_period'])) {
                    update_option('ebdn_price_auto_update_period', sanitize_text_field($postData['ebdn_price_auto_update_period']));
                }

                if (isset($postData['ebdn_update_per_schedule'])) {
                    update_option('ebdn_update_per_schedule', (int)$postData['ebdn_update_per_schedule']);
                } else {
                    update_option('ebdn_update_per_schedule', 20);
                }

                $price_auto_update = get_option('ebdn_price_auto_update', false);
                if ($price_auto_update) {
                    wp_schedule_event(
                        time(),
                        get_option('ebdn_price_auto_update_period', 'daily'),
                        'ebdn_update_price_event'
                    );
                } else {
                    wp_clear_scheduled_hook('ebdn_update_price_event');
                }
                do_action('ebdn_save_common_settings', $_POST);
            } elseif (isset($_POST['language_settings'])) {
                update_option('ebdn_tr_ebay_language', sanitize_text_field($_POST['ebdn_tr_ebay_language']));

                update_option('ebdn_tr_ebay_bing_secret', sanitize_text_field($_POST['ebdn_tr_ebay_bing_secret']));

                update_option('ebdn_tr_ebay_bing_client_id', sanitize_text_field($_POST['ebdn_tr_ebay_bing_client_id']));
            }


            include EBDN_ROOT_PATH . '/layout/settings.php';
        }
    }
}
