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
 * Abstract controller for Customer interface
 */
abstract class ACustomer extends \XLite\Controller\AController
{
    /**
     * cart
     *
     * @var \XLite\Model\Cart
     */
    protected $cart;

    /**
     * Initial cart fingerprint
     *
     * @var array
     */
    protected $initialCartFingerprint;

    /**
     * Breadcrumbs
     *
     * @var \XLite\View\Location
     */
    protected $locationPath;

    // {{{ Breadcrumbs

    /**
     * Return current location path
     *
     * @return \XLite\View\Location
     */
    public function getLocationPath()
    {
        if (!isset($this->locationPath)) {
            $this->defineLocationPath();
        }

        return $this->locationPath;
    }

    /**
     * Return true if checkout layout is used
     *
     * @return boolean
     */
    public function isCheckoutLayout()
    {
        return in_array($this->getTarget(), array('checkout', 'checkoutPayment'));
    }

    /**
     * Define the account links availability
     *
     * @return boolean
     */
    public function isAccountLinksVisible()
    {
        return !$this->isLogged();
    }

    /**
     * Method to create the location line
     *
     * @return void
     */
    protected function defineLocationPath()
    {
        $this->locationPath = array();

        // Ability to add part to the line
        $this->addBaseLocation();

        // Ability to define last element in path via short function
        $location = $this->getLocation();

        if ($location) {
            $this->addLocationNode($location);
        }
    }

    /**
     * Common method to determine current location
     *
     * @return string
     */
    protected function getLocation()
    {
        return null;
    }

    /**
     * Add part to the location nodes list
     *
     * @return void
     */
    protected function addBaseLocation()
    {
        // Common element for all location lines
        $this->locationPath[] = new \XLite\View\Location\Node\Home();
    }

    /**
     * Add node to the location line
     *
     * @param string $name     Node title
     * @param string $link     Node link OPTIONAL
     * @param array  $subnodes Node subnodes OPTIONAL
     *
     * @return void
     */
    protected function addLocationNode($name, $link = null, array $subnodes = null)
    {
        $this->locationPath[] = \XLite\View\Location\Node::create($name, $link, $subnodes);
    }

    // }}}

    /**
     * Return current category Id
     *
     * @return integer
     */
    public function getCategoryId()
    {
        return parent::getCategoryId() ?: $this->getRootCategoryId();
    }

    /**
     * Return cart instance
     *
     * @param null|boolean $doCalculate Flag: completely recalculate cart if true OPTIONAL
     *
     * @return \XLite\Model\Order
     */
    public function getCart($doCalculate = null)
    {
        return \XLite\Model\Cart::getInstance(isset($doCalculate) ? $doCalculate : $this->markCartCalculate());
    }

    /**
     * Defines the canonical URL for the page
     *
     * @return string
     */
    public function getCanonicalURL()
    {
        $params = $this->getAllParams();
        $target = isset($params['target']) ? $params['target'] : '';
        unset($params['target']);
        // Product pages do not count the category identificator for the canonical URL
        if ('product' === $target) {
            unset($params['category_id']);
        }

        if (
            isset($_SERVER)
            && isset($_SERVER['QUERY_STRING'])
            && '' === $_SERVER['QUERY_STRING']
            && isset($_SERVER['SCRIPT_URL'])
            && (
                '' === $_SERVER['SCRIPT_URL']
                || false === strpos($_SERVER['SCRIPT_URL'], '.php')
            )
        ) {
            $canonicalURL = '';

        } elseif ('main' === $target) {
            $canonicalURL = $this->getShopURL();

        } else {
            $canonicalURL = $this->getShopURL(
                \XLite\Core\Converter::buildURL($target, '', $params, null, true),
                $this->isHTTPS()
            );
        }

        return $canonicalURL;
    }

    /**
     * Controller marks the cart calculation.
     * In some cases we do not need to recalculate the cart.
     * We need it mainly on the checkout page.
     *
     * @return boolean
     */
    protected function markCartCalculate()
    {
        return false;
    }

    /**
     * Get cart fingerprint exclude keys
     *
     * @return array
     */
    protected function getCartFingerprintExclude()
    {
        $result = array();

        if (!$this->markCartCalculate()) {
            $result[] = 'shippingMethodsHash';
            $result[] = 'shippingTotal';
            $result[] = 'shippingMethodId';
        }

        return $result;
    }

    /**
     * Get the full URL of the page
     * Example: getShopURL('cart.php') = "http://domain/dir/cart.php
     *
     * @param string  $url    Relative URL OPTIONAL
     * @param boolean $secure Flag to use HTTPS OPTIONAL
     * @param array   $params Optional URL params OPTIONAL
     *
     * @return string
     */
    public function getShopURL($url = '', $secure = null, array $params = array())
    {
        if (!isset($secure) && $this->isFullCustomerSecurity()) {
            $secure = true;
        }

        return parent::getShopURL($url, $secure, $params);
    }

    /**
     * Get current profile username
     *
     * @return string
     */
    public function getProfileUsername()
    {
        return $this->getCart()->getProfile()
            ? $this->getCart()->getProfile()->getLogin()
            : '';
    }

    /**
     * Handles the request
     *
     * @return void
     */
    public function handleRequest()
    {
        if (!$this->checkStorefrontAccessibility()) {
            $this->closeStorefront();
        }

        if (!$this->isServiceController()) {
            // Save initial cart fingerprint
            $this->initialCartFingerprint = $this->getCart()->getEventFingerprint($this->getCartFingerprintExclude());
        }

        parent::handleRequest();
    }

    /**
     * Check - is top 'Continue Shopping' button is visible or not
     *
     * @return boolean
     */
    public function isContinueShoppingVisible()
    {
        return false;
    }

    /**
     * Get continue shopping URL
     *
     * @return string
     */
    public function getContinueShoppingURL()
    {
        if (\XLite\Core\Session::getInstance()->continueShoppingURL) {
            $url = \XLite\Core\Session::getInstance()->continueShoppingURL;

        } elseif (isset($_SERVER['HTTP_REFERER'])) {
            $url = $_SERVER['HTTP_REFERER'];

        } else {
            $url = $this->buildURL();
        }

        return \XLite::getInstance()->getShopURL($url);
    }

    /**
     * Check if current page is accessible
     *
     * @return boolean
     */
    protected function checkAccess()
    {
        return parent::checkAccess() && $this->checkFormId();
    }

    /**
     * Stub for the CMS connectors
     *
     * @return boolean
     */
    protected function checkStorefrontAccessibility()
    {
        return \XLite\Core\Auth::getInstance()->isAccessibleStorefront();
    }

    /**
     * Perform some actions to prohibit access to storefornt
     *
     * @return void
     */
    protected function closeStorefront()
    {
        \Includes\ErrorHandler::fireError(
            'Storefront is closed',
            \Includes\ErrorHandler::ERROR_CLOSED
        );
    }

    /**
     * Return template to use in a CMS
     *
     * @return string
     */
    protected function getCMSTemplate()
    {
        return 'center_top.tpl';
    }

    /**
     * Select template to use
     *
     * @return string
     */
    protected function getViewerTemplate()
    {
        return $this->getParam(self::PARAM_IS_EXPORTED) ? $this->getCMSTemplate() : parent::getViewerTemplate();
    }

    /**
     * Recalculates the shopping cart
     *
     * @return void
     */
    protected function updateCart()
    {
        if ($this->markCartCalculate()) {
            $this->getCart()->updateOrder();
        }

        \XLite\Core\Database::getRepo('XLite\Model\Cart')->update($this->getCart());

        $this->assembleEvent();

        $this->initialCartFingerprint = $this->getCart()->getEventFingerprint($this->getCartFingerprintExclude());
    }

    /**
     * Assemble updateCart event
     *
     * @return boolean
     */
    protected function assembleEvent()
    {
        $diff = $this->getCartFingerprintDifference(
            $this->initialCartFingerprint,
            $this->getCart()->getEventFingerprint($this->getCartFingerprintExclude())
        );

        if ($diff) {
            $actualDiff = $this->posprocessCartFingerprintDifference($diff);
            if ($actualDiff) {
                \XLite\Core\Event::updateCart($actualDiff);
            }
        }

        return (bool)$diff;
    }

    /**
     * Get fingerprint difference
     *
     * @param array $old Old fingerprint
     * @param array $new New fingerprint
     *
     * @return array
     */
    protected function getCartFingerprintDifference(array $old, array $new)
    {
        $diff = array();

        $items = array();

        // Assembly changed
        foreach ($new['items'] as $n => $cell) {
            $found = false;
            foreach ($old['items'] as $i => $oldCell) {
                if ($cell['key'] == $oldCell['key']) {
                    if ($cell['quantity'] != $oldCell['quantity']) {
                        $cell['quantity_change'] = $cell['quantity'] - $oldCell['quantity'];
                        $items[] = $cell;
                    }

                    unset($old['items'][$i]);
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $cell['quantity_change'] = $cell['quantity'];
                $items[] = $cell;
            }
        }

        // Assemble removed
        foreach ($old['items'] as $cell) {
            $cell['quantity_change'] = $cell['quantity'] * -1;
            $items[] = $cell;
        }

        if ($items) {
            $diff['items'] = $items;
        }

        $cellKeys = array(
            'shippingTotal',
            'shippingMethodId',
            'paymentMethodId',
            'shippingAddressId',
            'billingAddressId',
            'sameAddress',
            'shippingMethodsHash',
            'paymentMethodsHash',
            'itemsCount',
        );

        foreach ($cellKeys as $name) {
            $old[$name] = isset($old[$name]) ? $old[$name] : '';
            $new[$name] = isset($new[$name]) ? $new[$name] : '';

            if ($old[$name] != $new[$name]) {
                $diff[$name] = $new[$name];
            }
        }

        // Assemble total diff
        if ($old['total'] != $new['total']) {
            $diff['total'] = $new['total'] - $old['total'];
        }

        return $diff;
    }

    /**
     * Postprocess cart fingerprint differences and exclude some of them
     *
     * @param array $diff Differences
     *
     * @return array
     */
    protected function posprocessCartFingerprintDifference(array $diff)
    {
        $result = array();

        foreach ($diff as $name => $data) {

            $isAvail = true;

            $method = 'postprocessDifference' . ucfirst($name);
            if (method_exists($this, $method)) {
                // postprocessDifference + <param name>
                $isAvail = $this->{$method}($data);
            }

            if ($isAvail) {
                $result[$name] = $data;
            }
        }

        return $result;
    }

    /**
     * Postprocess fingerprint difference parameter.
     * Return false if this param should be removed from event-updateCart params list.
     *
     * @param array $data New payment method ID
     *
     * @return boolean
     */
    protected function postprocessDifferencePaymentMethodId($data)
    {
        $oldPaymentMethod = null;

        // Get old payment method
        if (!empty($this->initialCartFingerprint)) {
            $oldPaymentMethod = \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
                ->find($this->initialCartFingerprint['paymentMethodId']);
        }

        $newPaymentMethod = $this->getCart()->getPaymentMethod();

        return ($newPaymentMethod && $newPaymentMethod->isCheckoutUpdateActionRequired())
            || ($oldPaymentMethod && $oldPaymentMethod->isCheckoutUpdateActionRequired());
    }

    /**
     * isCartProcessed
     *
     * @return boolean
     */
    protected function isCartProcessed()
    {
        return $this->getCart()->isProcessed() || $this->getCart()->isQueued();
    }

    /**
     * Get or create cart profile
     *
     * @return \XLite\Model\Profile
     */
    protected function getCartProfile()
    {
        $profile = $this->getCart()->getProfile();

        if (!$profile) {
            $profile = new \XLite\Model\Profile;
            $profile->setLogin('');
            $profile->setOrder($this->getCart());
            $profile->setAnonymous(true);
            $this->getCart()->setProfile($profile);
            $profile->create();
        }

        return $profile;
    }

    /**
     * Check - need use secure protocol or not
     *
     * @return boolean
     */
    public function needSecure()
    {
        return parent::needSecure()
            || (!$this->isHTTPS()) && $this->isFullCustomerSecurity();
    }

    /**
     * Check if the any customer script must be redirected to HTTPS
     *
     * @return boolean
     */
    protected function isFullCustomerSecurity()
    {
        return false;
    }

    // {{{ Clean URLs related routines

    /**
     * Preprocessor for no-action run
     *
     * @return void
     */
    protected function doNoAction()
    {
        parent::doNoAction();

        if (
            LC_USE_CLEAN_URLS
            && !$this->isAJAX()
            && !$this->isRedirectNeeded()
            && $this->isRedirectToCleanURLNeeded()
        ) {
            $this->performRedirectToCleanURL();
        }
    }

    /**
     * Check if redirect to clean URL is needed
     *
     * @return boolean
     */
    protected function isRedirectToCleanURLNeeded()
    {
        return preg_match(
            '/\/cart\.php/Si',
            \Includes\Utils\ArrayManager::getIndex(\XLite\Core\Request::getInstance()->getServerData(), 'REQUEST_URI')
        );
    }

    /**
     * Redirect to clean URL
     *
     * @return void
     */
    protected function performRedirectToCleanURL()
    {
        $data = \XLite\Core\Request::getInstance()->getGetData();

        $target = $this->getTarget();

        if (\XLite::TARGET_DEFAULT === $target) {
            $target = '';
        } else {
            unset($data['target']);
        }

        $this->setReturnURL(\XLite\Core\Converter::buildFullURL($target, '', $data));
    }

    // }}}

    // {{{ Getters

    /**
     * Get address fields
     *
     * @return array
     */
    public function getAddressFields()
    {
        $result = array();

        foreach (\XLite\Core\Database::getRepo('XLite\Model\AddressField')->findAllEnabled() as $field) {
            $result[$field->getServiceName()] = array(
                \XLite\View\Model\Address\Address::SCHEMA_CLASS    => $field->getSchemaClass(),
                \XLite\View\Model\Address\Address::SCHEMA_LABEL    => $field->getName(),
                \XLite\View\Model\Address\Address::SCHEMA_REQUIRED => $field->getRequired(),
                \XLite\View\Model\Address\Address::SCHEMA_MODEL_ATTRIBUTES => array(
                    \XLite\View\FormField\Input\Base\String::PARAM_MAX_LENGTH => 'length',
                ),
                \XLite\View\FormField\AFormField::PARAM_WRAPPER_CLASS => 'address-' . $field->getServiceName(),
            );
        }

        return $result;
    }

    /**
     * Get field value
     *
     * @param string               $fieldName    Field name
     * @param \XLite\Model\Address $address      Field name
     * @param boolean              $processValue Process value flag OPTIONAL
     *
     * @return string
     */
    public function getFieldValue($fieldName, \XLite\Model\Address $address, $processValue = false)
    {
        $result = '';

        if (isset($address)) {

            $methodName = 'get' . \XLite\Core\Converter::getInstance()->convertToCamelCase($fieldName);

            // $methodName assembled from 'get' + camelized $fieldName
            $result = $address->$methodName();

            if ($result && false !== $processValue) {

                switch ($fieldName) {
                    case 'state_id':
                        $result = $address->getCountry()->hasStates()
                            ? $address->getState()->getState()
                            : null;
                        break;

                    case 'custom_state':
                        $result = $address->getCountry()->hasStates()
                            ? null
                            : $result;
                        break;

                    case 'country_code':
                        $result = $address->getCountry()->getCountry();
                        break;

                    case 'type':
                        $result = $address->getTypeName();
                        break;

                    default:

                }
            }
        }

        return $result;
    }

    // }}}

    /**
     * Return current product Id
     *
     * @return integer
     */
    protected function getProductId()
    {
        return \XLite\Core\Request::getInstance()->product_id;
    }

    /**
     * Check - is service controller or not
     *
     * @return boolean
     */
    protected function isServiceController()
    {
        return false;
    }
}
