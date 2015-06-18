<?php

/** 
 * This class renders a navigation menu.
 */
class CNavigation {

    /**
     * GenerateMenu function generates nav element with menu item/url list.
     * @param array $items, string $pageID.
     * @return string, HTML string of menu. Css class "selected" indicates active 
     * menu choice.
     */
    public static function GenerateMenu($items, $pageID) {
        $html = "<ul>";
        foreach ($items as $key => $item) {
            $active = (isset($pageID)) && $pageID == $key ? 'active' : null;
            $html .= "<li><a href='{$item['url']}' class='{$active}'>{$item['text']}</a></li>";
        }
        $html .= "</ul>";
        return $html;
    }
}
