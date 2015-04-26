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

namespace XLite\Module\CDev\MarketPrice\View;

/**
 * Details
 */
abstract class Price extends \XLite\View\Price implements \XLite\Base\IDecorator
{
    const MARKET_PRICE_LABEL = 'market_price_label';

    protected $marketPriceLabel = null;

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'modules/CDev/MarketPrice/style.css';

        return $list;
    }

    /**
     * Determine if we need to display product market price
     *
     * @return boolean
     */
    protected function isShowMarketPrice()
    {
        return 0 < $this->getListPrice()
            && $this->getProduct()->getMarketPrice() > $this->getListPrice();
    }

    /**
     * Get the "You save" value
     *
     * @return float
     */
    public function getSaveDifference()
    {
        return $this->getProduct()->getMarketPrice() - $this->getListPrice();
    }

    /**
     * Return the "x% label" element
     *
     * @return array
     */
    protected function getLabels()
    {
        return parent::getLabels() + $this->getMarketPriceLabel();
    }

    /**
     * Return the specific market price label info
     *
     * @return array
     */
    protected function getMarketPriceLabel()
    {
        if (is_null($this->marketPriceLabel)) {
            $percent = min(99, round(($this->getSaveDifference() / $this->getProduct()->getMarketPrice()) * 100));

            if (0 < $percent) {
                $this->marketPriceLabel['green market-price'] = $percent . '% ' . static::t('less');
            }

            \XLite\Module\CDev\MarketPrice\Core\Labels::addLabel($this->getProduct(), $this->marketPriceLabel);
        }

        return $this->marketPriceLabel;
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
        return static::MARKET_PRICE_LABEL === $labelName
            ? $this->getMarketPriceLabel()
            : parent::getLabel($labelName);
    }
}
