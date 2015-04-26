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

namespace XLite\Model\Base;

/**
 * Surcharge owner
 *
 * @MappedSuperclass
 */
abstract class SurchargeOwner extends \XLite\Model\AEntity
{
    /**
     * Total
     *
     * @var float
     *
     * @Column (type="decimal", precision=14, scale=4)
     */
    protected $total = 0.0000;

    /**
     * Subtotal
     *
     * @var float
     *
     * @Column (type="decimal", precision=14, scale=4)
     */
    protected $subtotal = 0.0000;

    /**
     * Set total 
     * 
     * @param float $total Total
     *  
     * @return \XLite\Model\Bae\SurchargeOwner
     */
    public function setTotal($total)
    {
        $this->total = round($total, 4);

        return $this;
    }

    /**
     * Set subtotal
     *
     * @param float $subtotal Subtotal
     *
     * @return \XLite\Model\Bae\SurchargeOwner
     */
    public function setSubtotal($subtotal)
    {
        $this->subtotal = round($subtotal, 4);

        return $this;
    }

    // {{{ Saved surcharges

    /**
     * Get exclude surcharges (non-included)
     * 
     * @return array
     */
    public function getExcludeSurcharges()
    {
        $list = array();

        foreach ($this->getSurcharges() as $surcharge) {
            if (!$surcharge->getInclude()) {
                $list[] = $surcharge;
            }
        }

        return $list;
    }

    /**
     * Get included surcharges
     *
     * @return array
     */
    public function getIncludeSurcharges()
    {
        $list = array();

        foreach ($this->getSurcharges() as $surcharge) {
            if ($surcharge->getInclude()) {
                $list[] = $surcharge;
            }
        }

        return $list;
    }

    /**
     * Get exclude surcharges (non-included) by type
     * 
     * @param string $type Type
     *  
     * @return array
     */
    public function getExcludeSurchargesByType($type)
    {
        $list = array();

        foreach ($this->getSurcharges() as $surcharge) {
            if (!$surcharge->getInclude() && $surcharge->getType() == $type) {
                $list[] = $surcharge;
            }
        }

        return $list;
    }

    /**
     * Get surcharge totals 
     * 
     * @return array
     */
    public function getSurchargeTotals()
    {
        $surcharges = array();

        foreach ($this->getExcludeSurcharges() as $surcharge) {

            $code = $surcharge->getCode();

            if (!isset($surcharges[$code])) {

                $modifierClass = $surcharge->getClass();

                $surcharges[$code] = array(
                    'name'      => $surcharge->getTypeName(),
                    'cost'      => 0,
                    'available' => $surcharge->getAvailable(),
                    'count'     => 0,
                    'lastName'  => null,
                    'code'      => $code,
                    'widget'    => \Includes\Utils\Operator::checkIfClassExists($modifierClass)
                        ? $modifierClass::getWidgetClass()
                        : \XLite\Logic\Order\Modifier\AModifier::getWidgetClass(),
                    'object'    => $surcharge,
                );
            }

            $surcharges[$code]['cost'] += $surcharge->getValue();
            $surcharges[$code]['count']++;
            $surcharges[$code]['lastName'] = $surcharge->getName();
        }

        return $surcharges;
    }

    /**
     * Get surcharge sum
     *
     * @return float
     */
    public function getSurchargeSum()
    {
        $total = 0;

        foreach ($this->getExcludeSurcharges() as $s) {
            $total += $s->getValue();
        }

        return $total;
    }

    /**
     * Get surcharge sum by type
     *
     * @param string $type Surcharge type
     *
     * @return float
     */
    public function getSurchargeSumByType($type)
    {
        $total = 0;

        foreach ($this->getExcludeSurchargesByType($type) as $s) {
            $total += $s->getValue();
        }

        return $total;
    }

    /**
     * Get surcharge total by type
     *
     * @param string $type Surcharge type
     *
     * @return float
     */
    public function getSurchargeTotalByType($type)
    {
        return $this->getSubtotal() + $this->getSurchargeSumByType($type);
    }

    // }}}

    // {{{ Surcharge compare

    /**
     * Reset surcharges list
     *
     * @return array
     */
    public function resetSurcharges()
    {
        $result = $this->getSurcharges()->toArray();
        foreach ($result as $surcharge) {
            $surcharge->setOwner(null);
        }

        $this->getSurcharges()->clear();

        return $result;
    }

    /**
     * Compare and merge surcharges 
     * 
     * @param array $oldSurcharges Old surcharges
     *  
     * @return void
     */
    public function compareSurcharges(array $oldSurcharges)
    {
        foreach ($oldSurcharges as $i => $oldSurcharge) {
            foreach ($oldSurcharges as $i2 => $oldSurcharge2) {
                if ($i != $i2 && $oldSurcharge->isEqualSurcharge($oldSurcharge2)) {
                    unset($oldSurcharges[$i2]);
                    \XLite\Core\Database::getEM()->remove($oldSurcharge2);

                }
            }
        }

        foreach ($oldSurcharges as $i => $oldSurcharge) {
            foreach ($this->getSurcharges() as $surcharge) {
                if ($surcharge->isEqualSurcharge($oldSurcharge)) {
                    $oldSurcharge->replaceSurcharge($surcharge);
                    unset($oldSurcharges[$i]);
                    break;
                }
            }
        }

        foreach ($oldSurcharges as $surcharge) {
            \XLite\Core\Database::getEM()->remove($surcharge);
        }

        return $oldSurcharges;
    }

    // }}}
}
