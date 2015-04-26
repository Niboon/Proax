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

namespace XLite\Module\CDev\Sale\View;

/**
 * ItemsList
 */
abstract class ItemsList extends \XLite\View\ItemsList\Product\Customer\ACustomer implements \XLite\Base\IDecorator
{
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
        return $this->getOnlyEntities(parent::getData($cnd, $countOnly));
    }

    /**
     * getPageData
     *
     * @return array
     */
    protected function getPageData()
    {
        return $this->getOnlyEntities(parent::getPageData());
    }

    /**
     * Return collection result from the mixed one.
     *
     * @param mixed $data Data
     *
     * @return mixed
     */
    protected function getOnlyEntities($data)
    {
        $result = $data;
        if (is_array($data)) {
            // Sanitize result array as it is contains the following values: array(0 => Product object, 'cnt' => <counter>)
            // We should return array of product objects
            $result = array();
            foreach ($data as $row) {
                $result[] = is_array($row) ? $row[0] : $row;
            }
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
        return parent::getLabels($product) + \XLite\Module\CDev\Sale\Core\Labels::getLabel($product);
    }
}
