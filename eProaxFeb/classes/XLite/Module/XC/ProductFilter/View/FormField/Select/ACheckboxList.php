<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * X-Cart
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the software license agreement
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.x-cart.com/license-agreement.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to licensing@x-cart.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not modify this file if you wish to upgrade X-Cart to newer versions
 * in the future. If you wish to customize X-Cart for your needs please
 * refer to http://www.x-cart.com/ for more information.
 *
 * @category  X-Cart 5
 * @author    Qualiteam software Ltd <info@x-cart.com>
 * @copyright Copyright (c) 2011-2015 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Module\XC\ProductFilter\View\FormField\Select;

/**
 * Checkbox list (abstract) 
 * 
 */
abstract class ACheckboxList extends \XLite\View\FormField\Select\Multiple
{
    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = $this->getDir() . '/style.css';

        return $list;
    }

    /**
     * Return default options list
     *
     * @return array
     */
    protected function getDefaultOptions()
    {
        return array();
    }

    /**
     * Return name of the folder with templates
     *
     * @return string
     */
    protected function getDir()
    {
        return 'modules/XC/ProductFilter/form_field/select/checkbox_list';
    }

    /**
     * Get label container class
     *
     * @return string
     */
    protected function getLabelContainerClass()
    {
        return parent::getLabelContainerClass()
            . $this->getCommonClass();
    }

    /**
     * Get value container class
     *
     * @return string
     */
    protected function getValueContainerClass()
    {
        return parent::getValueContainerClass()
            . ' checkbox-list'
            . $this->getCommonClass();
    }

    /**
     * Get value container class
     *
     * @return string
     */
    protected function getCommonClass()
    {
        $class = ' collapsible';
        $value = $this->getValue();
        if (
            !$value
            || !is_array($value)
            || isset($value[0])
        ) {
            $class .= ' collapsed';
        }

        return $class;
    }
}
