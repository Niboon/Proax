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

namespace XLite\Module\CDev\GoogleAnalytics\View;

/**
 * Tabber link widget 
 *
 * @ListChild (list="tabs.content", zone="admin")
 */
class TabberLink extends \XLite\View\AView
{
    /**
     * Return list of allowed targets
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        return array_merge(parent::getAllowedTargets(), array('orders_stats'));
    }

    /**
     * Add widget specific CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'modules/CDev/GoogleAnalytics/tabs/style.css';

        return $list;
    }
    
    /**
     * Check if the Google Analitics module is configured
     * 
     * @return boolean
     */
    protected function isConfigured()
    {
        return (bool)\XLite\Core\Config::getInstance()->CDev->GoogleAnalytics->ga_account;
    }

    /**
     * Defines the module link to configure
     * 
     * @return string
     */
    protected function getModuleLink()
    {
        return $this->buildURL(
            'module', 
            '',
            array(
                'moduleId' => \XLite\Core\Database::getRepo('XLite\Model\Module')
                    ->findOneBy(array('name' => 'GoogleAnalytics', 'fromMarketplace' => false))->getModuleId(),
            )
        );
    }
    
    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/CDev/GoogleAnalytics/tabs/link.tpl';
    }

}
