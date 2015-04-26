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

namespace XLite\Module\CDev\ProductAdvisor\Core;

/**
 * Add2CartPopup class
 *
 * @LC_Dependencies("XC\Add2CartPopup")
 */
class Add2CartPopup extends \XLite\Module\XC\Add2CartPopup\Core\Add2CartPopup implements \XLite\Base\IDecorator
{
    /**
     * Get products list
     *
     * @param integer $productId  Current product ID
     * @param array   $productIds Product ID which must be excluded from the search results
     * @param integer $maxCount   Maximum number of products
     *
     * @return array
     */
    public function getSourceCustomerBought($productId, $productIds, $maxCount)
    {
        $result = array();

        $profileIds = \XLite\Core\Database::getRepo('XLite\Model\Order')->findUsersBoughtProduct($productId);

        if ($profileIds) {

            $cnd = new \XLite\Core\CommonCell;

            $cnd->{\XLite\Module\CDev\ProductAdvisor\Model\Repo\Product::P_PROFILE_ID} = $profileIds;
            if ($productIds) {
                $cnd->{\XLite\Model\Repo\Product::P_EXCL_PRODUCT_ID} = $productIds;
            }
            $cnd->{\XLite\Module\CDev\ProductAdvisor\Model\Repo\Product::P_ORDER_BY} = array('cnt', 'DESC');
            $cnd->{\XLite\Module\CDev\ProductAdvisor\Model\Repo\Product::P_PA_GROUP_BY} = 'p.product_id';
            $cnd->{\XLite\Module\CDev\ProductAdvisor\Model\Repo\Product::P_LIMIT} = array(0, $maxCount + 1);

            $products = \XLite\Core\Database::getRepo('XLite\Model\Product')->search($cnd, false);

            foreach ($products as $product) {
                if ($product->getProductId() != $productId) {
                    $result[] = $product;
                }
            }
        }

        return $result;
    }

    /**
     * Register products source 'Customers also bought...' for 'Add to Cart popup' module
     *
     * @return array
     */
    protected function getSources()
    {
        $sources = parent::getSources();
        $sources['PAB'] = array(
            'method' => 'getSourceCustomerBought',
            'position' => 200,
        );

        return $sources;
    }
}
