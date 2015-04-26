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

namespace XLite\Controller\Admin;

/**
 * Model product selector controller
 */
class ModelProductSelector extends \XLite\Controller\Admin\ModelSelector\AModelSelector
{
    /**
     * Products limit in the query
     */
    const MAX_PRODUCT_COUNT = 10;

    /**
     * Define specific data structure which will be sent in the triggering event (model.selected)
     *
     * @param mixed $item Model item
     *
     * @return string
     */
    protected function defineDataItem($item)
    {
        $data = parent::defineDataItem($item);
        $data['selected_value'] = $item->getName();
        $data['selected_sku']   = $item->getSKU();

        return $data;
    }

    /**
     * Get data of the model request
     *
     * @return \Doctrine\ORM\PersistentCollection | array
     */
    protected function getData()
    {
        $cnd = new \XLite\Core\CommonCell();
        $cnd->{\XLite\Model\Repo\Product::P_SUBSTRING}  = $this->getKey();
        $cnd->{\XLite\Model\Repo\Product::P_BY_TITLE}   = 'Y';
        $cnd->{\XLite\Model\Repo\Product::P_BY_SKU}     = 'Y';
        $cnd->{\XLite\Model\Repo\Product::P_LIMIT}      = array(0, static::MAX_PRODUCT_COUNT);
        $cnd->{\XLite\Model\Repo\Product::P_ORDER_BY}   = array('translations.name', 'asc');

        return \XLite\Core\Database::getRepo('XLite\Model\Product')->search($cnd);
    }

    /**
     * Format model text presentation
     *
     * @param mixed $item Model item
     *
     * @return string
     */
    protected function formatItem($item)
    {
        return $item->getSku() . ' - ' . $item->getName();
    }

    /**
     * Defines the model value
     *
     * @param mixed $item Model item
     *
     * @return string
     */
    protected function getItemValue($item)
    {
        return $item->getId();
    }
}
