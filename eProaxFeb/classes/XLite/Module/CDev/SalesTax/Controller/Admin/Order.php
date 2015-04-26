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

namespace XLite\Module\CDev\SalesTax\Controller\Admin;

/**
 * Order page controller
 */
class Order extends \XLite\Controller\Admin\Order implements \XLite\Base\IDecorator
{
    /**
     * Assemble shipping dump surcharge
     *
     * @return array
     */
    protected function assembleCdevstaxDumpSurcharge()
    {
        $code = \XLite\Module\CDev\SalesTax\Logic\Order\Modifier\Tax::getSurchargeCode();

        return $code
            ? $this->assembleDefaultDumpSurcharge(
                \XLite\Model\Base\Surcharge::TYPE_TAX,
                $code,
                '\XLite\Module\CDev\SalesTax\Logic\Order\Modifier\Tax',
                static::t('Sales tax')
            )
            : null;
    }

    /**
     * Get required surcharges
     *
     * @return array
     */
    protected function getRequiredSurcharges()
    {
        $result = parent::getRequiredSurcharges();

        if (\XLite\Module\CDev\SalesTax\Logic\Order\Modifier\Tax::getSurchargeCode()) {
            $result = array_merge(
                $result,
                array(\XLite\Module\CDev\SalesTax\Logic\Order\Modifier\Tax::MODIFIER_CODE)
            );
        }

        return $result;
    }

    /**
     * Postprocess surcharge totals
     *
     * @param array $modifiers Modifiers
     *
     * @return array
     */
    protected function postprocessSurchargeTotals(array $modifiers)
    {
        $modifiers = parent::postprocessSurchargeTotals($modifiers);

        // Search for sales taxes surcharges and leave only one (first) surcharge

        $first = null;

        foreach ($modifiers as $code => $modifier) {
            if ($this->isSalesTaxSurcharge($modifier['object'])) {
                if (!$first) {
                    $first = $modifier;

                } else {
                    unset($modifiers[$code]);
                }
            }
        }

        return $modifiers;
    }

    /**
     * Return true if code is SalesTax surcharge code
     *
     * @param \XLite\Model\Order\Surcharge $surcharge Surcharge
     *
     * @return boolean
     */
    protected function isSalesTaxSurcharge($surcharge)
    {
        $salesTaxModifier = $surcharge->getOwner()->getModifier(
            \XLite\Model\Base\Surcharge::TYPE_TAX,
            \XLite\Module\CDev\SalesTax\Logic\Order\Modifier\Tax::MODIFIER_CODE
        );

        return $salesTaxModifier->isSurchargeOwner($surcharge);
    }

    /**
     * Add human readable name for CDEV.STAX modifier code
     *
     * @return array
     */
    protected static function getFieldHumanReadableNames()
    {
        $result = parent::getFieldHumanReadableNames();

        $code = \XLite\Module\CDev\SalesTax\Logic\Order\Modifier\Tax::getSurchargeCode();

        if ($code) {
            $result = array_merge($result, array($code => 'Sales tax'));
        }

        return $result;
    }
}
