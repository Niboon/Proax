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

namespace XLite\Module\CDev\Sale\View;

/**
 * Viewer
 */
abstract class Price extends \XLite\View\Price implements \XLite\Base\IDecorator
{
    const SALE_PRICE_LABEL = 'sale_price_label';

    protected $salePriceLabel = null;

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'modules/CDev/Sale/css/lc.css';

        return $list;
    }

    /**
     * Calculate "Sale percent off" value.
     *
     * @return integer
     */
    protected function getSalePercent()
    {
        $oldPrice = $this->getOldPrice();

        return 0 < $oldPrice
            ? round((1 - $this->getListPrice() / $oldPrice ) * 100)
            : 0;
    }

    /**
     * Return sale percent value
     *
     * @return float
     */
    protected function getSalePriceDifference()
    {
        return $this->getOldPrice() - $this->getCart()->getCurrency()->roundValue($this->getListPrice());
    }

    /**
     * Return old price value
     *
     * @return float
     */
    protected function getOldPrice()
    {
        return $this->getProduct()->getDisplayPriceBeforeSale();
    }

    /**
     * Return sale participation flag
     *
     * @return boolean
     */
    protected function participateSale()
    {
        return $this->getProduct()->getParticipateSale()
            && $this->getListPrice() < $this->getOldPrice();
    }

    /**
     * Return the "x% label" element
     *
     * @return array
     */
    protected function getLabels()
    {
        return parent::getLabels() + $this->getSalePriceLabel();
    }

    /**
     * Return the specific sale price label info
     *
     * @return array
     */
    protected function getSalePriceLabel()
    {
        if (!isset($this->salePriceLabel)) {
            if ($this->participateSale()) {
                $label = static::t('percent X off', array('percent' => $this->getSalePercent()));
                $this->salePriceLabel = array(
                    'green sale-price' => $label,
                );

                \XLite\Module\CDev\Sale\Core\Labels::addLabel($this->getProduct(), $this->salePriceLabel);
            }
        }

        return $this->salePriceLabel;
    }

    /**
     * Return the specific label info
     *
     * @param string $labelName
     *
     * @return array
     */
    protected function getLabel($labelName)
    {
        if (static::SALE_PRICE_LABEL === $labelName) {
            $result = $this->getSalePriceLabel();

        }  else {
            $result = parent::getLabel($labelName);
        }

        return $result;
    }
}
