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

namespace XLite\Model\Payment\Processor;

/**
 * Purchase order
 */
class PurchaseOrder extends \XLite\Model\Payment\Processor\Offline
{
    /**
     * Get input template
     *
     * @return string|void
     */
    public function getInputTemplate()
    {
        return 'checkout/purchase_order.tpl';
    }

    /**
     * Get input errors
     *
     * @param array $data Input data
     *
     * @return array
     */
    public function getInputErrors(array $data)
    {
        $errors = parent::getInputErrors($data);

        foreach ($this->getInputDataLabels() as $k => $t) {
            if (!isset($data[$k]) || !$data[$k]) {
                $errors[] = \XLite\Core\Translation::lbl('X field is required', array('field' => $t));
            }
        }

        return $errors;
    }


    /**
     * Get input data labels list
     *
     * @return array
     */
    protected function getInputDataLabels()
    {
        return array(
            'number'    => 'Purchase order number',
            'company'   => 'Company name',
            'purchaser' => 'Name of purchaser',
            'position'  => 'Position',
        );
    }

    /**
     * Get input data access levels list
     *
     * @return array
     */
    protected function getInputDataAccessLevels()
    {
        return array(
            'number'    => \XLite\Model\Payment\TransactionData::ACCESS_CUSTOMER,
            'company'   => \XLite\Model\Payment\TransactionData::ACCESS_ADMIN,
            'purchaser' => \XLite\Model\Payment\TransactionData::ACCESS_ADMIN,
            'position'  => \XLite\Model\Payment\TransactionData::ACCESS_ADMIN,
        );
    }
}
