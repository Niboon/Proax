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

namespace XLite\Module\XC\ProductComparison\Controller\Customer;

/**
 * Product comparison
 *
 */
class ProductComparison extends \XLite\Controller\Customer\ACustomer
{
    /**
     * Controller parameters
     *
     * @var array
     */
    protected $params = array('target');

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return \XLite\Module\XC\ProductComparison\Core\Data::getInstance()->getTitle();
    }

    /**
     * Product comparison delete
     *
     * @return void
     */
    protected function doActionDelete()
    {
        $id = \XLite\Core\Request::getInstance()->product_id;
        \XLite\Module\XC\ProductComparison\Core\Data::getInstance()->deleteProductId($id);
        $this->afterAction('delete', $id);
    }

    /**
     * Product comparison add
     *
     * @return void
     */
    protected function doActionAdd()
    {
        $id = \XLite\Core\Request::getInstance()->product_id;
        \XLite\Module\XC\ProductComparison\Core\Data::getInstance()->addProductId($id);
        $this->afterAction('add', $id);
    }

    /**
     * Clear list
     *
     * @return void
     */
    protected function doActionClear()
    {
        \XLite\Module\XC\ProductComparison\Core\Data::getInstance()->clearList();
        $this->afterAction('clear');
    }

    /**
     * After action
     *
     * @param string  $action Action
     * @param integer $id     Id OPTIONAL
     *
     * @return void
     */
    protected function afterAction($action, $id = 0)
    {
        $data = array(
            'productId' => $id,
            'action'    => $action,
            'title'     => $this->getTitle(),
            'count'     => \XLite\Module\XC\ProductComparison\Core\Data::getInstance()->getProductsCount(),
        );
        \XLite\Core\Event::updateProductComparison($data);
        \XLite\Core\Event::getInstance()->display();
        \XLite\Core\Event::getInstance()->clear();
        print json_encode($data);
        exit(0);
    }
}
