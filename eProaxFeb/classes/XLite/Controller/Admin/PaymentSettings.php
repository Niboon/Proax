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
 * Payment methods
 */
class PaymentSettings extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('Payment settings');
    }

    /**
     * Enable method
     *
     * @return void
     */
    protected function doActionEnable()
    {
        $method = \XLite\Core\Request::getInstance()->id
            ? \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')->find(\XLite\Core\Request::getInstance()->id)
            : null;

        if ($method && $method->canEnable()) {
            $method->setEnabled(true);

            \XLite\Core\TopMessage::addInfo('Payment method has been enabled successfully');
            \XLite\Core\Database::getEM()->flush();
        }

        $this->dispatchAJAXEnable();

        $this->setReturnURL(\XLite\Core\Converter::buildURL('payment_settings'));
    }

    /**
     * Send specific headers and print AJAX data as JSON string
     *
     * @param array $data
     *
     * @return void
     */
    protected function printAJAX($data)
    {
        // Move top messages into headers since we print data and die()
        $this->translateTopMessagesToHTTPHeaders();

        $content = json_encode($data);

        header('Content-Type: application/json; charset=UTF-8');
        header('Content-Length: ' . strlen($content));
        header('ETag: ' . md5($content));

        print ($content);
        die;
    }

    /**
     * For AJAX request sends the changed HREF for the disable link
     *
     * @return void
     */
    protected function dispatchAJAXEnable()
    {
        if ($this->isAJAX()) {
            $this->printAJAX(array(
                'href'      => $this->buildURL('payment_settings', 'disable', array('id' => \XLite\Core\Request::getInstance()->id)),
                'removeCSS' => 'disabled',
                'addCSS'    => 'enabled',
            ));
        }
    }

    /**
     * Disable method
     *
     * @return void
     */
    protected function doActionDisable()
    {
        $method = \XLite\Core\Request::getInstance()->id
            ? \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')->find(\XLite\Core\Request::getInstance()->id)
            : null;

        if ($method && !$method->isForcedEnabled()) {
            $method->setEnabled(false);

            \XLite\Core\TopMessage::addInfo('Payment method has been disabled successfully');
            \XLite\Core\Database::getEM()->flush();
        }

        $this->dispatchAJAXDisable();

        $this->setReturnURL(\XLite\Core\Converter::buildURL('payment_settings'));
    }

    /**
     * For AJAX request sends the changed HREF for the enable link
     *
     * @return void
     */
    protected function dispatchAJAXDisable()
    {
        if ($this->isAJAX()) {
            $this->printAJAX(array(
                'href'      => $this->buildURL('payment_settings', 'enable', array('id' => \XLite\Core\Request::getInstance()->id)),
                'removeCSS' => 'enabled',
                'addCSS'    => 'disabled',
            ));
        }
    }

    /**
     * Remove method
     *
     * @return void
     */
    protected function doActionRemove()
    {
        $method = \XLite\Core\Request::getInstance()->id
            ? \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')->find(\XLite\Core\Request::getInstance()->id)
            : null;

        if ($method && !$method->isForcedEnabled()) {
            if (get_class($method->getProcessor()) == 'XLite\Model\Payment\Processor\Offline') {
                \XLite\Core\Database::getEM()->remove($method);

            } else {
                $method->setAdded(false);
            }

            \XLite\Core\TopMessage::addInfo('Payment method has been removed successfully');
            \XLite\Core\Database::getEM()->flush();
        }

        $this->setReturnURL(\XLite\Core\Converter::buildURL('payment_settings'));
    }

    /**
     * Add method
     *
     * @return void
     */
    protected function doActionAdd()
    {
        $id = \XLite\Core\Request::getInstance()->id;

        $method = $id
            ? \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')->find($id)
            : null;

        if ($method) {
            $method->setAdded(true);
            \XLite\Core\TopMessage::addInfo('Payment method has been added successfully');
            \XLite\Core\Database::getEM()->flush();

            $this->setReturnURL($method->getConfigurationURL(true));
        }
    }

    /**
     * Add offline method
     *
     * @return void
     */
    protected function doActionAddOfflineMethod()
    {
        $name = strval(\XLite\Core\Request::getInstance()->name);
        $instruction = strval(\XLite\Core\Request::getInstance()->instruction);
        $description = strval(\XLite\Core\Request::getInstance()->description);

        if ($name) {
            $method = new \XLite\Model\Payment\Method;
            $method->setName($name);
            $method->setTitle($name);
            $method->setDescription($description);
            $method->setClass('Model\\Payment\\Processor\\Offline');
            $method->setAdded(true);
            $method->setModuleEnabled(true);
            $method->setType(\XLite\Model\Payment\Method::TYPE_OFFLINE);
            $method->setServiceName(microtime(true));
            if ($instruction) {
                $method->setInstruction($instruction);
            }
            \XLite\Core\Database::getEM()->persist($method);

            \XLite\Core\Database::getEM()->flush();

            $method->setServiceName($method->getmethodId());
            \XLite\Core\Database::getEM()->flush();

            \XLite\Core\TopMessage::addInfo('Payment method has been added successfully');
        }

        $this->setReturnURL(\XLite\Core\Converter::buildURL('payment_settings'));
        $this->setHardRedirect(true);
    }
}
