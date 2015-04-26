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

namespace XLite\Module\CDev\Paypal\Controller\Customer;

/**
 * Paypal login controller
 */
class PaypalLogin extends \XLite\Controller\Customer\ACustomer
{
    /**
     * Perform login action
     *
     * @return void
     */
    protected function doActionLogin()
    {
        $loginApi = new \XLite\Module\CDev\Paypal\Core\Login();
        $requestProcessed = false;
        $returnURL = '';

        \XLite\Module\CDev\Paypal\Main::addLog(
            'Login return',
            \XLite\Core\Request::getInstance()->getData()
        );

        if ($loginApi->checkRequest()) {

            $accessToken = $loginApi->createFromAuthorisationCode(\XLite\Core\Request::getInstance()->code);

            $profileInfo = isset($accessToken['access_token'])
                ? $loginApi->getUserinfo($accessToken['access_token'])
                : null;

            if ($profileInfo && !empty($profileInfo['user_id']) && !empty($profileInfo['email'])) {

                $profile = $this->getSocialLoginProfile(
                    $profileInfo['email'],
                    'PayPal',
                    $profileInfo['user_id'],
                    $profileInfo
                );

                if ($profile) {
                    if ($profile->isEnabled()) {
                        \XLite\Core\Auth::getInstance()->loginProfile($profile);

                        $accessToken['expirationTime'] = LC_START_TIME + $accessToken['expires_in'];
                        \XLite\Core\Session::getInstance()->paypalAccessToken = $accessToken;

                        // We merge the logged in cart into the session cart
                        $profileCart = $this->getCart();
                        $profileCart->login($profile);
                        \XLite\Core\Database::getEM()->flush();

                        if ($profileCart->isPersistent()) {
                            $this->updateCart();
                        }

                        $returnURL = $this->getAuthReturnURL();

                    } else {
                        \XLite\Core\TopMessage::addError('Profile is disabled');
                        $returnURL = $this->getAuthReturnURL(true);
                    }

                } else {
                    $provider = \XLite\Core\Database::getRepo('XLite\Model\Profile')
                        ->findOneBy(array('login' => $profileInfo['email'], 'order' => null))
                        ->getSocialLoginProvider();

                    if ($provider) {
                        $signInVia = 'Please sign in with ' . $provider . '.';
                    } else {
                        $signInVia = 'Profile with the same e-mail address already registered. '
                            . 'Please sign in the classic way.';
                    }

                    \XLite\Core\TopMessage::addError($signInVia);
                    $returnURL = $this->getAuthReturnURL(true);
                }

                $requestProcessed = true;
            }
        }

        if (!$requestProcessed) {
            \XLite\Core\TopMessage::addError('We were unable to process this request');
            $returnURL = '';
        }

        $this->closePopup($returnURL);
    }

    /**
     * Fetches an existing social login profile or creates new
     *
     * @param string $login          E-mail address
     * @param string $socialProvider SocialLogin auth provider
     * @param string $socialId       SocialLogin provider-unique id
     * @param array  $profileInfo    Profile info OPTIONAL
     *
     * @return \XLite\Model\Profile
     */
    protected function getSocialLoginProfile($login, $socialProvider, $socialId, $profileInfo = array())
    {
        $profile = \XLite\Core\Database::getRepo('\XLite\Model\Profile')->findOneBy(
            array(
                'socialLoginProvider'   => $socialProvider,
                'socialLoginId'         => $socialId,
                'order'              => null,
            )
        );

        if (!$profile) {
            $profile = new \XLite\Model\Profile();
            $profile->setLogin($login);
            $profile->setSocialLoginProvider($socialProvider);
            $profile->setSocialLoginId($socialId);

            $existingProfile = \XLite\Core\Database::getRepo('XLite\Model\Profile')
                ->findOneBy(array('login' => $login, 'order' => null));

            if ($existingProfile) {
                $profile = null;
            } else {
                $profile->create();

                if (
                    $profileInfo
                    && isset($profileInfo['given_name'])
                    && isset($profileInfo['family_name'])
                    && isset($profileInfo['address'])
                ) {
                    $address = new \XLite\Model\Address();
                    $address->setProfile($profile);

                    $address->setFirstname($profileInfo['given_name']);
                    $address->setLastname($profileInfo['family_name']);

                    if (
                        isset($profileInfo['address']['country'])
                        && isset($profileInfo['address']['region'])
                    ) {
                        $address->setCountryCode($profileInfo['address']['country']);

                        $state = \XLite\Core\Database::getRepo('\XLite\Model\State')->findOneByCountryAndCode(
                            $profileInfo['address']['country'],
                            $profileInfo['address']['region']
                        );

                        if ($state) {
                            $address->setState($state);
                        }
                    }

                    if (isset($profileInfo['address']['locality'])) {
                        $address->setCity($profileInfo['address']['locality']);
                    }

                    if (isset($profileInfo['address']['street_address'])) {
                        $address->setStreet($profileInfo['address']['street_address']);
                    }

                    if (isset($profileInfo['address']['postal_code'])) {
                        $address->setZipcode($profileInfo['address']['postal_code']);
                    }

                    if (isset($profileInfo['phone_number'])) {
                        $address->setPhone($profileInfo['phone_number']);
                    }

                    $address->setIsShipping(true);
                    $address->setIsBilling(true);

                    $profile->addAddresses($address);
                    $address->create();
                }
            }
        }

        return $profile;
    }

    /**
     * Set redirect URL
     *
     * @param mixed $failure Indicates if auth process failed OPTIONAL
     *
     * @return string
     */
    protected function getAuthReturnUrl($failure = false)
    {
        $controller = \XLite\Core\Request::getInstance()->state;

        $redirectTo = $failure ? 'login' : '';

        if ('XLite\Controller\Customer\Checkout' == $controller) {
            $redirectTo = 'checkout';
        } elseif ('XLite\Controller\Customer\Profile' == $controller) {
            $redirectTo = 'profile';
        }

        return $this->buildURL($redirectTo);
    }

    // {{{ Popup related methods

    /**
     * Preprocessor for no-action run
     *
     * @return void
     */
    protected function doNoAction()
    {
        $returnUrl = $this->getShopURL(
            $this->buildURL(
                'paypal_login',
                'login',
                array(
                    'auth_provider' => 'PayPal',
                    'state' => \XLite\Core\Request::getInstance()->state,
                ),
                false
            ),
            \XLite\Core\Request::getInstance()->isHTTPS()
        );

        $api = new \XLite\Module\CDev\Paypal\Core\Login();
        $url = $api->getSignInURL($returnUrl);

        $this->redirect($url);
    }

    /**
     * Close Paypal login popup
     *
     * @param string $returnURL Return URL OPTIONAL
     *
     * @return void
     */
    protected function closePopup($returnURL = '')
    {
        if ($returnURL) {

            $returnURL = $this->getShopURL($returnURL);

            echo (
            <<<HTML
    <script type="text/javascript">
    window.opener.location.replace("{$returnURL}");
    window.close();
</script>
HTML
            );

        } else {
            echo (
            <<<HTML
    <script type="text/javascript">
    window.opener.location.reload();
    window.close();
</script>
HTML
            );
        }

        exit ();
    }

    // }}}
}
