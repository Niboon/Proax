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

namespace XLite\Module\CDev\Paypal\Model\Payment;

/**
 * Payment method model
 */
class Method extends \XLite\Model\Payment\Method implements \XLite\Base\IDecorator
{
    /**
     * Get payment processor class
     *
     * @return string
     */
    public function getClass()
    {
        $class = parent::getClass();

        if (\XLite\Module\CDev\Paypal\Main::PP_METHOD_EC == $this->getServiceName()) {
            $className = '\XLite\\' . $class;
            /** @var \XLite\Model\Payment\Base\Processor $processor */
            $processor = \XLite\Core\Operator::isClassExists($className) ? $className::getInstance() : null;

            if ($this->isForceMerchantAPI($processor)) {
                $class = 'Module\CDev\Paypal\Model\Payment\Processor\ExpressCheckoutMerchantAPI';
            }
        }

        if (\XLite\Module\CDev\Paypal\Main::PP_METHOD_PC == $this->getServiceName()) {
            $className = '\XLite\\' . $class;
            /** @var \XLite\Model\Payment\Base\Processor $processor */
            $processor = \XLite\Core\Operator::isClassExists($className) ? $className::getInstance() : null;

            if ($this->getExpressCheckoutPaymentMethod()->isForceMerchantAPI($processor)) {
                $class = 'Module\CDev\Paypal\Model\Payment\Processor\PaypalCreditMerchantAPI';
            }
        }

        return $class;
    }

    /**
     * Get payment method setting by its name
     *
     * @param string $name Setting name
     *
     * @return string
     */
    public function getSetting($name)
    {
        if (\XLite\Module\CDev\Paypal\Main::PP_METHOD_EC == $this->getServiceName() && $this->isForcedEnabled()) {
            $parentMethod = $this->getProcessor()->getParentMethod();
            $result = $parentMethod->getSetting($name);

        } else {
            $result = parent::getSetting($name);
        }

        return $result;
    }

    /**
     * Additional check for PPS
     *
     * @return boolean
     */
    public function isEnabled()
    {
        $result = parent::isEnabled();

        if ($result && \XLite\Module\CDev\Paypal\Main::PP_METHOD_PPS == $this->getServiceName()) {
            $result = !$this->getProcessor()->isPaypalAdvancedEnabled();
        }

        if (\XLite\Module\CDev\Paypal\Main::PP_METHOD_PC == $this->getServiceName()) {
            $result = \XLite\Module\CDev\Paypal\Main::isExpressCheckoutEnabled() && $this->getSetting('enabled');
        }

        return $result;
    }

    /**
     * Set 'added' property
     *
     * @param boolean $added Property value
     *
     * @return \XLite\Model\Payment\Method
     */
    public function setAdded($added)
    {
        $result = parent::setAdded($added);

        if (\XLite\Module\CDev\Paypal\Main::PP_METHOD_EC == $this->getServiceName()) {
            if (!$added) {
                \XLite\Core\Database::getRepo('XLite\Model\Config')->createOption(
                    array(
                        'category' => 'CDev\Paypal',
                        'name'     => 'show_admin_welcome',
                        'value'    => 'N',
                    )
                );
            }
        }

        return $result;
    }

    /**
     * Get Express Checkout payment method
     *
     * @return \XLite\Model\Payment\Method
     */
    protected function getExpressCheckoutPaymentMethod()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
            ->findOneBy(array('service_name' => \XLite\Module\CDev\Paypal\Main::PP_METHOD_EC));
    }

    /**
     * Is forced Merchant API for Paypal Express
     * https://developer.paypal.com/docs/classic/api/#merchant
     *
     * @param \XLite\Model\Payment\Base\Processor $processor Payment processor
     *
     * @return boolean
     */
    protected function isForceMerchantAPI($processor)
    {
        $parentMethod = $processor->getParentMethod();

        return !$processor->isForcedEnabled($this)
            && (
                'email' == parent::getSetting('api_type')
                || 'paypal' == parent::getSetting('api_solution')
                || ($parentMethod && !$processor->isConfigured($parentMethod))
            );
    }
}
