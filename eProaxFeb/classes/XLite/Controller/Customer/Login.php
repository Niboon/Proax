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
 * Login page controller
 */
class Login extends \XLite\Controller\Customer\ACustomer
{
    /**
     * Index in request array; the secret token used for authorization
     */
    const SECURE_TOKEN = 'secureToken';

    /**
     * Controller parameters
     *
     * @var array
     */
    protected $params = array('target', 'mode');

    /**
     * Profile
     *
     * @var \XLite\Model\Profile|integer
     */
    protected $profile;

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
     * Get page title
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('Sign in');
    }

    /**
     * handleRequest
     *
     * @return void
     */
    public function handleRequest()
    {
        if (
            \XLite\Core\Auth::getInstance()->isLogged()
            && 'logoff' !== \XLite\Core\Request::getInstance()->{static::PARAM_ACTION}
        ) {
            $this->setReturnURL($this->buildURL());
        }

        parent::handleRequest();
    }

    /**
     * Check - controller must work in secure zone or not
     *
     * @return boolean
     */
    public function isSecure()
    {
        return 'logoff' !== \XLite\Core\Request::getInstance()->action
            ? \XLite\Core\Config::getInstance()->Security->customer_security
            : parent::isSecure();
    }

    /**
     * Perform some actions after the "login" action
     *
     * @return void
     */
    public function redirectFromLogin()
    {
        $url = $this->getRedirectFromLoginURL();

        if (isset($url)) {
            \XLite\Core\CMSConnector::isCMSStarted()
                ? \XLite\Core\Operator::redirect($url, true)
                : $this->setReturnURL($url);
        }
    }

    /**
     * Common method to determine current location
     *
     * @return string
     */
    protected function getLocation()
    {
        return $this->getTitle();
    }

    /**
     * Return URL to redirect from login
     *
     * @return string
     */
    protected function getRedirectFromLoginURL()
    {
        return null;
    }

    /**
     * Log in using the login and password from request
     *
     * @return \XLite\Model\Profile
     */
    protected function performLogin()
    {
        $data = \XLite\Core\Request::getInstance()->getData();
        $token = empty($data[self::SECURE_TOKEN]) ? null : $data[self::SECURE_TOKEN];

        return \XLite\Core\Auth::getInstance()->login($data['login'], $data['password'], $token);
    }

    /**
     * Login
     *
     * @return void
     */
    protected function doActionLogin()
    {
        if ($this->isLoginPermitted()) {
            if ($this->isAlternativeLoginUsed()) {
                $this->alternativeLoginBody();
            } else {
                $this->loginBody();
            }
        }
    }

    /**
     * Check if login permitted
     *
     * @return boolean
     */
    protected function isLoginPermitted()
    {
        return true;
    }

    /**
     * Check if alternative login uses
     *
     * @return boolean
     */
    protected function isAlternativeLoginUsed()
    {
        return false;
    }

    /**
     * Alternative login body
     *
     * @return void
     */
    protected function alternativeLoginBody()
    {
    }

    /**
     * Login body
     *
     * @return void
     */
    protected function loginBody()
    {
        $this->profile = $this->performLogin();

        if (!($this->profile instanceof \XLite\Model\Profile)) {
            $this->set('valid', false);
            $this->addLoginFailedMessage(\XLite\Core\Auth::RESULT_ACCESS_DENIED);
            \XLite\Logger::getInstance()
                ->log(sprintf('Log in action is failed (%s)', \XLite\Core\Request::getInstance()->login), LOG_WARNING);

        } else {
            if (\XLite\Core\Request::getInstance()->returnURL) {
                $url = preg_replace(
                    '/' . preg_quote(\XLite\Core\Session::getInstance()->getName()) . '=([^&]+)/',
                    '',
                    \XLite\Core\Request::getInstance()->returnURL
                );
                $this->setReturnURL($url);
            }

            $profileCart = $this->getCart();
            if (!$this->getReturnURL()) {
                $url = $profileCart->isEmpty()
                    ? \XLite\Core\Converter::buildURL()
                    : \XLite\Core\Converter::buildURL('cart');
                $this->setReturnURL($url);
            }

            $this->setHardRedirect();

            // We merge the logged in cart into the session cart
            $profileCart->login($this->profile);
            \XLite\Core\Database::getEM()->flush();

            if ($profileCart->isPersistent()) {
                $this->updateCart();
                \XLite\Core\Event::getInstance()->exclude('updateCart');
            }
        }
    }

    /**
     * Log out
     *
     * @return void
     */
    protected function doActionLogoff()
    {
        \XLite\Core\Auth::getInstance()->logoff();

        \Includes\Utils\Session::clearAdminCookie();

        $this->setReturnURL(\XLite\Core\Converter::buildURL());

        $this->getCart()->logoff();
        $this->updateCart();

        \XLite\Core\Database::getEM()->flush();
    }

    /**
     * Perform some actions before redirect
     *
     * @return void
     */
    protected function actionPostprocessLogin()
    {
        $this->redirectFromLogin();
    }

    /**
     * Add top message if log in is failed
     *
     * @param mixed $result Result of log in procedure
     *
     * @return void
     */
    protected function addLoginFailedMessage($result)
    {
        if (in_array($result, array(\XLite\Core\Auth::RESULT_ACCESS_DENIED, \XLite\Core\Auth::RESULT_PASSWORD_NOT_EQUAL))) {
            \XLite\Core\TopMessage::addError('Invalid login or password');
            \XLite\Core\Event::invalidForm('login-form', static::t('Invalid login or password'));
        }
    }
}
