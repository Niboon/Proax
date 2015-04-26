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

namespace XLite\Module\CDev\Sale\Controller\Admin;

/**
 * Sale selected controller
 */
class SaleSelected extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('Set the sale price');
    }

    /**
     * Set sale price parameters for products list
     *
     * @return void
     */
    protected function doActionSetSalePrice()
    {
        $form = new \XLite\Module\CDev\Sale\View\Form\SaleSelectedDialog();
        $form->getRequestData();

        if ($form->getValidationMessage()) {
            \XLite\Core\TopMessage::addError($form->getValidationMessage());
        } else {
            \XLite\Core\Database::getRepo('\XLite\Model\Product')->updateInBatchById($this->getUpdateInfo());
            \XLite\Core\TopMessage::addInfo(
                'Products information has been successfully updated'
            );
        }

        $this->setReturnURL($this->buildURL('product_list', '', array('mode' => 'search')));
    }

    /**
     * Return result array to update in batch list of products
     *
     * @return array
     */
    protected function getUpdateInfo()
    {
        return array_fill_keys(
            array_keys($this->getSelected()),
            $this->getUpdateInfoElement()
        );
    }

    /**
     * Return one element to update.
     *
     * @return array
     */
    protected function getUpdateInfoElement()
    {
        $data = $this->getPostedData();

        return array(
            'participateSale' => (0 !== $data['salePriceValue'] || \XLite\Model\Product::SALE_DISCOUNT_TYPE_PERCENT !== $data['discountType'])
        ) + $data;
    }
}
