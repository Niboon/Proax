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
 * Login
 * FIXME: must be completely refactored
 */
class Login extends \XLite\Controller\Admin\AAdmin
{
    /**
     * getAccessLevel
     *
     * @return integer
     */
    public function getAccessLevel()
    {
        return \XLite\Core\Auth::getInstance()->getCustomerAccessLevel();
    }

    /**
     * Initialization
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        if (empty(\XLite\Core\Request::getInstance()->login)) {
            \XLite\Core\Request::getInstance()->login = \XLite\Core\Auth::getInstance()->remindLogin();
        }
    }

    /**
     * Check - is current place public or not
     *
     * @return boolean
     */
    protected function isPublicZone()
    {
        return true;
    }

    /**
     * Preprocessor for no-action run
     *
     * @return void
     */
    protected function doNoAction()
    {
        parent::doNoAction();

        if (\XLite\Core\Auth::getInstance()->isAdmin()) {
            $this->setReturnURL($this->buildURL());
        }
    }

    /**
     * Login
     *
     * @return void
     */
    protected function doActionLogin()
    {
        $profile = \XLite\Core\Auth::getInstance()->loginAdministrator(
            \XLite\Core\Request::getInstance()->login,
            \XLite\Core\Request::getInstance()->password
        );

        if (
            is_int($profile)
            && in_array($profile, array(\XLite\Core\Auth::RESULT_ACCESS_DENIED, \XLite\Core\Auth::RESULT_PASSWORD_NOT_EQUAL, \XLite\Core\Auth::RESULT_LOGIN_IS_LOCKED))
        ) {
            $this->set('valid', false);

            if (in_array($profile, array(\XLite\Core\Auth::RESULT_ACCESS_DENIED, \XLite\Core\Auth::RESULT_PASSWORD_NOT_EQUAL))) {
                \XLite\Core\TopMessage::addError('Invalid login or password');

            } elseif ($profile == \XLite\Core\Auth::RESULT_LOGIN_IS_LOCKED) {
                \XLite\Core\TopMessage::addError('Login is locked out');
            }

            $returnURL = $this->buildURL('login');

        } else {
            if (!\XLite::getXCNLicense()) {
                \XLite\Core\Session::getInstance()->set(\XLite::SHOW_TRIAL_NOTICE, true);
            }

            if (isset(\XLite\Core\Session::getInstance()->lastWorkingURL)) {
                $returnURL = \XLite\Core\Session::getInstance()->lastWorkingURL;
                unset(\XLite\Core\Session::getInstance()->lastWorkingURL);

            } else {
                $returnURL = $this->buildURL();
            }

            \Includes\Utils\Session::setAdminCookie();

            \XLite\Core\Database::getEM()->flush();
        }

        $this->setReturnURL($returnURL);
    }

    /**
     * Define the actions with no secure token
     *
     * @return array
     */
    public static function defineFreeFormIdActions()
    {
        return array_merge(parent::defineFreeFormIdActions(), array('logoff'));
    }

    /**
     * Logoff
     *
     * @return void
     */
    protected function doActionLogoff()
    {
        \XLite\Controller\Admin\Base\AddonsList::cleanRecentlyInstalledModuleList();

        \Includes\Utils\Session::clearAdminCookie();

        \XLite\Core\Auth::getInstance()->logoff();

        \XLite\Model\Cart::getInstance()->logoff();
        \XLite\Model\Cart::getInstance()->updateOrder();

        \XLite\Core\Database::getEM()->flush();

        $this->setReturnURL($this->buildURL('login'));
    }
}
