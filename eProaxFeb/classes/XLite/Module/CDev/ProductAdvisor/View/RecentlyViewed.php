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
 * Recenly viewed products widget
 *
 * @ListChild (list="center.bottom", zone="customer", weight="700")
 * @ListChild (list="sidebar.single", zone="customer", weight="180")
 * @ListChild (list="sidebar.second", zone="customer", weight="130")
 */
class RecentlyViewed extends \XLite\View\ItemsList\Product\Customer\ACustomer
{
    /**
     * Widget parameter
     */
    const PARAM_MAX_ITEMS_TO_DISPLAY = 'maxItemsToDisplay';


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

        return $result;
    }

    /**
     * Returns CSS classes for the container element
     *
     * @return string
     */
    public function getListCSSClasses()
    {
        return parent::getListCSSClasses() . ' recently-viewed-products';
    }

    /**
     * Initialize widget (set attributes)
     *
     * @param array $params Widget params
     *
     * @return void
     */
    public function setWidgetParams(array $params)
    {
        parent::setWidgetParams($params);

        unset($this->widgetParams[\XLite\View\Pager\APager::PARAM_SHOW_ITEMS_PER_PAGE_SELECTOR]);
        unset($this->widgetParams[\XLite\View\Pager\APager::PARAM_ITEMS_PER_PAGE]);
    }

    /**
     * Get title
     *
     * @return string
     */
    protected function getHead()
    {
        return \XLite\Core\Translation::getInstance()->translate('Recently viewed');
    }

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            self::PARAM_MAX_ITEMS_TO_DISPLAY => new \XLite\Model\WidgetParam\Int(
                'Maximum products to display', $this->getMaxCountInBlock(), true, true
            ),
        );

        $this->widgetParams[self::PARAM_WIDGET_TYPE]->setValue(self::WIDGET_TYPE_SIDEBAR);
        $this->widgetParams[self::PARAM_DISPLAY_MODE]->setValue(self::DISPLAY_MODE_TEXTS);

        unset($this->widgetParams[self::PARAM_SHOW_DISPLAY_MODE_SELECTOR]);
        unset($this->widgetParams[self::PARAM_SHOW_SORT_BY_SELECTOR]);
    }

    /**
     * Return class name for the list pager
     *
     * @return string
     */
    protected function getPagerClass()
    {
        return '\XLite\Module\CDev\ProductAdvisor\View\Pager\Pager';
    }

    /**
     * Return products list
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return mixed
     */
    protected function getData(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        $productIds = \XLite\Module\CDev\ProductAdvisor\Main::getProductIds();

        $maxCount = $this->getMaxItemsCount();

        if ($countOnly) {
            // Calculate count recently viewed products w/o gathering them from database
            $result = min(
                \XLite\Core\Database::getRepo('XLite\Model\Product')->findByProductIds($productIds, true),
                $maxCount
            );

        } else {
            if (count($productIds) > $maxCount) {
                // Cut productIds array up to the limit
                array_splice($productIds, $maxCount);
            }
            $products = $result = array();
            // Get products list
            foreach (
                \XLite\Core\Database::getRepo('XLite\Model\Product')->findByProductIds($productIds, false) as $product
            ) {
                $products[$product->getProductId()] = $product;
            }
            // Sort out products list by its order in the productIds array
            foreach ($productIds as $productId) {
                if (isset($products[$productId])) {
                    $result[] = $products[$productId];
                }
            }
            unset($products);
        }

        return $result;
    }

    /**
     * Returns maximum allowed items count
     *
     * @return integer
     */
    protected function getMaxItemsCount()
    {
        return $this->getParam(self::PARAM_MAX_ITEMS_TO_DISPLAY) ?: $this->getMaxCountInBlock();
    }

    /**
     * Returns maximum allowed items count
     *
     * @return integer
     */
    protected function getMaxCountInBlock()
    {
        return intval(\XLite\Core\Config::getInstance()->CDev->ProductAdvisor->rv_max_count_in_block);
    }

    /**
     * Return template of New arrivals widget. It depends on widget type:
     * SIDEBAR/CENTER and so on.
     *
     * @return string
     */
    protected function getTemplate()
    {
        $template = parent::getTemplate();

        if (
            $template == $this->getDefaultTemplate()
            && self::WIDGET_TYPE_SIDEBAR == $this->getParam(self::PARAM_WIDGET_TYPE)
        ) {
            $template = self::TEMPLATE_SIDEBAR;
        }

        return $template;
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        $result = parent::isVisible()
            && \XLite\Core\Config::getInstance()->CDev->ProductAdvisor->rv_enabled
            && \XLite\Module\CDev\ProductAdvisor\Main::getProductIds();

        if ($result) {

            if (!\XLite\Core\CMSConnector::isCMSStarted()) {

                if (self::WIDGET_TYPE_SIDEBAR == $this->getParam(self::PARAM_WIDGET_TYPE)) {
                    $result = in_array($this->viewListName, array('sidebar.second', 'sidebar.single'));

                } elseif (self::WIDGET_TYPE_CENTER == $this->getParam(self::PARAM_WIDGET_TYPE)) {
                    $result = ('center.bottom' == $this->viewListName);
                }
            }
        }

        return $result;
    }

    /**
     * Cache availability
     *
     * @return boolean
     */
    protected function isCacheAvailable()
    {
        return false;
    }

    /**
     * Register the CSS classes for this block
     *
     * @return string
     */
    protected function getBlockClasses()
    {
        return parent::getBlockClasses() . ' block-recently-viewed';
    }
}
