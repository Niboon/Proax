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

namespace XLite\Module\XC\Upselling\Core;

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
     * @param array   $productIds Product ID which must be excluded from the search result
     * @param integer $maxCount   Maximum number of products
     *
     * @return array
     */
    public function getSourceRelatedProducts($productId, $productIds, $maxCount)
    {
        $result = array();

        $cnd = new \XLite\Core\CommonCell;
        if ($productIds) {
            $cnd->{\XLite\Module\XC\Upselling\Model\Repo\UpsellingProduct::SEARCH_EXCL_PRODUCT_ID} = $productIds;
        }
        $cnd->{\XLite\Module\XC\Upselling\Model\Repo\UpsellingProduct::SEARCH_PARENT_PRODUCT_ID} = $productId;
        $cnd->{\XLite\Module\XC\Upselling\Model\Repo\UpsellingProduct::SEARCH_DATE} = \XLite\Base\SuperClass::getUserTime();
        $cnd->{\XLite\Module\XC\Upselling\Model\Repo\UpsellingProduct::SEARCH_LIMIT} = array(0, $maxCount + 1);

        $products = \XLite\Core\Database::getRepo('XLite\Module\XC\Upselling\Model\UpsellingProduct')
            ->search($cnd, false);

        foreach ($products as $product) {
            $result[] = $product->getProduct();
        }

        return $result;
    }

    /**
     * Register products source 'Related Products' for 'Add to Cart popup' module
     *
     * @return array
     */
    protected function getSources()
    {
        $sources = parent::getSources();
        $sources['REL'] = array(
            'method' => 'getSourceRelatedProducts',
            'position' => 100,
        );

        return $sources;
    }
}
