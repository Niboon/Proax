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
 * Version
 */
class Version extends \XLite\Controller\Customer\ACustomer
{
    /**
     * Special code
     */
    const VER_CODE = '06c2792b0b1db32aac9cb5eb69eabc04';

    /**
     * Handles the request.
     *
     * @return void
     */
    public function handleRequest()
    {
        if (!$this->isValidRequest()) {
            $this->display404();
            $this->doRedirect();
        }

        parent::handleRequest();
    }

    /**
     * Process request
     *
     * @return void
     */
    public function processRequest()
    {
        header('Content-Type: text/html; charset=utf-8');

        \Includes\Utils\Operator::flush($this->getInfoMessage());
    }

    /**
     * Method to compose different messages
     *
     * @return string
     */
    protected function getInfoMessage()
    {
        $result = '';

        $result .= $this->getVersionMessage() . LC_EOL;
        $result .= $this->getLicenseMessage() . LC_EOL;
        $result .= $this->getInstallationMessage() . LC_EOL;
        $result .= $this->getModulesMessage() . LC_EOL;

        return $result;
    }

    /**
     * Return info about current LC version
     *
     * @return string
     */
    protected function getVersionMessage()
    {
        return static::t('Version') . ': ' . \XLite::getInstance()->getVersion() . LC_EOL;
    }

    /**
     * License info
     *
     * @return string
     */
    protected function getLicenseMessage()
    {
        $key = \XLite::getXCNLicense();

        if ($key) {
            $keyData = $key->getKeyData();
        }

        return $key ? ('License: ' . $keyData['editionName']) : static::t('License: trial version');
    }

    /**
     * Installation info
     *
     * @return string
     */
    protected function getInstallationMessage()
    {
        return static::t('Installation date') . ': ' . date('r', \XLite\Core\Config::getInstance()->Version->timestamp);
    }

    /**
     * Return info about installed modules
     *
     * @return string
     */
    protected function getModulesMessage()
    {
        $result = array();

        foreach (\Includes\Utils\ModulesManager::getActiveModules() as $data) {
            $result[] = array(
                $data['authorName'],
                $data['moduleName'],
                $data['majorVersion'],
                $data['minorVersion'],
            );
        }

        usort(
            $result,
            function ($a, $b) {
                return strcasecmp($a[1], $b[1]);
            }
        );

        $list = array_map(
            function ($a) {
                return vsprintf('(%s): %s (v.%s.%s)', $a);
            },
            $result
        );

        return 'Installed modules:' . LC_EOL . ($list ? implode(LC_EOL, $list) : static::t('None'));
    }

    /**
     * Check if request is valid
     *
     * @return boolean
     */
    protected function isValidRequest()
    {
        $code = \XLite\Core\Request::getInstance()->scode;

        return (self::VER_CODE == md5($code));
    }

    /**
     * Stub for the CMS connectors
     *
     * @return boolean
     */
    protected function checkStorefrontAccessibility()
    {
        return true;
    }

}
