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

namespace XLite\Module\CDev\ProductAdvisor\View\ItemsList\Product\Customer;

/**
 * Products items list extension
 */
abstract class ACustomer extends \XLite\View\ItemsList\Product\Customer\ACustomer implements \XLite\Base\IDecorator
{
    /**
     * Allowed sort criterions
     */
    const SORT_BY_MODE_DATE  = 'p.arrivalDate';

    /**
     * Define and set widget attributes; initialize widget
     *
     * @param array $params Widget params OPTIONAL
     *
     * @return void
     */
    public function __construct(array $params = array())
    {
        parent::__construct($params);

        $this->sortByModes = array(
            static::SORT_BY_MODE_DATE => 'Newest first',
        ) + $this->sortByModes;
    }

    /**
     * Get products 'sort by' fields
     *
     * @return array
     */
    protected function getSortByFields()
    {
        return array(
            'newest' => static::SORT_BY_MODE_DATE,
        ) + parent::getSortByFields();
    }

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'modules/CDev/ProductAdvisor/style.css';

        return $list;
    }

    /**
     * Defines the CSS class for sorting order arrow
     *
     * @param string $sortBy
     *
     * @return string
     */
    protected function getSortArrowClassCSS($sortBy)
    {
        return static::SORT_BY_MODE_DATE === $this->getSortBy() ? '' : parent::getSortArrowClassCSS($sortBy);
    }

    /**
     * getSortOrder
     *
     * @return string
     */
    protected function getSortOrder()
    {
        return static::SORT_BY_MODE_DATE === $this->getSortBy() ? static::SORT_ORDER_DESC : parent::getSortOrder();
    }

    /**
     * Add 'coming-soon' class attribute for the product cell
     *
     * @param \XLite\Model\Product $product The product to look for
     *
     * @return string
     */
    public function getProductCellClass(\XLite\Model\Product $product)
    {
        $result = parent::getProductCellClass($product);

        if ($product->isUpcomingProduct()) {
            $result = preg_replace('/out-of-stock/', '', $result) . ' coming-soon';
        }

        return $result;
    }

    /**
     * Return product labels
     *
     * @param \XLite\Model\Product $product The product to look for
     *
     * @return array
     */
    protected function getLabels(\XLite\Model\Product $product)
    {
        $labels = parent::getLabels($product);

        $targets = array(
            \XLite\Module\CDev\ProductAdvisor\View\ANewArrivals::WIDGET_TARGET_NEW_ARRIVALS,
            \XLite\Module\CDev\ProductAdvisor\View\AComingSoon::WIDGET_TARGET_COMING_SOON,
        );

        if (!in_array(static::getWidgetTarget(), $targets)) {
            // Add ProductAdvisor's labels into the begin of labels list
            $labels = array_reverse($labels);
            $labels += \XLite\Module\CDev\ProductAdvisor\Main::getLabels($product);
            $labels = array_reverse($labels);
        }

        return $labels;
    }
}
