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

namespace XLite\Model;

/**
 * Currency
 *
 * @Entity
 * @Table (name="currencies",
 *      indexes = {
 *          @Index (name="code", columns={"code"})
 *      }
 * )
 */
class Currency extends \XLite\Model\Base\I18n
{
    /**
     * Currency unique id (ISO 4217 number)
     *
     * @var integer
     *
     * @Id
     * @Column (type="integer", options={ "unsigned": true })
     */
    protected $currency_id;

    /**
     * Currency code (ISO 4217 alpha-3)
     *
     * @var string
     *
     * @Column (type="string", options={ "fixed": true }, length=3, unique=true)
     */
    protected $code;

    /**
     * Symbol
     *
     * @var string
     *
     * @Column (type="string", length=16)
     */
    protected $symbol;

    /**
     * Prefix
     *
     * @var string
     *
     * @Column (type="string", length=32)
     */
    protected $prefix = '';

    /**
     * Suffix
     *
     * @var string
     *
     * @Column (type="string", length=32)
     */
    protected $suffix = '';

    /**
     * Number of digits after the decimal separator.
     *
     * @var integer
     *
     * @Column (type="smallint")
     */
    protected $e = 0;

    /**
     * Decimal part delimiter
     * @var string
     *
     * @Column (type="string", length=8)
     */
    protected $decimalDelimiter = '.';

    /**
     * Thousand delimier
     *
     * @var string
     *
     * @Column (type="string", length=8)
     */
    protected $thousandDelimiter = '';

    /**
     * Orders
     *
     * @var \Doctrine\Common\Collections\Collection
     *
     * @OneToMany (targetEntity="XLite\Model\Order", mappedBy="currency")
     */
    protected $orders;

    /**
     * Countries
     *
     * @var \Doctrine\Common\Collections\Collection
     *
     * @OneToMany (targetEntity="XLite\Model\Country", mappedBy="currency", cascade={"all"})
     */
    protected $countries;


    /**
     * Set currency Id
     *
     * @param integer $value Currency id
     * TODO - Doctrine is not generate setter for identifier. We must reworkt it
     *
     * @return void
     */
    public function setCurrencyId($value)
    {
        $this->currency_id = $value;
    }

    /**
     * Round value
     *
     * @param float $value Value
     *
     * @return float
     */
    public function roundValue($value)
    {
        return \XLite\Logic\Math::getInstance()->roundByCurrency($value, $this);
    }

    /**
     * Round value as integer
     *
     * @param float $value Value
     *
     * @return integer
     */
    public function roundValueAsInteger($value)
    {
        return intval(round($this->roundValue($value) * pow(10, $this->getE()), 0));
    }

    /**
     * Convert integer to float
     *
     * @param integer $value Value
     *
     * @return float
     */
    public function convertIntegerToFloat($value)
    {
        return $value / pow(10, $this->getE());
    }

    /**
     * Format value
     *
     * @param float $value Value
     *
     * @return string
     */
    public function formatValue($value)
    {
        return \XLite\Logic\Math::getInstance()->formatValue($value, $this);
    }

    /**
     * Get minimum value 
     *
     * @return float
     */
    public function getMinimumValue()
    {
        return $this->convertIntegerToFloat(1);
    }

    /**
     * Constructor
     *
     * @param array $data Entity properties OPTIONAL
     *
     * @return void
     */
    public function __construct(array $data = array())
    {
        $this->orders    = new \Doctrine\Common\Collections\ArrayCollection();
        $this->countries = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct($data);
    }

    /**
     * Format value as parts list
     * 
     * @param float $value Value
     *  
     * @return array
     */
    public function formatParts($value)
    {
        return \XLite\Logic\Math::getInstance()->formatParts($value, $this);
    }

}
