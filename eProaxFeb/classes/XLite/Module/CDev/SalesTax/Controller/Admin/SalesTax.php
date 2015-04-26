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
 * Taxes controller
 */
class SalesTax extends \XLite\Controller\Admin\AAdmin
{

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('Sales tax');
    }

    // {{{ Widget-specific getters

    /**
     * Get tax
     *
     * @return object
     */
    public function getTax()
    {
        return \XLite\Core\Database::getRepo('XLite\Module\CDev\SalesTax\Model\Tax')->getTax();
    }

    // }}}

    // {{{ Actions

    /**
     * Update tax rate
     *
     * @return void
     */
    protected function doActionUpdate()
    {
        $tax = $this->getTax();

        $name = trim(\XLite\Core\Request::getInstance()->name);
        if (0 < strlen($name)) {
            $tax->setName($name);

        } else {
            \XLite\Core\TopMessage::addError('The name of the tax has not been preserved, because that is not filled');
        }

        $optionNames = array(
            'ignore_memberships',
            'addressType',
            'taxableBase',
        );

        foreach ($optionNames as $optionName) {
            $optionValue = !empty(\XLite\Core\Request::getInstance()->$optionName)
                ? \XLite\Core\Request::getInstance()->$optionName
                : 'N';

            $optionData = array(
                'name'     => $optionName,
                'category' => 'CDev\\SalesTax',
                'value'    => $optionValue,
            );
            \XLite\Core\Database::getRepo('XLite\Model\Config')->createOption($optionData);
        }

        $list = new \XLite\Module\CDev\SalesTax\View\ItemsList\Model\Rate;
        $list->processQuick();

        $list2 = new \XLite\Module\CDev\SalesTax\View\ItemsList\Model\ShippingRate;
        $list2->processQuick();

        $rates = \XLite\Core\Database::getRepo('XLite\Module\CDev\SalesTax\Model\Tax\Rate')
            ->findBy(array('tax' => null));

        foreach ($rates as $rate) {
            $tax->addRates($rate);
            $rate->setTax($tax);
        }

        \XLite\Core\TopMessage::addInfo('Tax rates have been updated successfully');
        \XLite\Core\Database::getEM()->flush();
    }

    /**
     * Remove tax rate
     *
     * @return void
     */
    protected function doActionRemoveRate()
    {
        $rate = null;
        $rateId = \XLite\Core\Request::getInstance()->id;

        foreach ($this->getTax()->getRates() as $r) {
            if ($r->getId() == $rateId) {
                $rate = $r;
                break;
            }
        }

        if ($rate) {
            $this->getTax()->getRates()->removeElement($rate);
            \XLite\Core\Database::getEM()->remove($rate);
            \XLite\Core\TopMessage::addInfo('Tax rate has been deleted successfully');
            $this->setPureAction(true);

        } else {
            $this->valid = false;
            \XLite\Core\TopMessage::addError('Tax rate has not been deleted successfully');
        }

        \XLite\Core\Database::getEM()->flush();
    }

    /**
     * Switch tax state
     *
     * @return void
     */
    protected function doActionSwitch()
    {
        $tax = $this->getTax();
        $tax->setEnabled(!$tax->getEnabled());
        \XLite\Core\Database::getEM()->flush();
        $this->setPureAction(true);

        if ($tax->getEnabled()) {
            \XLite\Core\TopMessage::addInfo('Tax has been enabled successfully');

        } else {
            \XLite\Core\TopMessage::addInfo('Tax has been disabled successfully');
        }
    }

    /**
     * Define the actions with no secure token
     *
     * @return array
     */
    public static function defineFreeFormIdActions()
    {
        return array_merge(parent::defineFreeFormIdActions(), array('switch'));
    }

    // }}}
}
