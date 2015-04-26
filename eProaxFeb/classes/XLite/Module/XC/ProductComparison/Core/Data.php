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

namespace XLite\Module\XC\ProductComparison\Core;

/**
 * Data class
 *
 */
class Data extends \XLite\Base\Singleton
{
    /**
     * Products count
     *
     * @var integer
     */
    protected $productsCount;

    /**
     * Product ids
     *
     * @var array
     */
    protected $productIds;

    /**
     * Get products count
     *
     * @return integer
     */
    public function getProductsCount()
    {
        if (!isset($this->productsCount)) {
            $this->productsCount = count($this->getProducts());
        }

        return $this->productsCount;
    }

    /**
     * Add product id
     *
     * @param integer $productId Product id
     *
     * @return void
     */
    public function addProductId($productId)
    {
        $ids = $this->getProductIds();
        $ids[$productId] = $productId;
        $this->productIds = $ids;
        \XLite\Core\Session::getInstance()->productComparisonIds = $ids;
    }

    /**
     * Delete product id
     *
     * @param integer $productId Product id
     *
     * @return void
     */
    public function deleteProductId($productId)
    {
        $ids = $this->getProductIds();
        if (isset($ids[$productId])) {
            unset($ids[$productId]);
        }
        $this->productIds = $ids;
        \XLite\Core\Session::getInstance()->productComparisonIds = $ids;
    }

    /**
     * Clear list
     *
     * @return void
     */
    public function clearList()
    {
        $this->productIds = array();
        \XLite\Core\Session::getInstance()->productComparisonIds = array();
    }

    /**
     * Get product ids
     *
     * @return array
     */
    public function getProductIds()
    {
        if (!isset($this->productIds)) {
            $this->productIds = \XLite\Core\Session::getInstance()->productComparisonIds;
        }

        return is_array($this->productIds)
            ? $this->productIds
            : array();
    }

    /**
     * Get products
     *
     * @return array
     */
    public function getProducts()
    {
        return \XLite\Core\Database::getRepo('\XLite\Model\Product')->findByIds($this->getProductIds());
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        $count = $this->getProductsCount();

        return 1 >= $count
            ? static::t('Add other products to compare')
            : static::t(
                'X products selected',
                array(
                    'count' => $count
                )
            );
    }

}
