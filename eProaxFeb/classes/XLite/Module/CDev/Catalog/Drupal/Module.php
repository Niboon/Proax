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

namespace XLite\Module\CDev\Catalog\Drupal;

/**
 * Handler
 *
 * @LC_Dependencies ("CDev\DrupalConnector")
 */
class Module extends \XLite\Module\CDev\DrupalConnector\Drupal\Module implements \XLite\Base\IDecorator
{
    /**
     * Catalog portal to remove 
     * 
     * @var array
     */
    protected $catalogToRemove = array(
        '\XLite\Controller\Customer\OrderList',
        '\XLite\Controller\Customer\Order',
        '\XLite\Controller\Customer\Invoice',
    );

    /**
     * Register a portal
     *
     * @param string  $url        Drupal URL
     * @param string  $controller Controller class name
     * @param string  $title      Portal title OPTIONAL
     * @param integer $type       Node type OPTIONAL
     *
     * @return void
     */
    protected function registerPortal($url, $controller, $title = '', $type = MENU_LOCAL_TASK)
    {
        if (
            !\XLite\Core\Config::getInstance()->CDev->Catalog->disable_checkout
            || !in_array($controller, $this->catalogToRemove)
        ) {
            parent::registerPortal($url, $controller, $title, $type);
        }
    }

}
