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

namespace XLite\Module\CDev\SimpleCMS\View\FormField;

/**
 * "Visible for" selector
 *
 */
class VisibleFor extends \XLite\View\FormField\Inline\Base\Single
{
    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = 'modules/CDev/SimpleCMS/form_field/visible_for.js';

        return $list;
    }

    /**
     * getContainerClass
     *
     * @return string
     */
    protected function getContainerClass()
    {
        return parent::getContainerClass() . ' inline-visible-for';
    }

    /**
     * defineFieldClass
     *
     * @return string
     */
    protected function defineFieldClass()
    {
        return 'XLite\Module\CDev\SimpleCMS\View\FormField\Select\VisibleFor';
    }

    /**
     * Return field template
     *
     * @return string
     */
    protected function getViewTemplate()
    {
        return 'modules/CDev/SimpleCMS/form_field/visible_for_view.tpl';
    }

    /**
     * Title for the visible for value
     *
     * @return string
     */
    protected function getVisibleFormValue()
    {
        $values = $this->getValues();
        $value = $this->getEntity()->getVisibleFor();

        return isset($values[$value])
            ? $values[$value]
            : \XLite\Module\CDev\SimpleCMS\View\FormField\Select\VisibleFor::ANY_VISITORS;
    }

    /**
     * Get default options
     *
     * @return array
     */
    protected function getValues()
    {
        return array(
            'AL' => static::t(\XLite\Module\CDev\SimpleCMS\View\FormField\Select\VisibleFor::ANY_VISITORS),
            'A'  => static::t(\XLite\Module\CDev\SimpleCMS\View\FormField\Select\VisibleFor::ANONYMOUS_ONLY),
            'L'  => static::t(\XLite\Module\CDev\SimpleCMS\View\FormField\Select\VisibleFor::LOGGED_IN_ONLY),
        );
    }
}
