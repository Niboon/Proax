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

namespace XLite\Module\XC\ProductVariants\View\Product;

/**
 * Abstract class
 *
 */
abstract class AProduct extends \XLite\View\AView
{
    /**
     * The number of variants limits
     */
    const VARIANTS_NUMBER_SOFT_LIMIT = 30;
    const VARIANTS_NUMBER_HARD_LIMIT = 300;

    /**
     * Return list of allowed targets
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        return array_merge(parent::getAllowedTargets(), array('product'));
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return $this->getDir() . '/body.tpl';
    }

    /**
     * Return templates directory name
     *
     * @return string
     */
    protected function getDir()
    {
        return 'modules/XC/ProductVariants/variants/parts';
    }

    /**
     * Return block style
     *
     * @return string
     */
    protected function getBlockStyle()
    {
        return 'dialog-block';
    }

    /**
     * Get variants limit warning message (for tooltip)
     *
     * @return string
     */
    protected function getLimitWarningMessage()
    {
        return static::t('Number of variants warning', array('limit' => $this->getVariantsNumberSoftLimit()));
    }

    /**
     * Get variants limit confirmation message (for js confirmation)
     *
     * @return string
     */
    protected function getLimitConfirmationMessage()
    {
        return static::t('Number of variants confirmation', array('limit' => $this->getVariantsNumberSoftLimit()));
    }

    /**
     * Get variants limit error message (for tooltip and JS alert)
     *
     * @return string
     */
    protected function getLimitErrorMessage()
    {
        return static::t("Number of variants error", array('limit' => $this->getVariantsNumberHardLimit()));
    }

    /**
     * Get variants number warning message (for variants page)
     *
     * @return string
     */
    protected function getVariantsNumberWarning()
    {
        return static::t("Number of variants warning message", array('limit' => $this->getVariantsNumberSoftLimit()));
    }

    /**
     * Get variants number soft limit (to display warning if exceed)
     *
     * @return integer
     */
    protected function getVariantsNumberSoftLimit()
    {
        return static::VARIANTS_NUMBER_SOFT_LIMIT;
    }

    /**
     * Get variants number hard limit (to display error)
     *
     * @return integer
     */
    protected function getVariantsNumberHardLimit()
    {
        return static::VARIANTS_NUMBER_HARD_LIMIT;
    }
}
