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

namespace XLite\Module\XC\ProductFilter\Controller\Customer;

/**
 * Category filter
 *
 */
class CategoryFilter extends \XLite\Controller\Customer\Category
{
    /**
     * Check controller visibility
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && 1 < $this->getCategory()->getProductsCount();
    }

    /**
     * Do action filter
     *
     * @return void
     */
    protected function doActionFilter()
    {
        $sessionCell = $this->isAJAX()
            ? \XLite\Module\XC\ProductFilter\View\ItemsList\Product\Customer\Category\CategoryFilter::getSessionCellName()
            : \XLite\View\ItemsList\Product\Customer\Category\Main::getSessionCellName();

        $data = \XLite\Core\Session::getInstance()->$sessionCell;

        if (!is_array($data)) {
            $data = array();
        }

        $data['filter'] = \XLite\Core\Request::getInstance()->filter;

        if (!$this->isAJAX()) {
            $sessionCell = \XLite\Module\XC\ProductFilter\View\ItemsList\Product\Customer\Category\CategoryFilter::getSessionCellName();
        }

        \XLite\Core\Session::getInstance()->$sessionCell = $data;

        $this->setReturnURL(
            $this->buildURL(
                'category_filter',
                '',
                array('category_id' => \XLite\Core\Request::getInstance()->category_id)
            )
        );
    }

    /**
     * Check if redirect to clean URL is needed
     *
     * @return boolean
     */
    protected function isRedirectToCleanURLNeeded()
    {
        return false;
    }
}
