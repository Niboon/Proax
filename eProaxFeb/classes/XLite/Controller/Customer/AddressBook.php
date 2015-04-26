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

namespace XLite\Controller\Customer;

/**
 * Addresses management controller
 */
class AddressBook extends \XLite\Controller\Customer\ACustomer
{
    /**
     * address
     *
     * @var \XLite\Model\Address
     */
    protected $address = null;

    /**
     * Set if the form id is needed to make an actions
     * Form class uses this method to check if the form id should be added
     *
     * @return boolean
     */
    public static function needFormId()
    {
        return true;
    }

    /**
     * Check - controller must work in secure zone or not
     *
     * @return boolean
     */
    public function isSecure()
    {
        return \XLite\Core\Config::getInstance()->Security->customer_security;
    }

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return \XLite\Core\Request::getInstance()->widget
            ? static::t('Address details')
            : static::t('Address book');
    }

    /**
     * Check whether the title is to be displayed in the content area
     *
     * @return boolean
     */
    public function isTitleVisible()
    {
        return \XLite\Core\Request::getInstance()->widget && $this->checkAccess();
    }

    /**
     * getAddress
     *
     * @return \XLite\Model\Address
     */
    public function getAddress()
    {
        return $this->address = $this->getModelForm()->getModelObject();
    }

    /**
     * Check if current page is accessible
     *
     * @return boolean
     */
    protected function checkAccess()
    {
        $auth = \XLite\Core\Auth::getInstance();
        $address = $this->getAddress();

        return parent::checkAccess()
            && $auth->isLogged()
            && (!$address || $address->getProfile()->getProfileId() == $auth->getProfile()->getProfileId());
    }

    /**
     * Alias
     *
     * @return \XLite\Model\Profile
     */
    protected function getProfile()
    {
        return $this->getModelForm()->getModelObject()->getProfile() ?: new \XLite\Model\Profile();
    }

    /**
     * Common method to determine current location
     *
     * @return string
     */
    protected function getLocation()
    {
        return static::t('Address book');
    }

    /**
     * Add part to the location nodes list
     *
     * @return void
     */
    protected function addBaseLocation()
    {
        parent::addBaseLocation();

        $this->addLocationNode(static::t('My account'));
    }

    /**
     * getModelFormClass
     *
     * @return string
     */
    protected function getModelFormClass()
    {
        return '\XLite\View\Model\Address\Address';
    }

    /**
     * Save address
     *
     * @return boolean
     */
    protected function doActionSave()
    {
        $result = $this->getModelForm()->performAction('update');
        if ($result) {
            $this->setHardRedirect();
        }

        return $result;
    }

    /**
     * doActionDelete
     *
     * @return void
     */
    protected function doActionDelete()
    {
        $address = $this->getAddress();

        if (isset($address)) {
            \XLite\Core\Database::getEM()->remove($address);
            \XLite\Core\Database::getEM()->flush();

            \XLite\Core\TopMessage::addInfo('Address has been deleted');
        }

        $this->setReturnURL(\Xlite\Core\Converter::buildURL('address_book'));
    }

    /**
     * doActionCancelDelete
     *
     * @return void
     */
    protected function doActionCancelDelete()
    {
        // Do nothing, action is needed just for redirection back
    }
}
