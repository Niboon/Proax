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
 * Profile management controller
 */
class AddressBook extends \XLite\Controller\Admin\AAdmin
{
    /**
     * address
     *
     * @var \XLite\Model\Address
     */
    protected $address = null;

    /**
     * Check ACL permissions
     *
     * @return boolean
     */
    public function checkACL()
    {
        $profile = $this->getProfile();

        return parent::checkACL()
            || \XLite\Core\Auth::getInstance()->isPermissionAllowed('manage users')
            || ($profile && $profile->getProfileId() == \XLite\Core\Auth::getInstance()->getProfile()->getProfileId());
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
            : static::t('Edit profile');
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
     * Get addresses array for working profile
     *
     * @return array
     */
    public function getAddresses()
    {
        return \XLite\Core\Database::getRepo('\XLite\Model\Address')
            ->findBy(
                array(
                    'profile' => $this->getProfile()->getProfileId(),
                )
            );

    }

    /**
     * Get return URL
     *
     * @return string
     */
    public function getReturnURL()
    {
        if (\XLite\Core\Request::getInstance()->action) {
            $profileId = \XLite\Core\Request::getInstance()->profile_id;

            if (!isset($profileId)) {
                $profileId = $this->getAddress()->getProfile()->getProfileId();

                if (\XLite\Core\Auth::getInstance()->getProfile()->getProfileId() === $profileId) {
                    unset($profileId);
                }
            }

            $params = isset($profileId) ? array('profile_id' => $profileId) : array();

            $url = $this->buildURL('address_book', '', $params);

        } else {
            $url = parent::getReturnURL();
        }

        return $url;
    }

    /**
     * Check if current page is accessible
     *
     * @return boolean
     */
    public function checkAccess()
    {
        return parent::checkAccess() && $this->isOrigProfile();
    }

    /**
     * Check controller visibility
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible() && $this->getProfile();
    }

    /**
     * Return true if profile is not related with any order (i.e. it's an original profile)
     *
     * @return boolean
     */
    protected function isOrigProfile()
    {
        return !($this->getProfile() && $this->getProfile()->getOrder());
    }

    /**
     * Alias
     *
     * @return \XLite\Model\Profile
     */
    protected function getProfile()
    {
        return $this->getModelForm()->getModelObject()->getProfile();
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
     * doActionSave
     *
     * @return void
     */
    protected function doActionSave()
    {
        if ($this->getModelForm()->performAction('update')) {
            $this->setHardRedirect();
        }
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

            \XLite\Core\TopMessage::addInfo(
                static::t('Address has been deleted')
            );
        }
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
