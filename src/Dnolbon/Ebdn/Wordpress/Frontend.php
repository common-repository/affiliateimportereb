<?php
namespace Dnolbon\Ebdn\Wordpress;

class Frontend
{
    public function init()
    {
        add_action('wp', [$this, 'registerHit'], 0);

        add_action('woocommerce_add_to_cart', [$this, 'addToCart'], 1, 3);
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
