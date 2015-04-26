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

namespace XLite\Core;

/**
 * DB-based configuration registry
 */
class Config extends \XLite\Base\Singleton
{
    /**
     * Config (cache)
     *
     * @var \XLite\Core\CommonCell
     */
    protected $config;


    /**
     * Method to access a singleton
     *
     * @return \XLite\Core\CommonCell
     */
    public static function getInstance()
    {
        return parent::getInstance()->readConfig();
    }

    /**
     * Reset state
     *
     * @return void
     */
    public static function updateInstance()
    {
        parent::getInstance()->readConfig(true);
    }


    /**
     * Read config options
     *
     * @param mixed $force Force OPTIONAL
     *
     * @return \XLite\Core\CommonCell
     */
    public function readConfig($force = false)
    {
        if (!isset($this->config) || $force) {
            $config = \XLite\Core\Database::getRepo('XLite\Model\Config')->getAllOptions($force);

            if ($config && $config->General && $config->General->default_admin_language) {
                parent::setDefaultLanguage($config->General->default_admin_language);
            }

            // Add human readable store country and state names for Company options
            if (isset($config->Company)) {
                $config->Company->locationCountry = \XLite\Core\Database::getRepo('XLite\Model\Country')
                    ->find($config->Company->location_country);
                $hasStates = $config->Company->locationCountry && $config->Company->locationCountry->hasStates();

                $config->Company->locationState = \XLite\Core\Database::getRepo('XLite\Model\State')
                    ->findById(
                        $hasStates ? $config->Company->location_state : -1, $config->Company->location_custom_state
                    );
            }

            // Add human readable default country name for General options
            if (isset($config->General)) {
                $config->General->defaultCountry = \XLite\Core\Database::getRepo('XLite\Model\Country')
                    ->find($config->Shipping->anonymous_country);
            }

            $this->config = $config;
        }

        return $this->config;
    }

    /**
     * Update and re-read options
     *
     * @return void
     */
    public function update()
    {
        parent::update();

        $this->readConfig(true);
    }
}
