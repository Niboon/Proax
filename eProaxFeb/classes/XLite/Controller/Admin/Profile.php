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
class Profile extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Controller parameters (to generate correct URL in getURL() method)
     *
     * @var array
     */
    protected $params = array('target', 'profile_id');

    /**
     * Return value for the "register" mode param
     *
     * @return string
     */
    public static function getRegisterMode()
    {
        return 'register';
    }

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return ($this->isRegisterMode())
            ? static::t('Create profile')
            : static::t('Edit profile');
    }

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
        return parent::isVisible() && $this->getModelForm()->getModelObject();
    }

    /**
     * The "mode" parameter used to determine if we create new or modify existing profile
     *
     * @return boolean
     */
    public function isRegisterMode()
    {
        return self::getRegisterMode() === \XLite\Core\Request::getInstance()->mode;
    }

    /**
     * Alias
     *
     * @return \XLite\Model\Profile
     */
    protected function getProfile()
    {
        return $this->getModelForm()->getModelObject() ?: new \XLite\Model\Profile();
    }


    /**
     * Return true if profile is not related with any order (i.e. it's an original profile)
     *
     * @return boolean
     */
    protected function isOrigProfile()
    {
        return !($this->getProfile()->getOrder());
    }

    /**
     * Class name for the \XLite\View\Model\ form
     *
     * @return string
     */
    protected function getModelFormClass()
    {
        return '\XLite\View\Model\Profile\AdminMain';
    }

    /**
     * Modify profile action
     *
     * @return void
     */
    protected function doActionModify()
    {
        $this->getModelForm()->performAction('modify');
    }

    /**
     * actionPostprocessModify
     *
     * @return void
     */
    protected function actionPostprocessModify()
    {
        $params = array();

        if ($this->getModelForm()->isRegisterMode()) {

            // New profile is registered
            if ($this->isActionError()) {

                // Return back to register page
                $params = array('mode' => self::getRegisterMode());

            } else {

                // Send notification
                \XLite\Core\Mailer::sendProfileCreated(
                    $this->getProfile(),
                    \XLite\Core\Request::getInstance()->password
                );

                // Return to the created profile page
                $params = array('profile_id' => $this->getProfile()->getProfileId());
            }

        } else {
            // Existing profile is updated

            $password = null;

            // Check if user's password was changed
            if (!empty(\XLite\Core\Request::getInstance()->password)) {
                $password = \XLite\Core\Request::getInstance()->password;
            }

            \XLite\Core\Mailer::sendProfileUpdated($this->getProfile(), $password);

            // Get profile ID from modified profile model
            $profileId = $this->getProfile()->getProfileId();

            // Return to the profile page
            $params = array('profile_id' => $profileId);

            if (\XLite\Model\Profile::STATUS_DISABLED == $this->getProfile()->getStatus()) {
                // Clear user session if user profile has been disabled
                \XLite\Core\Session::getInstance()->clearUserSession($profileId);
            }
        }

        if (!empty($params)) {
            $this->setReturnURL($this->buildURL('profile', '', $params));
        }
    }

    /**
     * Delete profile action
     *
     * @return void
     */
    protected function doActionDelete()
    {
        $this->getModelForm()->performAction('delete');

        // Send notification to the user
        \XLite\Core\Mailer::sendProfileDeleted($this->getProfile()->getLogin());

        $this->setReturnURL($this->buildURL('profile_list'));
    }

    /**
     * Register anonymous profile
     * 
     * @return void
     */
    protected function doActionRegisterAsNew()
    {
        $result = false;
        $profile = $this->getModelForm()->getModelObject();

        if (
            $profile
            && $profile->isPersistent()
            && $profile->getAnonymous()
            && !$profile->getOrder()
            && !\XLite\Core\Database::getRepo('XLite\Model\Profile')->findUserWithSameLogin($profile)
        ) {
            $profile->setAnonymous(false);
            $password = \XLite\Core\Database::getRepo('XLite\Model\Profile')->generatePassword();
            $profile->setPassword(\XLite\Core\Auth::encryptPassword($password));

            $result = $profile->update();
        }

        if ($result) {

            // Send notification to the user
            \XLite\Core\Mailer::sendRegisterAnonymousCustomer($profile, $password);

            \XLite\Core\TopMessage::addInfo('The profile has been registered. The password has been sent to the user\'s email address');
        }
    }

    /**
     * Merge anonymous profile with registered 
     * 
     * @return void
     */
    protected function doActionMergeWithRegistered()
    {
        $result = false;
        $profile = $this->getModelForm()->getModelObject();

        if (
            $profile
            && $profile->isPersistent()
            && $profile->getAnonymous()
            && !$profile->getOrder()
        ) {
            $same = \XLite\Core\Database::getRepo('XLite\Model\Profile')->findUserWithSameLogin($profile);
            if ($same && !$same->isAdmin()) {
                $same->mergeWithProfile($profile);
                $result = $same->update();
                if ($result) {
                    $profile->delete();
                }
            }
        }

        if ($result) {
            \XLite\Core\TopMessage::addInfo('The profiles have been merged');
            $this->setReturnURL(static::buildURL('profile', '', array('profile_id' => $same->getProfileId())));
        }
    }
}
