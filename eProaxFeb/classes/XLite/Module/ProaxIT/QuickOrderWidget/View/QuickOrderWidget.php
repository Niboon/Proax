<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\QuickOrderWidget\View;

/**
 * Form widget for Quick Orders
 *
 * @ListChild (list="sidebar.first", zone="customer", weight="170")
 * @ListChild (list="sidebar.single", zone="customer", weight="170")
 */
class QuickOrderWidget extends \XLite\View\SideBarBox
{
    /**
     * Get widget templates directory
     *
     * @return string
     */
    protected function getDir()
    {
        return 'modules/ProaxIT/QuickOrderWidget/';
    }

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'modules/ProaxIT/QuickOrderWidget/style.css';

        return $list;
    }

    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = 'modules/ProaxIT/QuickOrderWidget/addMulti.js';

        return $list;
    }

    /**
     * Get widget title
     *
     * @return string
     */
    protected function getHead()
    {
        return \XLite\Core\Translation::getInstance()->translate('Quick Order Form');
    }

    /**
     * Register the CSS classes for this block
     *
     * @return string
     */
    protected function getBlockClasses()
    {
        return parent::getBlockClasses() . ' quick-order-widget';
    }

    /**
     * Return list of targets allowed for this widget
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $result = parent::getAllowedTargets();

        $result[] = 'main';
        $result[] = 'category';
        $result[] = 'shipping';
        $result[] = 'order_list';
        $result[] = 'contact_us';

        return $result;
    }

    /**
     * @return int number of fields to show on the form as set in the module settings
     */
    public function getNumberOfFields() {
        $config = \XLite\Core\Config::getInstance()->ProaxIT->QuickOrderWidget;
        return $config->number_of_fields ? : 10;
    }

    /**
     * @return int number of fields to show on the form as set in the module settings
     */
    public function getNumberOfFieldsArray() {
        $arr = [];
        $num = $this->getNumberOfFields();
        for ($i = 0; $i < $num; $i++) $arr[] = $i;
        return $arr;
    }


}
