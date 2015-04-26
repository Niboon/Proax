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
 * Checkout
 */
class Checkout extends \XLite\Controller\Customer\Cart
{
    /**
     * Checkout availability flag
     */
    const CHECKOUT_AVAIL_FLAG = 'checkout_avail_flag';

    /**
     * Checkout available flag timeout (24 hours)
     */
    const CHECKOUT_AVAIL_TIMEOUT = 86400;

    /**
     * Request data
     *
     * @var mixed
     */
    protected $requestData;

    /**
     * Payment widget data
     *
     * @var array
     */
    protected $paymentWidgetData = array();

    /**
     * Go to cart view if cart is empty
     *
     * @return void
     */
    public function handleRequest()
    {
        // We do not verify the cart when the customer returns from the payment
        if (
            \XLite\Core\Request::getInstance()->action !== 'return'
            && !$this->getCart()->checkCart()
        ) {
            $this->setHardRedirect();
            $this->setReturnURL($this->buildURL('cart'));
            $this->doRedirect();
        }

        parent::handleRequest();
    }

    /**
     * Get substep number
     *
     * @param string $name Substep name
     *
     * @return integer
     */
    public function getSubstepNumber($name)
    {
        $modifier = $this->getCart()->getModifier(\XLite\Model\Base\Surcharge::TYPE_SHIPPING, 'SHIPPING');

        $shippable = (!$modifier || !$modifier->canApply()) ? 0 : 1;

        switch ($name) {
            case 'shippingAddress':
                $result = 1;
                break;

            case 'billingAddress':
                $result = $shippable ? 2 : 1;
                break;

            case 'shippingMethods':
                $result = 3;
                break;

            case 'paymentMethods':
                $result = $shippable ? 4 : 2;
                break;

            default:
                $result = 1;
        }

        return $result;
    }

    /**
     * Get page title
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('Checkout');
    }

    /**
     * External call processSucceed() method
     * TODO: to revise
     *
     * @return void
     */
    public function callSuccess()
    {
        $this->processSucceed();
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
     * Get login URL
     *
     * @param string $email Email OPTIONAL
     *
     * @return string
     */
    public function getLoginURL($email = null)
    {
        $params = array();
        if ($email) {
            $params['login'] = $email;
        }

        return $this->buildURL('login', null, $params);
    }

    /**
     * Check - current profile is anonymous or not
     *
     * @return boolean
     */
    public function isAnonymous()
    {
        return !$this->getCart()->getProfile() || $this->getCart()->getProfile()->getAnonymous();
    }

    /**
     * Define the account links availability
     *
     * @return boolean
     */
    public function isAccountLinksVisible()
    {
        return parent::isAccountLinksVisible() && $this->isCheckoutAvailable();
    }

    /**
     * Define if the checkout is available
     * On the other hand the sign-in page is available only
     *
     * @return boolean
     */
    public function isCheckoutAvailable()
    {
        return \XLite\Core\Config::getInstance()->General->force_login_before_checkout
            ? $this->isLogged()
                || (
                    $this->getCart()->getProfile()
                    && $this->getCart()->getProfile()->getAnonymous()
                    && $this->getCheckoutAvailable()
                )
            : true;
    }

    /**
     * Get payment widget data
     *
     * @return array
     */
    public function getPaymentWidgetData()
    {
        return $this->paymentWidgetData;
    }


    /**
     * Checkout
     * TODO: to revise
     *
     * @return void
     */
    protected function doActionCheckout()
    {
        $itemsBeforeUpdate = $this->getCart()->getItemsFingerprint();
        $this->updateCart();
        $itemsAfterUpdate = $this->getCart()->getItemsFingerprint();

        if (
            $this->get('absence_of_product')
            || $this->getCart()->isEmpty()
            || $itemsAfterUpdate != $itemsBeforeUpdate
        ) {
            // Cart is changed
            $this->set('absence_of_product', true);
            $this->redirect($this->buildURL('cart'));

        } elseif (!$this->checkCheckoutAction()) {
            // Check access
            $this->redirect($this->buildURL('checkout'));

        } else {
            $data = is_array(\XLite\Core\Request::getInstance()->payment)
                ? \XLite\Core\Request::getInstance()->payment
                : array();

            $errors = array();
            $firstOpenTransaction = $this->getCart()->getFirstOpenPaymentTransaction();
            if ($firstOpenTransaction) {
                $errors = $firstOpenTransaction->getPaymentMethod()
                    ->getProcessor()
                    ->getInputErrors($data);
            }

            if ($errors) {
                foreach ($errors as $error) {
                    \XLite\Core\TopMessage::addError($error);
                }

                $this->redirect($this->buildURL('checkout'));

            } else {
                $shippingMethod = \XLite\Core\Database::getRepo('XLite\Model\Shipping\Method')->findOneBy(
                    array(
                        'method_id' => $this->getCart()->getShippingId()
                    )
                );

                if ($shippingMethod) {
                    $this->getCart()->setShippingMethodName($shippingMethod->getName());
                } else {
                    $this->getCart()->setShippingMethodName(null);
                }

                // Register 'Place order' event in the order history
                \XLite\Core\OrderHistory::getInstance()->registerPlaceOrder($this->getCart()->getOrderId());

                // Register 'Order packaging' event in the order history
                \XLite\Core\OrderHistory::getInstance()->registerOrderPackaging(
                    $this->getCart()->getOrderId(),
                    $this->getCart()->getModifier(\XLite\Model\Base\Surcharge::TYPE_SHIPPING, 'SHIPPING')
                );

                $this->getCart()->setPaymentStatus(\XLite\Model\Order\Status\Payment::STATUS_QUEUED);
                $this->getCart()->decreaseInventory();

                // Clean widget cache (for products lists widgets)
                \XLite\Core\WidgetCache::getInstance()->deleteAll();

                // Make order payment step
                $this->doPayment();
            }
        }
    }

    /**
     * Controller marks the cart calculation.
     * On the checkout page we need cart recalculation
     *
     * @return boolean
     */
    protected function markCartCalculate()
    {
        return true;
    }

    /**
     * Do payment
     * :TODO: to revise
     * :FIXME: decompose
     *
     * @return void
     */
    protected function doPayment()
    {
        $cart = $this->getCart();

        if (isset(\XLite\Core\Request::getInstance()->notes)) {
            $cart->setNotes(\XLite\Core\Request::getInstance()->notes);
        }

        if ($cart->hasCartStatus()) {
            $cart->setDate(\XLite\Core\Converter::time());

            // Set order number. This ID is incremented only for orders (not for cart entities)
            $cart->assignOrderNumber();
        }

        // Get first (and only) payment transaction
        $transaction = $cart->getFirstOpenPaymentTransaction();
        $result = null;
        $paymentStatusCode = null;

        if ($transaction) {
            // Process transaction
            $result = $transaction->handleCheckoutAction();

            $pstatus = $cart->getCalculatedPaymentStatus(true);
            if ($pstatus != \XLite\Model\Order\Status\Payment::STATUS_QUEUED) {
                $paymentStatusCode = $pstatus;
            }

        } elseif (!$cart->isOpen()) {
            // Cart is payed - create dump transaction
            $result = \XLite\Model\Payment\Transaction::COMPLETED;
            $paymentStatusCode = $cart->getCalculatedPaymentStatus(true);

        } else {
            $paymentStatusCode = $cart->getPaymentStatusCode();
        }

        if (\XLite\Model\Payment\Transaction::PROLONGATION == $result) {
            $this->set('silent', true);

            exit (0);

        } elseif (\XLite\Model\Payment\Transaction::SILENT == $result) {
            $this->paymentWidgetData = $transaction->getPaymentMethod()
                ->getProcessor()
                ->getPaymentWidgetData();
            $this->set('silent', true);

        } elseif (\XLite\Model\Payment\Transaction::SEPARATE == $result) {
            $this->setReturnURL($this->buildURL('checkoutPayment'));

        } elseif ($cart->isOpen()) {
            // Order is open - go to Select payment method step
            if ($transaction && $transaction->getNote()) {
                \XLite\Core\TopMessage::getInstance()->add(
                    $transaction->getNote(),
                    array(),
                    null,
                    $transaction->isFailed()
                        ? \XLite\Core\TopMessage::ERROR
                        : \XLite\Core\TopMessage::INFO,
                    true
                );
            }

            $this->setReturnURL($this->buildURL('checkout'));

        } else {
            if ($cart->isPayed()) {
                $paymentStatus = $paymentStatusCode ?: \XLite\Model\Order\Status\Payment::STATUS_PAID;
                $this->processSucceed();

            } elseif ($transaction && $transaction->isFailed()) {
                $paymentStatus = \XLite\Model\Order\Status\Payment::STATUS_DECLINED;

            } else {
                $paymentStatus = \XLite\Model\Order\Status\Payment::STATUS_QUEUED;
                $this->processSucceed();
                \XLite\Core\Mailer::getInstance()->sendOrderCreated($cart);
            }

            if ($paymentStatus) {
                $cart->setPaymentStatus($paymentStatus);
                \XLite\Core\Database::getEM()->flush();
            }

            \XLite\Core\TopMessage::getInstance()->clearTopMessages();

            $this->setReturnURL(
                $this->buildURL(
                    \XLite\Model\Order\Status\Payment::STATUS_DECLINED == $paymentStatus
                        ? 'checkoutFailed'
                        : 'checkoutSuccess',
                    '',
                    $cart->getOrderNumber()
                        ? array('order_number' => $cart->getOrderNumber())
                        : array('order_id' => $cart->getOrderId())
                )
            );
        }

        // Commented out in connection with E:0041438
        //$this->updateCart();
    }

    /**
     * Return from payment gateway
     *
     * :TODO: to revise
     * :FIXME: decompose
     *
     * @return void
     */
    protected function doActionReturn()
    {
        // some of gateways can't accept return url on run-time and
        // use the one set in merchant account, so we can't pass
        // 'order_id' in run-time, instead pass the order id parameter name
        $orderId = \XLite\Core\Request::getInstance()->order_id;
        $cart = \XLite\Core\Database::getRepo('XLite\Model\Cart')->find($orderId)
            ?: \XLite\Core\Database::getRepo('XLite\Model\Order')->find($orderId);

        if ($cart) {
            \XLite\Model\Cart::setObject($cart);
        }

        if (!$cart) {

            // Cart not found
            unset(\XLite\Core\Session::getInstance()->order_id);
            \XLite\Core\TopMessage::addError('Order not found');
            $this->setReturnURL($this->buildURL('cart'));

        } elseif ($cart->isOpen()) {

            // Order still not payed
            $this->setReturnURL($this->buildURL('checkout'));

        } else {

            // Order payed or pending
            if ($cart instanceOf \XLite\Model\Cart) {
                $cart->tryClose($this);
                \XLite\Core\Database::getEM()->flush();
            }

            \XLite\Core\TopMessage::getInstance()->clearTopMessages();
            $this->setReturnURL(
                $this->buildURL(
                    $this->getStatusTarget($cart->getPaymentStatusCode()),
                    '',
                    $cart->getOrderNumber()
                        ? array('order_number' => $cart->getOrderNumber())
                        : array('order_id' => $orderId)
                )
            );
        }
    }

    /**
     * Defines the target to return according the order status
     *
     * @param string $paymentStatus Order status
     *
     * @return string Target name
     */
    protected function getStatusTarget($paymentStatus)
    {
        switch ($paymentStatus) {
            case \XLite\Model\Order\Status\Payment::STATUS_CANCELED:
                $result = 'checkoutCanceled';
                break;

            case \XLite\Model\Order\Status\Payment::STATUS_DECLINED:
                $result = 'checkoutFailed';
                break;

            default:
                $result = 'checkoutSuccess';
        }

        return $result;
    }

    /**
     * Order placement is success
     *
     * :TODO: to revise
     * :FIXME: decompose
     *
     * @param boolean $fullProcess Full process or not OPTIONAL
     *
     * @return void
     */
    public function processSucceed($fullProcess = true)
    {
        $this->processCartProfile($fullProcess);

        if ($fullProcess) {
            $this->getCart()->processSucceed();
        }

        // Save order id in session and forget cart id from session
        \XLite\Core\Session::getInstance()->last_order_id = $this->getCart()->getOrderId();
        \XLite\Core\Session::getInstance()->unsetBatch(
            'order_id',
            'order_create_profile',
            'saveShippingAsNew',
            'createProfilePassword',
            'lastLoginUnique'
        );

        // Commented out in connection with E:0041438
        //$this->updateCart();

        // Mark all addresses as non-work
        if ($this->getCart()->getOrigProfile()) {
            foreach ($this->getCart()->getOrigProfile()->getAddresses() as $address) {
                $address->setIsWork(false);
            }
        }

        if ($this->getCart()->getProfile()) {
            foreach ($this->getCart()->getProfile()->getAddresses() as $address) {
                $address->setIsWork(false);
            }
        }

        \XLite\Core\Database::getEM()->flush();
    }

    /**
     * Process cart profile
     *
     * @param boolean $doCloneProfile Clone profile flag
     *
     * @return boolean
     */
    protected function processCartProfile($doCloneProfile)
    {
        $isAnonymous = $this->isAnonymous();

        if ($isAnonymous) {
            if (\XLite\Core\Session::getInstance()->order_create_profile) {
                // Create profile based on anonymous order profile
                $this->saveAnonymousProfile();
                $this->loginAnonymousProfile();

                $this->getCart()->getOrigProfile()->setPassword(
                    \XLite\Core\Auth::encryptPassword(\XLite\Core\Session::getInstance()->createProfilePassword)
                );

                $isAnonymous = false;

            } elseif ($doCloneProfile) {
                $this->mergeAnonymousProfile();
            }

        } elseif ($doCloneProfile) {
            // Clone profile
            $this->cloneProfile();
            $isAnonymous = false;
        }

        return $isAnonymous;
    }

    /**
     * Save anonymous profile
     *
     * @return void
     */
    protected function saveAnonymousProfile()
    {
        // Create cloned profile
        $profile = $this->getCart()->getProfile()->cloneEntity();

        // Generate password
        $profile->setAnonymous(false);
        $profile->setOrder(null);

        // Set cloned profile as original profile
        $this->getCart()->setOrigProfile($profile);

        // Send notifications
        $this->sendCreateProfileNotifications();
    }

    /**
     * Merge anonymous profile
     *
     * @return void
     */
    protected function mergeAnonymousProfile()
    {
        $profile = \XLite\Core\Database::getRepo('XLite\Model\Profile')
            ->findOneAnonymousByProfile($this->getCart()->getProfile());

        if ($profile) {
            $profile->mergeWithProfile(
                $this->getCart()->getProfile(),
                \XLite\Model\Profile::MERGE_ALL ^ \XLite\Model\Profile::MERGE_ORDERS
            );

        } else {
            $profile = $this->getCart()->getProfile()->cloneEntity();
            $profile->setOrder(null);
            $profile->setAnonymous(true);
        }
        $this->getCart()->setOrigProfile($profile);

        \XLite\Core\Database::getEM()->flush();
    }

    /**
     * Login anonymous profile
     *
     * @return void
     */
    protected function loginAnonymousProfile()
    {
        \XLite\Core\Auth::getInstance()->loginProfile($this->getCart()->getOrigProfile());
    }

    /**
     * Send create profile notifications
     *
     * @param string $password Password OPTIONAL
     *
     * @return void
     */
    protected function sendCreateProfileNotifications($password = null)
    {
        $profile = $this->getCart()->getOrigProfile();

        // Send notification
        \XLite\Core\Mailer::sendProfileCreated($profile, $password, true);
    }

    /**
     * Clone profile and move profile to original profile
     *
     * @return void
     */
    protected function cloneProfile()
    {
        $origProfile = $this->getCart()->getProfile();
        $profile = $origProfile->cloneEntity();

        // Assign cloned order's profile
        $this->getCart()->setProfile($profile);
        $profile->setOrder($this->getCart());

        // Save old profile as original profile
        $this->getCart()->setOrigProfile($origProfile);
        $origProfile->setOrder(null);

        if (
            \XLite\Core\Session::getInstance()->checkoutEmail
            && \XLite\Core\Session::getInstance()->checkoutEmail != $this->getCart()->getProfile()->getLogin()
        ) {
            $this->getCart()->getProfile()->setLogin(\XLite\Core\Session::getInstance()->checkoutEmail);
        }
        unset(\XLite\Core\Session::getInstance()->checkoutEmail);

        \XLite\Core\Database::getEM()->flush();
    }

    /**
     * isRegistrationNeeded
     * (CHECKOUT_MODE_REGISTER step check)
     *
     * @return boolean
     */
    protected function isRegistrationNeeded()
    {
        return !\XLite\Core\Auth::getInstance()->isLogged();
    }

    /**
     * Check if order total is zero
     *
     * @return boolean
     */
    protected function isZeroOrderTotal()
    {
        return 0 == $this->getCart()->getTotal()
            && \XLite\Core\Config::getInstance()->Payments->default_offline_payment;
    }

    /**
     * Check if we are ready to select payment method
     *
     * @return boolean
     */
    protected function isPaymentNeeded()
    {
        return !$this->getCart()->getPaymentMethod() && $this->getCart()->getOpenTotal();
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
     * Check amount for all cart items
     *
     * @return void
     */
    protected function checkItemsAmount()
    {
        // Do not call parent: it's only needed to check amounts in cart, not on checkout
    }

    /**
     * The checkout availability flag
     *
     * @return boolean
     */
    protected function getCheckoutAvailable()
    {
        // Checkout available flag will be reset after 24 hours (86400 seconds)
        return ((intval(\XLite\Core\Session::getInstance()->{static::CHECKOUT_AVAIL_FLAG}) + static::CHECKOUT_AVAIL_TIMEOUT) >= time());
    }

    /**
     * The checkout is available now (Sign-in page is hidden now)
     *
     * @return void
     */
    protected function setCheckoutAvailable()
    {
        \XLite\Core\Session::getInstance()->{static::CHECKOUT_AVAIL_FLAG} = time();
    }

    /**
     * Update profile
     *
     * @return void
     */
    protected function doActionUpdateProfile()
    {
        $form = new \XLite\View\Form\Checkout\UpdateProfile();

        $this->requestData = $form->getRequestData();

        $this->updateProfile();

        $this->updateShippingAddress();

        $this->updateBillingAddress();

        $this->setCheckoutAvailable();

        if (
            empty($this->requestData['shippingAddress'])
            && empty($this->requestData['billingAddress'])
            && isset($this->requestData['same_address'])
        ) {
            \XLite\Core\Session::getInstance()->same_address = (bool)$this->requestData['same_address'];
        }

        $this->updateCart();
    }

    /**
     * Update profile
     *
     * @return void
     */
    protected function updateProfile()
    {
        if ($this->isAnonymous()) {
            $this->updateAnonymousProfile();

        } else {
            $this->updateLoggedProfile();
        }
    }

    /**
     * Update anonymous profile
     *
     * @return void
     */
    protected function updateAnonymousProfile()
    {
        $login = $this->requestData['email'];

        if (isset($login)) {
            $tmpProfile = new \XLite\Model\Profile;
            $tmpProfile->setProfileId(0);
            $tmpProfile->setLogin($login);

            $profile = \XLite\Core\Database::getRepo('XLite\Model\Profile')->findUserWithSameLogin($tmpProfile);

            $exists = $profile && !$profile->getAnonymous();

            if ($profile) {
                \XLite\Core\Database::getEM()->detach($profile);
            }

            if ($exists) {
                \XLite\Core\Session::getInstance()->order_create_profile = false;
            }

            \XLite\Core\Session::getInstance()->lastLoginUnique = !$exists;

            $profile = $this->getCartProfile();
            $profile->setLogin($login);
            \XLite\Core\Database::getEM()->flush($profile);

            \XLite\Core\Event::loginExists(
                array(
                    'value' => $exists,
                )
            );

            if ($exists && $this->requestData['create_profile']) {
                // Profile with same login is exists
                $this->valid = false;

            } elseif (
                isset($this->requestData['password'])
                && !$this->requestData['password']
                && $this->requestData['create_profile']
            ) {
                $this->valid = false;

                $label = static::t('Field is required!');

                \XLite\Core\Event::invalidElement('password', $label);

            } elseif (false !== $this->valid) {
                \XLite\Core\Session::getInstance()->order_create_profile = (bool)$this->requestData['create_profile'];

                if (\XLite\Core\Session::getInstance()->order_create_profile) {
                    \XLite\Core\Session::getInstance()->createProfilePassword = $this->requestData['password'];
                }
            }
        }
    }

    /**
     * Update logged profile
     *
     * @return void
     */
    protected function updateLoggedProfile()
    {
        $login = $this->requestData['email'];

        if (isset($login)) {
            \XLite\Core\Session::getInstance()->checkoutEmail = $login;
        }
    }

    /**
     * Update shipping address
     *
     * @return void
     */
    protected function updateShippingAddress()
    {
        $data = $this->requestData['shippingAddress'];

        $profile = $this->getCartProfile();

        $address = $profile->getShippingAddress();
        if ($address) {
            \XLite\Core\Database::getEM()->refresh($address);
        }

        if (is_array($data)) {
            $noAddress = !isset($address);

            $andAsBilling = false;

            $current = new \XLite\Model\Address;
            $current->map($this->prepareAddressData($data, 'shipping'));
            $equal = null;
            foreach ($profile->getAddresses() as $addressEqual) {
                if (
                    (!$address || $address->getAddressId() != $addressEqual->getAddressId())
                    && $addressEqual->isEqualAddress($current)
                ) {
                    $equal = $addressEqual;
                    break;
                }
            }

            if ($equal) {
                if ($address->getIsWork()) {
                    $profile->getAddresses()->removeElement($address);
                    \XLite\Core\Database::getEM()->remove($address);
                }

                if ($address) {
                    $andAsBilling = $address->getIsBilling();
                    $address->setIsShipping(false);

                    if ($andAsBilling) {
                        $address->setIsBilling(false);
                    }
                }

                $address = $equal;
                $address->setIsShipping(true);

                if ($andAsBilling) {
                    $address->setIsBilling($andAsBilling);
                }

                $noAddress = false;
            }

            if ($noAddress || (!$address->getIsWork() && !$address->isEqualAddress($current))) {
                if (!$noAddress) {
                    $andAsBilling = $address->getIsBilling();
                    $address->setIsBilling(false);
                    $address->setIsShipping(false);
                }

                $address = new \XLite\Model\Address;

                $address->setProfile($profile);
                $address->setIsShipping(true);
                $address->setIsBilling($andAsBilling);
                $address->setIsWork(true);

                if ($noAddress || !(bool)\XLite\Core\Request::getInstance()->only_calculate) {
                    $profile->addAddresses($address);
                    \XLite\Core\Database::getEM()->persist($address);
                }
            }

            $address->map($this->prepareAddressData($data, 'shipping'));

            if (
                $noAddress
                && !$profile->getBillingAddress()
                && (
                    is_null(\XLite\Core\Session::getInstance()->same_address)
                    || \XLite\Core\Session::getInstance()->same_address
                )
            ) {
                // Same address as default behavior
                $address->setIsBilling(true);
            }

            \XLite\Core\Session::getInstance()->same_address = $this->getCart()->getProfile()->isEqualAddress();

            if ($noAddress) {
                \XLite\Core\Event::createShippingAddress(array('id' => $address->getAddressId()));
            }
        }
    }

    /**
     * Update profile billing address
     *
     * @return void
     */
    protected function updateBillingAddress()
    {
        $noAddress = false;

        $data = empty($this->requestData['billingAddress']) ? null : $this->requestData['billingAddress'];

        $profile = $this->getCartProfile();

        if (isset($this->requestData['same_address'])) {
            \XLite\Core\Session::getInstance()->same_address = (bool)$this->requestData['same_address'];
        }

        if ($this->requestData['same_address']) {
            // Shipping and billing are same addresses
            $address = $profile->getBillingAddress();

            if ($address) {
                // Unselect old billing address
                $address->setIsBilling(false);
            }

            $address = $profile->getShippingAddress();

            if ($address) {
                // Link shipping and billing address
                $address->setIsBilling(true);
            }

        } elseif (
            isset($this->requestData['same_address'])
            && !$this->requestData['same_address']
        ) {
            // Unlink shipping and billing addresses
            $address = $profile->getShippingAddress();

            if ($address && $address->getIsBilling()) {
                $address->setIsBilling(false);
            }
        }

        if (!$this->requestData['same_address'] && is_array($data)) {
            // Save separate billing address
            $address = $profile->getBillingAddress();

            if ($address) {
                \XLite\Core\Database::getEM()->refresh($address);
            }

            $andAsShipping = false;

            $current = new \XLite\Model\Address;
            $current->map($this->prepareAddressData($data, 'billing'));
            $equal = null;

            foreach ($profile->getAddresses() as $addressEqual) {
                if (
                    (!$address || $address->getAddressId() != $addressEqual->getAddressId())
                    && $addressEqual->isEqualAddress($current)
                ) {
                    $equal = $addressEqual;
                    break;
                }
            }

            if ($equal) {
                if ($address && $address->getIsWork()) {
                    $profile->getAddresses()->removeElement($address);
                    \XLite\Core\Database::getEM()->remove($address);
                }

                if ($address) {
                    $andAsShipping = $address->getIsShipping();
                    $address->setIsBilling(false);
                    if ($andAsShipping) {
                        $address->setIsShipping(false);
                    }
                }

                $address = $equal;
                $address->setIsBilling(true);

                if ($andAsShipping) {
                    $address->setIsShipping($andAsShipping);
                }
            }

            if (!$address || (!$address->getIsWork() && !$address->isEqualAddress($current))) {
                if ($address) {
                    $andAsShipping = $address->getIsShipping();
                    $address->setIsBilling(false);
                    $address->setIsShipping(false);
                }

                $address = new \XLite\Model\Address;
                $address->setProfile($profile);
                $address->setIsBilling(true);
                $address->setIsShipping($andAsShipping);
                $address->setIsWork(true);

                if (!(bool)\XLite\Core\Request::getInstance()->only_calculate) {
                    $profile->addAddresses($address);

                    \XLite\Core\Database::getEM()->persist($address);
                    $noAddress = true;
                }
            }

            $address->map($this->prepareAddressData($data, 'billing'));

            \XLite\Core\Session::getInstance()->same_address = $this->getCart()->getProfile()->isEqualAddress();
        }

        if ($noAddress) {
            \XLite\Core\Event::createBillingAddress(array('id' => $address->getAddressId()));
        }
    }

    /**
     * Prepare address data
     *
     * @param array  $data Address data
     * @param string $type Address type OPTIONAL
     *
     * @return array
     */
    protected function prepareAddressData(array $data, $type = 'shipping')
    {
        unset($data['save_in_book']);

        $requiredFields = 'shipping' == $type
            ? \XLite\Core\Database::getRepo('XLite\Model\AddressField')->getShippingRequiredFields()
            : \XLite\Core\Database::getRepo('XLite\Model\AddressField')->getBillingRequiredFields();

        foreach ($requiredFields as $fieldName) {
            if (!isset($data[$fieldName]) && \XLite\Model\Address::getDefaultFieldValue($fieldName)) {
                $data[$fieldName] = \XLite\Model\Address::getDefaultFieldValue($fieldName);
            }
        }

        return $data;
    }

    /**
     * Set payment method
     *
     * @return void
     */
    protected function doActionPayment()
    {
        /** @var \XLite\Model\Payment\Method $pm */
        $pm = \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
            ->find(\XLite\Core\Request::getInstance()->methodId);

        if (!$pm) {
            \XLite\Core\TopMessage::addError(
                'No payment method selected'
            );

        } else {
            if ($this->getCart()->getProfile()) {
                $this->getCart()->getProfile()->setLastPaymentId($pm->getMethodId());
            }

            $this->getCart()->setPaymentMethod($pm);

            $this->updateCart();

            if ($this->isPaymentNeeded()) {
                \XLite\Core\TopMessage::addError(
                    'The selected payment method is obsolete or invalid. Select another payment method'
                );
            }
        }
    }

    /**
     * Change shipping method
     *
     * @return void
     */
    protected function doActionShipping()
    {
        if (isset(\XLite\Core\Request::getInstance()->methodId)) {
            $this->getCart()->getProfile()->setLastShippingId(\XLite\Core\Request::getInstance()->methodId);
            $this->getCart()->setShippingId(\XLite\Core\Request::getInstance()->methodId);
            $this->updateCart();

        } else {
            $this->valid = false;
        }
    }

    /**
     * Check checkout action accessibility
     *
     * @return boolean
     */
    public function checkCheckoutAction()
    {
        $result = true;

        $steps = new \XLite\View\Checkout\Steps();
        foreach (array_slice($steps->getSteps(), 0, -1) as $step) {
            if (!$step->isCompleted()) {
                $result = false;
                break;
            }
        }

        return $result && $this->checkReviewStep();
    }

    /**
     * Check review step - complete or not
     *
     * @return string
     */
    protected function checkReviewStep()
    {
        return $this->getCart()->getProfile()->getLogin();
    }

    /**
     * Get cart fingerprint exclude keys
     *
     * @return array
     */
    protected function getCartFingerprintExclude()
    {
        return array();
    }

    /**
     * Check - is service controller or not
     *
     * @return boolean
     */
    protected function isServiceController()
    {
        return in_array($this->getAction(), array('return'));
    }
}
