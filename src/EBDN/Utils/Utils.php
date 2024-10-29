<?php

if (!class_exists('EBDN_Utils')) {
    class EBDN_Utils
    {


        public static function removeTags($html, $tags = array())
        {
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            @$dom->loadHTML($html);
            libxml_use_internal_errors(false);
            $dom->formatOutput = true;

            if (!$tags) {
                $tags = array('script', 'head', 'meta', 'style', 'map', 'noscript', 'object');

                if (get_option('ebdn_remove_img_from_desc', false)) {
                    $tags[] = 'img';
                }
                if (get_option('ebdn_remove_link_from_desc', false)) {
                    $tags[] = 'a';
                }
            }
            foreach ($tags as $tag) {
                $elements = $dom->getElementsByTagName($tag);
                for ($i = $elements->length; --$i >= 0;) {
                    $e = $elements->item($i);
                    if ($tag === 'a') {
                        while ($e->hasChildNodes()) {
                            $child = $e->removeChild($e->firstChild);
                            $e->parentNode->insertBefore($child, $e);
                        }
                        $e->parentNode->removeChild($e);
                    } else {
                        $e->parentNode->removeChild($e);
                    }
                }
            }

            return preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $dom->saveHTML());
        }

        public static function getCategoriesTree()
        {
            $categories = get_terms('product_cat', array('hide_empty' => 0, 'hierarchical' => true));
            $categories = json_decode(json_encode($categories), TRUE);
            $categories = EBDN_Utils::buildCategoriesTree($categories, 0);
            return $categories;
        }

        private static function buildCategoriesTree($all_cats, $parent_cat, $level = 1)
        {
            $res = array();
            foreach ($all_cats as $c) {
                if ($c['parent'] === $parent_cat) {
                    $c['level'] = $level;
                    $res[] = $c;
                    $child_cats = EBDN_Utils::buildCategoriesTree($all_cats, $c['term_id'], $level + 1);
                    if ($child_cats) {
                        /** @noinspection SlowArrayOperationsInLoopInspection */
                        $res = array_merge($res, $child_cats);
                    }
                }
            }
            return $res;
        }

    }
}
