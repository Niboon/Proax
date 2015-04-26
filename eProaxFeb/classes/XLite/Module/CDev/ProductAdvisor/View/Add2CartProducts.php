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

namespace XLite\Module\CDev\ProductAdvisor\View;

/**
 * Add2CartPopup products list class extension
 *
 * @LC_Dependencies("XC\Add2CartPopup")
 */
class Add2CartProducts extends \XLite\Module\XC\Add2CartPopup\View\Products implements \XLite\Base\IDecorator
{
    /**
     * Return products list: temporary disable coming-soon products to exclude them from result
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return mixed
     */
    protected function getData(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        $oldCsEnabled = \XLite\Core\Config::getInstance()->CDev->ProductAdvisor->cs_enabled;

        \XLite\Core\Config::getInstance()->CDev->ProductAdvisor->cs_enabled = false;

        $result = parent::getData($cnd, $countOnly);

        \XLite\Core\Config::getInstance()->CDev->ProductAdvisor->cs_enabled = $oldCsEnabled;

        return $result;
    }
}
