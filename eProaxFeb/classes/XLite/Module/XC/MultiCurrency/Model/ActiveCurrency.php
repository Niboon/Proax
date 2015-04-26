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

namespace XLite\Module\XC\MultiCurrency\Model;

use \XLite\Module\XC\MultiCurrency\Core\MultiCurrency;

/**
 * Active currency
 *
 * @Entity (repositoryClass="\XLite\Module\XC\MultiCurrency\Model\Repo\ActiveCurrency")
 * @Table  (
 *      name="active_currencies",
 *      indexes={
 *          @Index (name="position", columns={"position"}),
 *          @Index (name="enabled", columns={"enabled"})
 *      }
 * )
 */
class ActiveCurrency extends \XLite\Model\Base\I18n
{
    /**
     * Active currency ID
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column (type="integer")
     */
    protected $active_currency_id;

    /**
     * Currency rate
     *
     * @var float
     *
     * @Column (type="decimal", precision=14, scale=4)
     */
    protected $rate = 1;

    /**
     * Enabled
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $enabled = true;

    /**
     * Position
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $position = 0;

    /**
     * Last rate update date
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $rateDate = 0;

    /**
     * Currency
     *
     * @var \XLite\Model\Currency
     *
     * @OneToOne (targetEntity="XLite\Model\Currency", inversedBy="active_currency")
     * @JoinColumn (name="currency_id", referencedColumnName="currency_id")
     */
    protected $currency;

    /**
     * Countries
     *
     * @var \XLite\Model\Country[]
     *
     * @OneToMany (targetEntity="XLite\Model\Country", mappedBy="active_currency")
     */
    protected $countries;

    /**
     * Default delimiter format
     *
     * @var array
     */
    protected $defaultDelimiterFormat = array('', '.');

    /**
     * Allowed thousands format
     *
     * @var array
     */
    protected $allowedThousandsDelimiter = array(' ','.',',','');

    /**
     * Allowed decimal delimiter
     *
     * @var array
     */
    protected $allowedDecimalDelimiter = array('.',',');

    /**
     * Update entity
     *
     * @return boolean
     */
    public function update()
    {
        $this->getCurrency()->update();

        return parent::update();
    }

    /**
     * Get rate
     *
     * @return integer
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Get currency name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getCurrency()->getName();
    }

    /**
     * Get currency code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->getCurrency()->getCode();
    }

    /**
     * Get currency prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->getCurrency()->getPrefix();
    }

    /**
     * Get currency suffix
     *
     * @return string
     */
    public function getSuffix()
    {
        return $this->getCurrency()->getSuffix();
    }

    /**
     * Get currency format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->getCurrency()->getThousandDelimiter()
        . \XLite\View\FormField\Select\FloatFormat::FORMAT_DELIMITER
        . $this->getCurrency()->getDecimalDelimiter();
    }

    /**
     * Get rate update date as string
     *
     * @return string
     */
    public function getRateDateAsString()
    {
        return (0 == $this->getRateDate())
            ? '-'
            : \XLite\Core\Converter::getInstance()->formatDate($this->getRateDate())
            . ' ' . \XLite\Core\Converter::getInstance()->formatDayTime($this->getRateDate());
    }

    /**
     * Get countries list
     *
     * @return string
     */
    public function getCountriesList()
    {
        $return = array();

        $countries = $this->getCountries();

        if (count($countries) > 0) {
            foreach ($countries as $country) {
                $return[] = $country->getCode();
            }
        }

        return empty($return) ? '...' : implode(', ', $return);
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return ActiveCurrency
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (boolean) $enabled;
        return $this;
    }

    /**
     * Set rate
     *
     * @param float $value Value
     *
     * @return void
     */
    public function setRate($value)
    {
        if ($this->isDefaultCurrency()) {
            $this->setDate(0);
        } else {
            $this->rateDate = \XLite\Core\Converter::getInstance()->time();
        }

        $this->rate = $value;
    }

    /**
     * Set currency prefix
     *
     * @param string $value Value
     *
     * @return void
     */
    public function setPrefix($value)
    {
        $this->getCurrency()->setPrefix($value);
    }

    /**
     * Set currency suffix
     *
     * @param string $value Value
     *
     * @return void
     */
    public function setSuffix($value)
    {
        $this->getCurrency()->setSuffix($value);
    }

    /**
     * Set currency format
     *
     * @param string $value Value
     *
     * @return void
     */
    public function setFormat($value)
    {
        $format = $this->getDelimitersFormat($value);

        $this->getCurrency()->setThousandDelimiter($format[0]);
        $this->getCurrency()->setDecimalDelimiter($format[1]);
    }

    /**
     * Get ID
     *
     * @return integer
     */
    public function getId()
    {
        return $this->getActiveCurrencyId();
    }

    /**
     * Is default currency
     *
     * @return boolean
     */
    public function isDefaultCurrency()
    {
        return $this->getCurrency()->getCurrencyId() == \XLite\Core\Config::getInstance()->General->shop_currency;
    }

    /**
     * Get default value
     *
     * @return boolean
     */
    public function getDefaultValue()
    {
        return $this->isDefaultCurrency();
    }

    /**
     * Get delimiters format from string
     *
     * @param string $format Format
     *
     * @return array
     */
    protected function getDelimitersFormat($format)
    {
        $return = explode(\XLite\View\FormField\Select\FloatFormat::FORMAT_DELIMITER, $format);

        if (!is_array($return)) {
            $return = $this->defaultDelimiterFormat;
        } else {
            if (!in_array($return[0], $this->allowedThousandsDelimiter)) {
                $return[0] = $this->defaultDelimiterFormat[0];
            }

            if (!in_array($return[1], $this->allowedDecimalDelimiter)) {
                $return[1] = $this->defaultDelimiterFormat[1];
            }
        }

        return $return;
    }

    /**
     * Return first assigned country
     *
     * @return \XLite\Model\Country
     */
    public function getFirstCountry()
    {
        $countries = $this->getCountries();

        return isset($countries[0]) ? $countries[0] : null;
    }

    /**
     * Check if currency has assigned countries
     *
     * @return boolean
     */
    public function hasAssignedCountries()
    {
        return \XLite\Core\Database::getRepo('XLite\Module\XC\MultiCurrency\Model\ActiveCurrency')
            ->hasAssignedCountries($this->getCode());
    }
}