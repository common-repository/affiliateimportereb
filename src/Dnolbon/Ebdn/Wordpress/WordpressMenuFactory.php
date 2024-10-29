<?php
namespace Dnolbon\Ebdn\Wordpress;

class WordpressMenuFactory
{
    /**
     * @param string $pageTitle
     * @param string $capability
     * @param string $menuSlug
     * @param array $params
     * @return WordpressMenu
     */
    public static function addMenu($pageTitle, $capability, $menuSlug, $params = [])
    {
        return new WordpressMenu(
            $pageTitle,
            $capability,
            $menuSlug,
            $params
        );
    }
}
