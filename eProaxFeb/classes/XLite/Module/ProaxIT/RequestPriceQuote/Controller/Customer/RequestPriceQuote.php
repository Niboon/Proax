<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\RequestPriceQuote\Controller\Customer;

/**
 * Request price quote controller
 */
class RequestPriceQuote extends \XLite\Controller\Customer\ACustomer
{
    /**
     * Fields
     *
     * @var   array
     */
    protected $requiredFields = array(
        'name'    => 'Your name',
        'email'   => 'Your e-mail',
        'sku' => 'Product SKU'
    );

    /**
     * Check if current page is accessible
     *
     * @return boolean
     */
    public function checkAccess()
    {
        return parent::checkAccess()
            && \XLite\Core\Config::getInstance()->ProaxIT->RequestPriceQuote->enable_form;
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
        return 'Request Price Quote';
    }

    /**
     * Return value of data
     *
     * @param string $field Field
     *
     * @return string
     */
    public function getValue($field)
    {
        $data = \XLite\Core\Session::getInstance()->request_price_quote;
        $value = $data && isset($data[$field]) ? $data[$field] : '';

        if (!$value) {
            $auth = \XLite\Core\Auth::getInstance();
            switch ($field) {
                case 'name': {
                    if (
                        $auth->isLogged()
                        && 0 < $auth->getProfile()->getAddresses()->count()
                    ) {
                        return $auth->getProfile()->getAddresses()->first()->getName();
                    } else {
                        return '';
                    }
                }
                case 'email': {
                    if ($auth->isLogged()) {
                        return $auth->getProfile()->getLogin();
                    } else {
                        return '';
                    }
                }
                case 'customerId': {
                    $customerId = 'N/A';
                    if ($auth->isLogged()) {
                        $customerId = $auth->getProfile()->getCustomerId();
                    }
                    return $customerId;
                }
                case 'sku': {
                    $popupData = \XLite\Core\Request::getInstance()->getData();
                    if ($data['sku']) {
                        $sku = $data['sku'];
                    } elseif ($popupData['sku']) {
                        $sku = $popupData['sku'];
                    } elseif ($this->getParam('sku')) {
                        $sku = $this->getParam('sku');
                    } else {
                        $sku = '[Enter Product SKU Here]';
                    }
                    return $sku;
                }
                default: {
                    return '';
                }
            }
        } else {
            return $value;
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
     * Send message
     *
     * @return void
     */
    protected function doActionSend()
    {
        $data = \XLite\Core\Request::getInstance()->getData();
        $config = \XLite\Core\Config::getInstance()->ProaxIT->RequestPriceQuote;
        $isValid = true;

        foreach ($this->requiredFields as $key => $name) {
            if (
                !isset($data[$key])
                || empty($data[$key])
            ) {
                $isValid = false;
                \XLite\Core\TopMessage::addError(
                    \XLite\Core\Translation::lbl(
                        'The X field is empty', array('name' => $name)
                    )
                );
            }
        }

        if (
            $isValid
            && false === filter_var($data['email'], FILTER_VALIDATE_EMAIL)
        ) {
            $isValid = false;
            \XLite\Core\TopMessage::addError(
                \XLite\Core\Translation::lbl(
                    'The value of the X field has an incorrect format',
                    array('name' => $this->requiredFields['email'])
                )
            );
        }

        if (
            $isValid
            && $config->recaptcha_private_key
            && $config->recaptcha_public_key
        ) {
            require_once LC_DIR_MODULES . '/ProaxIT/RequestPriceQuote/recaptcha/recaptchalib.php';

            $resp = recaptcha_check_answer(
                $config->recaptcha_private_key,
                $_SERVER['REMOTE_ADDR'],
                $data['recaptcha_challenge_field'],
                $data['recaptcha_response_field']
            );

            $isValid = $resp->is_valid;

            if (!$isValid) {
                \XLite\Core\TopMessage::addError('Please enter the correct captcha');
            }
        }

        if ($isValid) {
            $errorMessage = \XLite\Core\Mailer::getInstance()->sendRequestPriceQuoteMessage(
                $data,
                \XLite\Core\Config::getInstance()->ProaxIT->RequestPriceQuote->email ?: \XLite\Core\Config::getInstance()->Company->support_department
            );

            if ($errorMessage) {
                \XLite\Core\TopMessage::addError($errorMessage);

            } else {
                unset($data['sku']);
                unset($data['quantity']);
                unset($data['comments']);
                \XLite\Core\TopMessage::addInfo('Your request for Price Quote has been sent');
            }
        }

        \XLite\Core\Session::getInstance()->request_price_quote = $data;
    }
}
