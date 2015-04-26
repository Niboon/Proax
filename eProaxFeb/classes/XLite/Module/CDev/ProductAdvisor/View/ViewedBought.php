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
 * 'Customers who viewed this product bought' widget
 *
 * @ListChild (list="center.bottom", zone="customer", weight="800")
 */
class ViewedBought extends \XLite\Module\CDev\ProductAdvisor\View\ABought
{
    /**
     * Returns CSS classes for the container element
     *
     * @return string
     */
    public function getListCSSClasses()
    {
        return parent::getListCSSClasses() . ' viewed-bought-products';
    }


    /**
     * Get title
     *
     * @return string
     */
    protected function getHead()
    {
        return \XLite\Core\Translation::getInstance()->translate('Customers who viewed this product bought');
    }

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function getMaxCount()
    {
        return intval(\XLite\Core\Config::getInstance()->CDev->ProductAdvisor->cvb_max_count_in_block);
    }

    /**
     * Returns true if block is enabled
     *
     * @return boolean
     */
    protected function isBlockEnabled()
    {
        return \XLite\Core\Config::getInstance()->CDev->ProductAdvisor->cvb_enabled;
    }

    /**
     * Return params list to use for search
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getSearchConditions(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        $cnd->{\XLite\Module\CDev\ProductAdvisor\Model\Repo\Product::P_VIEWED_PRODUCT_ID} = $this->getProductId();

        if (!$this->getParam(self::PARAM_SHOW_SORT_BY_SELECTOR)) {
            $cnd->{\XLite\Module\CDev\ProductAdvisor\Model\Repo\Product::P_ORDER_BY} = array('bp.count', 'DESC');
        }

        if (!$this->getParam(\XLite\View\Pager\APager::PARAM_SHOW_ITEMS_PER_PAGE_SELECTOR)) {
            $cnd->{\XLite\Module\CDev\ProductAdvisor\Model\Repo\Product::P_LIMIT}
                = array(0, $this->getParam(\XLite\View\Pager\APager::PARAM_MAX_ITEMS_COUNT));
        }

        return $cnd;
    }
}
