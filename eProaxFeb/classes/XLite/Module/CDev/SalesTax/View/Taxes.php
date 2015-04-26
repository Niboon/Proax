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

namespace XLite\Module\CDev\SalesTax\View;

/**
 * Taxes widget (admin)
 */
class Taxes extends \XLite\View\AView
{
    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'modules/CDev/SalesTax/admin.css';

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

        $list[] = 'modules/CDev/SalesTax/admin.js';

        return $list;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/CDev/SalesTax/edit.tpl';
    }

    /**
     * Get CSS classes for dialog block
     *
     * @return string
     */
    protected function getDialogCSSClasses()
    {
        $result = 'edit-sales-tax';

        if (\XLite\Core\Config::getInstance()->CDev->SalesTax->ignore_memberships) {
            $result .= ' no-memberships';
        }

        if ('P' != \XLite\Core\Config::getInstance()->CDev->SalesTax->taxableBase) {
            $result .= ' no-taxbase';
        }

        return $result;
    }

    /**
     * Return true if list of tax rates on shipping cost is displayed
     *
     * @return boolean
     */
    protected function isShippingRatesDisplayed()
    {
        $cnd = new \XLite\Core\CommonCell;
        $cnd->{\XLite\Module\CDev\SalesTax\Model\Repo\Tax\Rate::PARAM_TAXABLE_BASE}
            = \XLite\Module\CDev\SalesTax\Model\Tax\Rate::TAXBASE_SHIPPING;

        $ratesCount = \XLite\Core\Database::getRepo('XLite\Module\CDev\SalesTax\Model\Tax\Rate')->search($cnd, true);

        return 0 < $ratesCount;
    }
}
