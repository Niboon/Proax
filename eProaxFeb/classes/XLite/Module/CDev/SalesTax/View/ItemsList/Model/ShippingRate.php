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

namespace XLite\Module\CDev\SalesTax\View\ItemsList\Model;

/**
 * ShippingRate items list
 */
class ShippingRate extends \XLite\Module\CDev\SalesTax\View\ItemsList\Model\Rate
{
    /**
     * Define columns structure
     *
     * @return array
     */
    protected function defineColumns()
    {
        $columns = parent::defineColumns();

        if (isset($columns['taxableBase'])) {
            unset($columns['taxableBase']);
        }

        return $columns;
    }

    /**
     * Get data prefix
     *
     * @return string
     */
    public function getDataPrefix()
    {
        return 'shipping' . parent::getDataPrefix();
    }

    /**
     * Get data prefix for new cells
     *
     * @return string
     */
    public function getCreateDataPrefix()
    {
        return 'shipping' . parent::getCreateDataPrefix();
    }

    /**
     * Get data prefix for remove cells
     *
     * @return string
     */
    public function getRemoveDataPrefix()
    {
        return 'shipping' . parent::getRemoveDataPrefix();
    }

    /**
     * Return params list to use for search
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getSearchCondition()
    {
        $result = new \XLite\Core\CommonCell;

        $result->{\XLite\Module\CDev\SalesTax\Model\Repo\Tax\Rate::PARAM_TAXABLE_BASE}
            = \XLite\Module\CDev\SalesTax\Model\Tax\Rate::TAXBASE_SHIPPING;

        return $result;
    }

    /**
     * Create entity
     *
     * @return \XLite\Model\AEntity
     */
    protected function createEntity()
    {
        $entity = parent::createEntity();

        $entity->setTaxableBase(\XLite\Module\CDev\SalesTax\Model\Tax\Rate::TAXBASE_SHIPPING);

        return $entity;
    }

    /**
     * getEmptyListFile
     *
     * @return string
     */
    protected function getEmptyListFile()
    {
        return 'empty_shipping.tpl';
    }
}
