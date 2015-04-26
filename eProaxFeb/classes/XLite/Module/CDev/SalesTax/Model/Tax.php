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

namespace XLite\Module\CDev\SalesTax\Model;

/**
 * Tax
 *
 * @Entity
 * @Table  (name="sales_taxes")
 */
class Tax extends \XLite\Model\Base\I18n
{
    /**
     * Tax unique ID
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer", options={ "unsigned": true })
     */
    protected $id;

    /**
     * Enabled
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $enabled = false;

    /**
     * Tax rates (relation)
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @OneToMany (targetEntity="XLite\Module\CDev\SalesTax\Model\Tax\Rate", mappedBy="tax", cascade={"all"})
     * @OrderBy ({"position" = "ASC"})
     */
    protected $rates;

    /**
     * Constructor
     *
     * @param array $data Entity properties OPTIONAL
     *
     * @return void
     */
    public function __construct(array $data = array())
    {
        $this->rates = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct($data);
    }

    /**
     * Get filtered general tax rates by zones and membership
     *
     * @param array                   $zones      Zone id list
     * @param \XLite\Model\Membership $membership Membership OPTIONAL
     * @param \XLite\Model\TaxClass   $taxClass   Tax class OPTIONAL
     *
     * @return array
     */
    public function getFilteredRates(
        array $zones,
        \XLite\Model\Membership $membership = null,
        \XLite\Model\TaxClass $taxClass = null
    ) {
        $rates = $this->getApplicableRates($zones, $membership, $taxClass);

        foreach ($rates as $k => $rate) {
            if (\XLite\Module\CDev\SalesTax\Model\Tax\Rate::TAXBASE_SHIPPING == $rate->getTaxableBase()) {
                unset($rates[$k]);
            }
        }

        return $rates;
    }

    /**
     * Get filtered tax rates on shipping cost by zones and membership
     *
     * @param array                   $zones      Zone id list
     * @param \XLite\Model\Membership $membership Membership OPTIONAL
     * @param \XLite\Model\TaxClass   $taxClass   Tax class OPTIONAL
     *
     * @return array
     */
    public function getFilteredShippingRates(
        array $zones,
        \XLite\Model\Membership $membership = null,
        \XLite\Model\TaxClass $taxClass = null
    ) {
        $rates = $this->getApplicableRates($zones, $membership, $taxClass);

        foreach ($rates as $k => $rate) {
            if (\XLite\Module\CDev\SalesTax\Model\Tax\Rate::TAXBASE_SHIPPING != $rate->getTaxableBase()) {
                unset($rates[$k]);
            }
        }

        return $rates;
    }

    /**
     * Get filtered rate by zones and membership
     *
     * @param array                   $zones      Zone id list
     * @param \XLite\Model\Membership $membership Membership OPTIONAL
     * @param \XLite\Model\TaxClass   $taxClass   Tax class OPTIONAL
     *
     * @return \XLite\Module\CDev\SalesTax\Model\Tax\Rate
     */
    public function getFilteredRate(
        array $zones,
        \XLite\Model\Membership $membership = null,
        \XLite\Model\TaxClass $taxClass = null
    ) {
        $rates = $this->getFilteredRates($zones, $membership, $taxClass);

        return array_shift($rates);
    }

    /**
     * Get applicable tax rates by zones and membership
     *
     * @param array                   $zones      Zone id list
     * @param \XLite\Model\Membership $membership Membership OPTIONAL
     * @param \XLite\Model\TaxClass   $taxClass   Tax class OPTIONAL
     *
     * @return array
     */
    protected function getApplicableRates(
        array $zones,
        \XLite\Model\Membership $membership = null,
        \XLite\Model\TaxClass $taxClass = null
    ) {
        $rates = array();

        foreach ($this->getRates() as $rate) {
            if ($rate->isApplied($zones, $membership, $taxClass)) {
                $rates[] = $rate;
            }
        }
        usort($rates, array($this, 'sortRates'));

        return $rates;
    }

   /**
     * Sort rates
     *
     * @param mixed $a Rate A
     * @param mixed $b Rate B
     *
     * @return boolean
     */
    protected function sortRates($a, $b)
    {
        return $a->getPosition() >= $b->getPosition();
    }
}
