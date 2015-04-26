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

namespace XLite\Module\CDev\FeaturedProducts\Controller\Admin;

/**
 * Featured products
 */
class FProductSelections extends \XLite\Controller\Admin\ProductSelections
{
    protected $categoryCache = null;

    /**
     * Check if the product id which will be displayed as "Already added"
     *
     * @return array
     */
    public function isExcludedProductId($productId)
    {
        return (bool)\XLite\Core\Database::getRepo('XLite\Module\CDev\FeaturedProducts\Model\FeaturedProduct')->findOneBy(array(
            'category' => \XLite\Core\Request::getInstance()->id ?: $this->getCondition('categoryId'),
            'product'  => $productId,
        ));
    }

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return ($this->getCategoryId() != $this->getRootCategoryId())
            ? ($this->getCategory() ? $this->getCategoryTitle() : parent::getTitle())
            : $this->getFrontPageTitle();
    }

    /**
     * Defines the title if it is front page (no category is provided)
     *
     * @return string
     */
    protected function getFrontPageTitle()
    {
        return static::t('Add featured products for the front page');
    }

    /**
     * Defines the title if the category is provided
     *
     * @return string
     */
    protected function getCategoryTitle()
    {
        return static::t('Add featured products for "X"', array('category' => $this->getCategoryName()));
    }

    /**
     * Returns the category object if the category_id parameter is provided
     *
     * @return \XLite\Model\Category
     */
    protected function getCategory()
    {
        if (is_null($this->categoryCache)) {
            $this->categoryCache = \XLite\Core\Database::getRepo('XLite\Model\Category')->find($this->getCategoryId());
        }
        return $this->categoryCache;
    }

    /**
     * Returns the stylish category path or space if there is no valid category
     *
     * @return string
     */
    protected function getCategoryName()
    {
        return $this->getCategory()
            ? implode(':', \XLite\Core\Database::getRepo('XLite\Model\Category')->getCategoryNamePath($this->getCategoryId()))
            : '';
    }
}
