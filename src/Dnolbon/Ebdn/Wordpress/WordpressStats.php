<?php
namespace Dnolbon\Ebdn\Wordpress;

class WordpressStats
{
    public function __construct()
    {
        add_action('wp_ajax_ebdn_redirect', [$this, 'redirect']);
        add_action('wp_ajax_nopriv_aeidn_redirect', [$this, 'redirect']);
        add_action('woocommerce_product_add_to_cart_url', [$this, 'buildLink']);

        add_action('wp', [$this, 'registerHit'], 0);
        add_action('woocommerce_add_to_cart', [$this, 'addToCart'], 1, 3);
    }

    public function buildLink($link)
    {
        $url = admin_url('admin-ajax.php');
        $url .= '?action=ebdn_redirect&link=' . urlencode($link) . '&id=' . get_the_ID();
        return $url;
    }

    public function redirect()
    {
        $link = esc_url_raw(urldecode($_GET['link']));
        $id = sanitize_text_field($_GET['id']);
        if (!is_admin()) {
            WordpressDb::getInstance()->getDb()->insert(
                WordpressDb::getInstance()->getDb()->prefix . EBDN_TABLE_STATS,
                ['date' => date('Y-m-d'), 'product_id' => $id, 'quantity' => 1]
            );
        }
        $link = str_replace('&#038;', '&', $link);

        header('Location: ' . $link . '');
        exit();
    }

    public function registerHit()
    {
        if (!is_admin()) {
            global $post;
            if ($post) {
                $postId = (int)$post->ID;

                if ($postId <= 0) {
                    return false;
                }

                WordpressDb::getInstance()->getDb()->insert(
                    WordpressDb::getInstance()->getDb()->prefix . EBDN_TABLE_STATS,
                    ['date' => date('Y-m-d'), 'product_id' => $postId]
                );
            }
        }
    }

    public function addToCart($cartItemKey = '', $productId = 0, $quantity = 0)
    {

        if (!is_admin()) {
            $postId = $productId;

            if ($postId <= 0) {
                return false;
            }

            WordpressDb::getInstance()->getDb()->insert(
                WordpressDb::getInstance()->getDb()->prefix . EBDN_TABLE_STATS,
                ['date' => date('Y-m-d'), 'product_id' => $postId, 'quantity' => $quantity]
            );

            return true;
        }
    }
}
