<?php

if (!class_exists('EBDN_WooCommerce')) {

    class EBDN_WooCommerce
    {
        /**
         * @param EBDN_Goods $goods
         * @param array $params
         * @return mixed|void
         */
        public static function addPost($goods, $params = array())
        {
            /**
             * @var wpdb $wpdb
             */
            global $wpdb;

            try {
                set_time_limit(500);
            } catch (Exception $e) {

            }

            do_action('ebdn_woocommerce_before_addpost', $goods);

            $result = array('state' => 'ok', 'message' => '');

            $product_status = get_option('ebdn_default_status', 'publish');
            $product_status = isset($params['import_status']) && $params['import_status'] ? $params['import_status'] : $product_status;

            $post = array(
                'post_title' => $goods->getProp('title'),
                'post_content' => EBDN_Goods::normalized($goods->getProp('description')),
                'post_status' => $product_status,
                'post_name' => $goods->getProp('title'),
                'post_type' => 'product'
            );
            $product_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='external_id' AND meta_value='%s' LIMIT 1", $goods->getId()));
            $post_id = $product_id;
            if (!$product_id) {
                $post_id = wp_insert_post($post);
            }
            $categories = EBDN_WooCommerce::buildCategories($goods);
            $product_type = get_option('ebdn_default_type', 'simple');

            wp_set_object_terms($post_id, $categories, 'product_cat');
            wp_set_object_terms($post_id, $product_type, 'product_type');
            update_post_meta($post_id, '_stock_status', 'instock');
            update_post_meta($post_id, '_sku', (string)$goods->external_id);
            update_post_meta($post_id, '_product_url', $goods->detail_url);

            update_post_meta($post_id, 'import_type', $goods->type);
            update_post_meta($post_id, 'external_id', (string)$goods->getId());
            update_post_meta($post_id, 'seller_url', $goods->seller_url);
            update_post_meta($post_id, 'product_url', $goods->detail_url);
            update_post_meta($post_id, 'ebdn_import', 1);

            EBDN_WooCommerce::updatePrice($post_id, $goods);

            $additional_meta = EBDN_Goods::getNormalizedValue($goods, 'additional_meta');

            if (isset($additional_meta['quantity'])) {
                update_post_meta($post_id, '_manage_stock', 'yes');
                update_post_meta($post_id, '_visibility', 'visible');
                update_post_meta($post_id, '_stock', (int)$additional_meta['quantity']);
            } else {
                $min_q = (int)get_option('ebdn_min_product_quantity', 5);
                $max_q = (int)get_option('ebdn_max_product_quantity', 10);
                $min_q = $min_q ? $min_q : 1;
                $max_q = ($max_q && $max_q > $min_q) ? $max_q : $min_q;
                $quantity = mt_rand($min_q, $max_q);

                if ($max_q > 1) {
                    update_post_meta($post_id, '_manage_stock', 'yes');
                    update_post_meta($post_id, '_stock', $quantity);
                    update_post_meta($post_id, '_visibility', 'visible');
                }
            }

            if (isset($additional_meta['filters']) && $additional_meta['filters']) {
                update_post_meta($post_id, '_ebdn_filters', $additional_meta['filters']);
            }

            if (isset($additional_meta['detail_url']) && $additional_meta['detail_url']) {
                update_post_meta($post_id, 'original_product_url', $additional_meta['detail_url']);
            }

            if (isset($additional_meta['ship']) && EBDN_Goods::normalized($additional_meta['ship'])) {
                update_post_meta($post_id, 'ship_price', $additional_meta['ship']);
            }

            if ($additional_meta && is_array($additional_meta)) {
                if (isset($additional_meta['attribute']) && $additional_meta['attribute']) {
                    EBDN_WooCommerce::setAttributes($post_id, $additional_meta['attribute']);
                }
                if (isset($additional_meta['discount_perc']) && strlen(trim((string)$additional_meta['discount_perc'])) > 0) {
                    update_post_meta($post_id, 'discount_perc', $additional_meta['discount_perc']);
                }
            }

            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';

            $thumb_url = $goods->getProp('image');
            if ($thumb_url) {
                $thumb_id = EBDN_WooCommerce::imageAttacher($thumb_url, $product_id);
                set_post_thumbnail($post_id, $thumb_id);
            }

            $images_url = $goods->getAllPhotos();

            $images_limit = (int)get_option('ebdn_import_product_images_limit');

            $image_gallery_ids = '';
            $cnt = 0;
            foreach (array_slice($images_url, 1) as $image_url) {
                if ($thumb_url !== $image_url) {
                    if (!$images_limit || ($cnt++) < $images_limit) {
                        try {
                            $image_gallery_ids .= EBDN_WooCommerce::imageAttacher($image_url, $post_id) . ',';
                        } catch (Exception $e) {
                            $result['state'] = 'warn';
                            $result['message'] = "\nimg_warn: $image_url";
                        }
                    }
                }
            }
            update_post_meta($post_id, '_product_image_gallery', $image_gallery_ids);

            return apply_filters('ebdn_woocommerce_after_addpost', $result, $post_id, $goods);
        }

        public static function updatePrice($post_id, $goods)
        {
            if (!$goods->user_regular_price) {
                update_post_meta($post_id, '_price', $goods->user_price);
                update_post_meta($post_id, '_regular_price', $goods->user_price);
                delete_post_meta($post_id, '_sale_price');
            } else {
                if (abs($goods->user_price - $goods->user_regular_price) < 0.001) {
                    update_post_meta($post_id, '_price', $goods->user_price);
                    update_post_meta($post_id, '_regular_price', $goods->user_price);
                    delete_post_meta($post_id, '_sale_price');
                } else {
                    update_post_meta($post_id, '_regular_price', $goods->user_regular_price);
                    update_post_meta($post_id, '_sale_price', $goods->user_price);
                    update_post_meta($post_id, '_price', $goods->user_price);
                }
            }
        }

        public static function updatePost()
        {
            return array('state' => 'ok', 'message' => '');
        }

        public static function buildCategories($goods)
        {
            return $goods->link_category_id ? (int)$goods->link_category_id : EBDN_Goods::getNormalizedValue($goods, 'category_name');
        }

        public static function imageAttacher($image_url, $post_id)
        {
            $image = EBDN_WooCommerce::downloadUrl($image_url);
            if ($image) {
                $file_array = array(
                    'name' => basename($image),
                    'size' => filesize($image),
                    'tmp_name' => $image
                );
                return media_handle_sideload($file_array, $post_id);
            }
            return false;
        }

        public static function downloadUrl($url)
        {
            $wp_upload_dir = wp_upload_dir();
            $parsed_url = parse_url($url);
            $pathinfo = pathinfo($parsed_url['path']);

            $dest_filename = wp_unique_filename($wp_upload_dir['path'], mt_rand() . '.' . $pathinfo['extension']);

            $dest_path = $wp_upload_dir['path'] . '/' . $dest_filename;

            $response = ebdn_remote_get($url);
            if (is_wp_error($response)) {
                return false;
            } elseif (!in_array($response['response']['code'], [404, 403], false)) {
                file_put_contents($dest_path, $response['body']);
            }

            if (!file_exists($dest_path)) {
                return false;
            } else {
                return $dest_path;
            }
        }

        public static function setAttributes($post_id, $attributes)
        {
            $extended_attribute = get_option('ebdn_import_extended_attribute', false);

            if ($extended_attribute) {
                foreach ($attributes as $name => $value) {
                    self::saveOneAttribute($value);
                }
            } else {
                $name = array_column($attributes, 'name');
                $count = array_count_values($name);
                $duplicate = array_unique(array_diff_assoc($name, array_unique($name)));
                $product_attributes = '';

                foreach ($attributes as $name => $value) {

                    if (isset($duplicate[$name + 1])) {
                        $val = array();
                        for ($i = 0; $i < $count[$value['name']]; $i++) {
                            $val[] = $attributes[$name + $i]['value'];
                        }
                        $product_attributes[str_replace(' ', '-', $value['name'])] = array(
                            'name' => $value['name'],
                            'value' => implode(', ', $val),
                            'position' => 0,
                            'is_visible' => 1,
                            'is_variation' => 0,
                            'is_taxonomy' => 0
                        );
                    } elseif (!in_array($value['name'], $duplicate, false)) {
                        $product_attributes[str_replace(' ', '-', $value['name'])] = array(
                            'name' => $value['name'],
                            'value' => $value['value'],
                            'position' => 0,
                            'is_visible' => 1,
                            'is_variation' => 0,
                            'is_taxonomy' => 0
                        );
                    }
                }

                update_post_meta($post_id, '_product_attributes', $product_attributes);
            }
        }

        private static function saveOneAttribute($attr_data)
        {
            /**
             * @var wpdb $wpdb
             */
            global $wpdb;

            $attribute = array(
                'attribute_label' => wc_clean(stripslashes($attr_data['name'])),
                'attribute_name' => wc_sanitize_taxonomy_name(stripslashes($attr_data['name'])),
                'attribute_type' => 'select',
                'attribute_orderby' => '',
                'attribute_public' => 0
            );

            if (!taxonomy_exists(wc_attribute_taxonomy_name($attribute['attribute_name']))) {
                $wpdb->insert($wpdb->prefix . 'woocommerce_attribute_taxonomies', $attribute);
            }

            flush_rewrite_rules();

            delete_transient('wc_attribute_taxonomies');
        }

        public static function randomQuantity()
        {
            $min_q = (int)get_option('ebdn_min_product_quantity', 5);
            $max_q = (int)get_option('ebdn_max_product_quantity', 10);
            $min_q = $min_q ? $min_q : 1;
            $max_q = ($max_q && $max_q > $min_q) ? $max_q : $min_q;
            return mt_rand($min_q, $max_q);
        }


    }

}

if (!function_exists('array_column')) {

    /**
     * Returns the values from a single column of the input array, identified by
     * the $columnKey.
     *
     * Optionally, you may provide an $indexKey to index the values in the returned
     * array by the values from the $indexKey column in the input array.
     *
     * @param array $input A multi-dimensional array (record set) from which to pull
     *                     a column of values.
     * @param mixed $columnKey The column of values to return. This value may be the
     *                         integer key of the column you wish to retrieve, or it
     *                         may be the string key name for an associative array.
     * @param mixed $indexKey (Optional.) The column to use as the index/keys for
     *                        the returned array. This value may be the integer key
     *                        of the column, or it may be the string key name.
     * @return array
     */
    function array_column($input = null, $columnKey = null, $indexKey = null)
    {
        // Using func_get_args() in order to check for proper number of
        // parameters and trigger errors exactly as the built-in array_column()
        // does in PHP 5.5.
        $argc = func_num_args();
        $params = func_get_args();

        if ($argc < 2) {
            trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
            return null;
        }

        if (!is_array($params[0])) {
            trigger_error('array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given', E_USER_WARNING);
            return null;
        }

        if (!is_int($params[1]) && !is_float($params[1]) && !is_string($params[1]) && $params[1] !== null && !(is_object($params[1]) && method_exists($params[1], '__toString'))
        ) {
            trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
            return null;
        }

        if (isset($params[2]) && !is_int($params[2]) && !is_float($params[2]) && !is_string($params[2]) && !(is_object($params[2]) && method_exists($params[2], '__toString'))
        ) {
            trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
            return null;
        }

        $paramsInput = $params[0];
        $paramsColumnKey = ($params[1] !== null) ? (string)$params[1] : null;

        $paramsIndexKey = null;
        if (isset($params[2])) {
            if (is_float($params[2]) || is_int($params[2])) {
                $paramsIndexKey = (int)$params[2];
            } else {
                $paramsIndexKey = (string)$params[2];
            }
        }

        $resultArray = array();

        foreach ($paramsInput as $row) {

            $key = $value = null;
            $keySet = $valueSet = false;

            if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                $keySet = true;
                $key = (string)$row[$paramsIndexKey];
            }

            if ($paramsColumnKey === null) {
                $valueSet = true;
                $value = $row;
            } elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
                $valueSet = true;
                $value = $row[$paramsColumnKey];
            }

            if ($valueSet) {
                if ($keySet) {
                    $resultArray[$key] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }
        }

        return $resultArray;
    }

}