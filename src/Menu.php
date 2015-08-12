<?php

namespace Tiga\Framework;

/**
 * Helper class to set active menu.
 */
class Menu
{
    private $overideMenus;

    private $configClass;

    public function addoverideMenu($config)
    {
        array_push($this->overideMenus, $config);
    }

    public function addConfigClass($key, $config)
    {
        $this->configClass[$key] = $config;
    }

    public function hook()
    {
        add_filter('wp_nav_menu_objects', array($this, 'process'));
    }

    public function process($menus)
    {
        $map = array();

        foreach ($menus as $key => $menu) {
            $found = false;

            $map[$menu->ID] = $key;

            foreach ($this->overideMenus as $overideMenu) {
                if ($overideMenu['url'] == $menu->url) {
                    array_push($menus[$key]->classes, 'current_page_item');
                    $found = true;
                    break;
                }
            }

            if ($found) {
                break;
            }
        }

        return $menus;
    }
}
