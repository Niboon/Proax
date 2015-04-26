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

namespace XLite\Controller\Admin;

/**
 * Currency management page controller
 */
class Currency extends \XLite\Controller\Admin\AAdmin
{
    /**
     * init
     *
     * @return void
     */
    public function init()
    {
        if (isset(\XLite\Core\Request::getInstance()->currency_id)) {
            $currency = \XLite\Core\Database::getRepo('XLite\Model\Currency')
                ->find(\XLite\Core\Request::getInstance()->currency_id);

            if ($currency) {
                $shopCurrency = \XLite\Core\Database::getRepo('XLite\Model\Config')
                    ->findOneBy(array('name' => 'shop_currency', 'category' => 'General'));

                \XLite\Core\Database::getRepo('XLite\Model\Config')->update(
                    $shopCurrency,
                    array('value' => $currency->getCurrencyId())
                );

                \XLite\Core\Config::updateInstance();
            }
        }
    }

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('Currency');
    }

    /**
     * Return currencies collection to use
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCurrencies()
    {
        if (!isset($this->currencies)) {
            $this->currencies = \XLite\Core\Database::getRepo('XLite\Model\Currency')->findAll();
        }

        return $this->currencies;
    }

    /**
     * Modify currency action
     *
     * @return void
     */
    protected function doActionModify()
    {
        $this->getModelForm()->performAction('modify');
    }

    /**
     * Class name for the \XLite\View\Model\ form
     *
     * @return string
     */
    protected function getModelFormClass()
    {
        return '\XLite\View\Model\Currency\Currency';
    }
}
