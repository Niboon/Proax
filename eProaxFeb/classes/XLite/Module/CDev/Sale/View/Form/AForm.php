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

namespace XLite\Module\CDev\Sale\View\Form;

/**
 * "Set the sale price" dialog form class
 */
class AForm extends \XLite\View\Form\AForm implements \XLite\Base\IDecorator
{
    /**
     * Set validators pairs for products data. Sale structure.
     *
     * @param mixed &$data Data
     *
     * @return void
     */
    protected function setSaleDataValidators(&$data)
    {
        if ($this->getPostedData('participateSale')) {
            switch ($this->getPostedData('discountType')) {

                case \XLite\Module\CDev\Sale\Model\Product::SALE_DISCOUNT_TYPE_PRICE:
                    $data->addPair('salePriceValue', new \XLite\Core\Validator\Float(), null, 'Sale price')
                        ->setRange(0);
                    break;

                case \XLite\Module\CDev\Sale\Model\Product::SALE_DISCOUNT_TYPE_PERCENT:
                    $data->addPair('salePriceValue', new \XLite\Core\Validator\Integer(), null, 'Percent off')
                        ->setRange(1, 100);
                    break;

                default:
            }
        }
    }
}
