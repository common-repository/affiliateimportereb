<?php
//date_default_timezone_set('GMT');

if (!defined('EBDN_NAME')) {
    define('EBDN_NAME', 'Affiliate Eb');
}

if (!defined('EBDN_TABLE_LOG')) {
    define('EBDN_TABLE_LOG', 'ebdn_log');
}

if (!defined('EBDN_TABLE_BLACKLIST')) {
    define('EBDN_TABLE_BLACKLIST', 'ebdn_blacklist');
}

if (!defined('EBDN_TABLE_STATS')) {
    define('EBDN_TABLE_STATS', 'ebdn_stats');
}

if (!defined('EBDN_TABLE_GOODS')) {
    define('EBDN_TABLE_GOODS', 'ebdn_goods');
}

if (!defined('EBDN_TABLE_GOODS_ARCHIVE')) {
    define('EBDN_TABLE_GOODS_ARCHIVE', 'ebdn_goods_archive');
}

if (!defined('EBDN_TABLE_ACCOUNT')) {
    define('EBDN_TABLE_ACCOUNT', 'ebdn_account');
}

if (!defined('EBDN_TABLE_PRICE_FORMULA')) {
    define('EBDN_TABLE_PRICE_FORMULA', 'ebdn_price_formula');
}

if (!defined('EBDN_NO_IMAGE_URL')) {
    define('EBDN_NO_IMAGE_URL', plugins_url('assets/img', 'iconPlaceholder_96x96.gif'));
}

if (!defined('EBDN_DEL_COOKIES_FILE_AFTER')) {
    define('EBDN_DEL_COOKIES_FILE_AFTER', 86400);
}

$classPath = __DIR__ . '/src/EBDN';

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

include_once $classPath . '/Log/Log.php';

include_once $classPath . '/Goods/Goods.php';
include_once $classPath . '/Abstract/Account.php';
include_once $classPath . '/Abstract/Loader.php';
include_once $classPath . '/Abstract/Configurator.php';
include_once $classPath . '/WooCommerce/WooCommerce.php';
include_once $classPath . '/Prices/PriceFormula.php';

include_once $classPath . '/Utils/Utils.php';
include_once $classPath . '/Pages/DashboardPage.php';
include_once $classPath . '/Pages/SettingsPage.php';
include_once $classPath . '/WooCommerce/ProductList.php';
include_once $classPath . '/WooCommerce/OrderList.php';

include_once $classPath . '/Utils/Ajax.php';

$EBDN_GLOBAL_API_LIST = array();

if (!function_exists('ebdn_add_api')) {

    /**
     * @param EBDN_AbstractConfigurator $api_configurator
     */
    function ebdn_add_api($api_configurator)
    {
        global $EBDN_GLOBAL_API_LIST;
        if (!is_array($EBDN_GLOBAL_API_LIST)) {
            $EBDN_GLOBAL_API_LIST = array();
        }
        if ($api_configurator instanceof EBDN_AbstractConfigurator) {
            $find = false;
            foreach ($EBDN_GLOBAL_API_LIST as $tmp_api) {
                if ($tmp_api->getType() === $api_configurator->getType()) {
                    $find = true;
                    break;
                }
            }
            if (!$find) {
                $EBDN_GLOBAL_API_LIST[$api_configurator->getType()] = $api_configurator;
            }
        }
    }

}

/* include api modules */
foreach (glob(EBDN_ROOT_PATH . 'src/EBDN/Modules/*', GLOB_ONLYDIR) as $dir) {
    $file_list = scandir($dir . '/');
    $include_array = array();
    foreach ($file_list as $f) {
        if (is_file($dir . '/' . $f)) {
            $file_info = pathinfo($f);
            if ($file_info['extension'] === 'php') {
                $file_data = get_file_data($dir . '/' . $f, array('position' => '@position'));
                $include_array[$dir . '/' . $f] = (int)$file_data['position'];
            }
        }
    }
    asort($include_array);
    foreach ($include_array as $file => $p) {
        include_once $file;
    }
}
/* include api modules */

/* include addons */
$dirs = glob(EBDN_ROOT_PATH . 'addons/*', GLOB_ONLYDIR);
if ($dirs && is_array($dirs)) {
    foreach (glob(EBDN_ROOT_PATH . 'addons/*', GLOB_ONLYDIR) as $dir) {
        $file_list = scandir($dir . '/');
        foreach ($file_list as $f) {
            if (is_file($dir . '/' . $f)) {
                $file_info = pathinfo($f);
                if ($file_info['extension'] === 'php') {
                    include_once $dir . '/' . $f;
                }
            }
        }
    }
}
/* include addons */

if (!function_exists('ebdn_get_api_list')) {

    /**
     * @param bool $installed_only
     * @return EBDN_AbstractConfigurator[]
     */
    function ebdn_get_api_list($installed_only = false)
    {
        global $EBDN_GLOBAL_API_LIST;
        $api_list = array();

        /**
         * @var EBDN_AbstractConfigurator $api
         */
        foreach ($EBDN_GLOBAL_API_LIST as $api) {
            if ($api instanceof EBDN_AbstractConfigurator && (!$installed_only || $api->isInstaled())) {
                $api_list[$api->getType()] = $api;
            }
        }
        return $api_list;
    }

}

if (!function_exists('ebdn_get_api')) {

    /**
     * @param $type
     * @return EBDN_AbstractConfigurator
     */
    function ebdn_get_api($type)
    {
        /**
         * @var EBDN_AbstractConfigurator $api
         */
        foreach (ebdn_get_api_list() as $api) {
            if ($api->getType() === $type) {
                return $api;
            }
        }
        return null;
    }

}

if (!function_exists('ebdn_get_default_api')) {

    function ebdn_get_default_api()
    {
        $api_list = ebdn_get_api_list();

        /**
         * @var EBDN_AbstractConfigurator $api
         */
        foreach ($api_list as $api) {
            if ($api->isInstaled()) {
                return $api;
            }
        }
        return false;
    }

}

if (!function_exists('ebdn_get_root_menu_id')) {

    function ebdn_get_root_menu_id()
    {
        $default_api = ebdn_get_default_api();
        return EBDN_ROOT_MENU_ID . ($default_api ? ('-' . $default_api->getType()) : '');
    }

}

if (!function_exists('ebdn_get_loader')) {

    /**
     * @param $type
     * @return EBDN_AbstractLoader
     */
    function ebdn_get_loader($type)
    {
        $api_list = ebdn_get_api_list();
        /**
         * @var EBDN_AbstractConfigurator $api
         */
        foreach ($api_list as $api) {
            if ($api->getType() === $type && class_exists($api->getConfigValues('loader_class'))) {
                $class_name = $api->getConfigValues('loader_class');
                return apply_filters('ebdn_get_loader', new $class_name($api));
            }
        }
        return null;
    }

}

if (!function_exists('ebdn_get_account')) {

    function ebdn_get_account($type)
    {
        $api_list = ebdn_get_api_list();
        /**
         * @var EBDN_AbstractConfigurator $api
         */
        foreach ($api_list as $api) {
            if ($api->getType() === $type && class_exists($api->getConfigValues('account_class'))) {
                $class_name = $api->getConfigValues('account_class');
                return apply_filters('ebdn_get_account', new $class_name($api));
            }
        }
        return false;
    }

}

if (!function_exists('ebdn_get_api_path')) {

    function ebdn_get_api_path($api)
    {
        if ($api instanceof EBDN_AbstractConfigurator) {
            return EBDN_ROOT_PATH . 'src/EBDN/Modules/' . $api->getType() . '/';
        }
        return '';
    }

}

if (!function_exists('ebdn_get_api_url')) {

    function ebdn_get_api_url($api)
    {
        if ($api instanceof EBDN_AbstractConfigurator) {
            return EBDN_ROOT_URL . 'src/EBDN/Modules/' . $api->getType() . '/';
        }
        return false;
    }

}

if (!function_exists('ebdn_api_enqueue_style')) {

    /**
     * @param EBDN_AbstractConfigurator $api
     */
    function ebdn_api_enqueue_style($api)
    {
        $dirs = glob(ebdn_get_api_path($api) . 'styles/', GLOB_ONLYDIR);
        if ($dirs && is_array($dirs)) {
            foreach (glob(ebdn_get_api_path($api) . 'styles/', GLOB_ONLYDIR) as $dir) {
                $file_list = scandir($dir . '/');
                foreach ($file_list as $f) {
                    if (is_file($dir . '/' . $f)) {
                        $file_info = pathinfo($f);
                        if ($file_info['extension'] === 'css') {
                            wp_enqueue_style('ebdn-' . $api->getType() . '-' . $file_info['filename'], ebdn_get_api_url($api) . 'styles/' . $file_info['basename'], array(), $api->getConfigValues('version'));
                        }
                    }
                }
            }
        }
    }

}

if (!function_exists('ebdn_error_handler')) {

    function ebdn_error_handler($errno, $errstr, $errfile, $errline)
    {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        switch ($errno) {
            case E_USER_ERROR:
                $mess = "<b>ERROR</b> [$errno] $errstr<br />\n Fatal error on line $errline in file $errfile, PHP " . PHP_VERSION . ' (' . PHP_OS . ")<br />\n";
                throw new Exception($mess);
            case E_USER_WARNING:
                $mess = "<b>My WARNING</b> [$errno] $errstr<br />\n";
                throw new Exception($mess);

            case E_USER_NOTICE:
                $mess = "<b>My NOTICE</b> [$errno] $errstr<br />\n";
                throw new Exception($mess);

            default:
                $mess = "Unknown error[$errno] on line $errline in file $errfile: $errstr<br />\n";
                throw new Exception($mess);
        }
    }

}

if (!function_exists('ebdn_log')) {

    function ebdn_log($message)
    {
        if (WP_DEBUG === true) {
            if (is_array($message) || is_object($message)) {
                error_log(print_r($message, true));
            } else {
                error_log($message);
            }
        }
    }
}

if (!function_exists('ebdn_add_js_hook')) {

    function ebdn_add_js_hook(&$result, $hook_name, $params)
    {
        if ($result !== null || !$result) {
            $result = array();
        }

        if (!isset($result['js_hook'])) {
            $result['js_hook'] = array();
        } else if (!is_array($result['js_hook'])) {
            $result['js_hook'] = array($result['js_hook']);
        }
        $result['js_hook'][] = array('name' => $hook_name, 'params' => $params);

        return $result;
    }
}


if (!function_exists('ebdn_get_goods_by_post_id')) {

    function ebdn_get_goods_by_post_id($post_id)
    {
        $goods = false;
        if ($post_id) {
            $external_id = get_post_meta($post_id, 'external_id', true);
            if ($external_id) {
                $goods = new EBDN_Goods($external_id);
                $cats = wp_get_object_terms($post_id, 'product_cat');
                if ($cats && !is_wp_error($cats)) {
                    $goods->link_category_id = $cats[0]->term_id;
                    $goods->additional_meta = array();
                    $goods->additional_meta['detail_url'] = 'www.aliexpress.com/item//' . $goods->external_id . '.html';
                }
            }
        }
        return $goods;
    }
}


if (!function_exists('ebdn_get_sorted_products_ids')) {

    function ebdn_get_sorted_products_ids($sort_type, $ids_count)
    {

        $result = array();

        $api_type_list = array();
        $api_list = ebdn_get_api_list(true);
        /**
         * @var EBDN_AbstractConfigurator $api
         */
        foreach ($api_list as $api) {
            $api_type_list[] = $api->getType();
        }

        $ids0 = get_posts(array(
            'post_type' => 'product',
            'fields' => 'ids',
            'numberposts' => $ids_count,
            'meta_query' => array(
                array(
                    'key' => 'import_type',
                    'value' => $api_type_list,
                    'compare' => 'IN'
                ),
                array(
                    'key' => $sort_type,
                    'compare' => 'NOT EXISTS'
                )
            )
        ));

        foreach ($ids0 as $id) {
            $result[] = $id;
        }

        if (($ids_count - count($result)) > 0) {
            $res = get_posts(array(
                'post_type' => 'product',
                'fields' => 'ids',
                'numberposts' => $ids_count - count($result),
                'meta_query' => array(
                    array(
                        'key' => 'import_type',
                        'value' => $api_type_list,
                        'compare' => 'IN'
                    )
                ),
                'order' => 'ASC',
                'orderby' => 'meta_value',
                'meta_key' => $sort_type,
                //allow hooks
                'suppress_filters' => false
            ));

            foreach ($res as $id) {
                $result[] = $id;
            }
        }
        return $result;
    }

}

if (!function_exists('ebdn_remote_get')) {

    function ebdn_remote_get($url, $args = array())
    {
        add_filter('http_api_transports', 'ebdn_custom_curl_transport', 100, 3);

        $def_args = array('headers' => array('Accept-Encoding' => ''), 'timeout' => 30, 'user-agent' => 'Toolkit/1.7.3', 'sslverify' => false);

        if (!is_array($args)) {
            $args = array();
        }

        foreach ($def_args as $key => $val) {
            if (!isset($args[$key])) {
                $args[$key] = $val;
            }
        }

        return wp_remote_get($url, $args);
    }

}

if (!function_exists('ebdn_custom_curl_transport')) {
    function ebdn_custom_curl_transport($transports)
    {
        array_unshift($transports, 'ebdn_curl');
        return $transports;
    }
}

if (!function_exists('ebdn_cookies_file_path')) {
    function ebdn_cookies_file_path($proxy = '')
    {
        $proxy_path = $proxy ? ('_' . str_replace(array('.', ':'), '_', $proxy)) : '';
        $file_path = WP_CONTENT_DIR . '/ebdn_cookie' . $proxy_path . '.txt';

        if (EBDN_DEL_COOKIES_FILE_AFTER && file_exists($file_path)) {
            $time_upd = filemtime($file_path);

            if (abs(time() - $time_upd) > EBDN_DEL_COOKIES_FILE_AFTER) {
                unlink($file_path);
            }
        }

        return $file_path;
    }
}

if (!function_exists('ebdn_proxy_get')) {
    function ebdn_proxy_get()
    {
        $proxy = '';
        if (get_option('ebdn_use_proxy', false)) {
            $proxies_str = str_replace([' ', "\n"], ['', ';'], get_option('ebdn_proxies_list', ''));

            $arr_proxies = explode(';', $proxies_str);

            $arr_proxies = apply_filters('ebdn_get_proxy_list', $arr_proxies);

            $proxies = array();
            foreach ($arr_proxies as $k => $v) {
                $proxies[$k] = trim($v);
            }

            if ($proxies) {
                $proxy = $proxies[array_rand($proxies)];
            }
        }
        return $proxy;
    }
}