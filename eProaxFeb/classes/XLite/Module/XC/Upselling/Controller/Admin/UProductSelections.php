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

namespace XLite\Module\XC\Upselling\Controller\Admin;

/**
 * Upselling products
 */
class UProductSelections extends \XLite\Controller\Admin\ProductSelections
{
    /**
     * Check if the product id which will be displayed as "Already added"
     *
     * @param integer $productId Product ID
     *
     * @return array
     */
    public function isExcludedProductId($productId)
    {
        $upsellingProduct = array(
            'parentProduct' => \XLite\Core\Request::getInstance()->product_id,
            'product'       => $productId,
        );

        return \XLite\Core\Request::getInstance()->product_id == $productId
            || (bool)\XLite\Core\Database::getRepo('XLite\Module\XC\Upselling\Model\UpsellingProduct')
                ->findOneBy($upsellingProduct);
    }

    /**
     * Specific title for the excluded product
     * By default it is 'Already added'
     *
     * @param integer $productId Product ID
     *
     * @return string
     */
    public function getTitleExcludedProduct($productId)
    {
        return \XLite\Core\Request::getInstance()->product_id == $productId
            ? static::t('You cannot choose this product')
            : parent::getTitleExcludedProduct($productId);
    }

    /**
     * Specific CSS class for the image of the excluded product.
     * You can check the Font Awesome CSS library if you want some specific icons
     *
     * @param integer $productId Product ID
     *
     * @return string
     */
    public function getStyleExcludedProduct($productId)
    {
        return \XLite\Core\Request::getInstance()->product_id == $productId
            ? 'fa-ban'
            : parent::getStyleExcludedProduct($productId);
    }
}
