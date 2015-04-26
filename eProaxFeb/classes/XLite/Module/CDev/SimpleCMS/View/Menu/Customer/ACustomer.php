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

namespace XLite\Module\CDev\SimpleCMS\View\Menu\Customer;

/**
 * Footer menu
 */
abstract class ACustomer extends \XLite\View\Menu\Customer\ACustomer implements \XLite\Base\IDecorator
{
    /**
     * Check - specified item is active or not
     *
     * @param array $item Menu item
     *
     * @return boolean
     */
    protected function isActiveItem(array $item)
    {
        $result = parent::isActiveItem($item);

        if (false === $item['controller']) {

            $result = (\XLite::getInstance()->getShopURL($item['url']) === \XLite\Core\URLManager::getCurrentURL())
                ?: $result;

        } else {

            if (!is_array($item['controller'])) {
                $item['controller'] = array($item['controller']);
            }

            $controller = \XLite::getController();

            foreach ($item['controller'] as $controllerName) {
                if ($controller instanceof $controllerName) {
                    $result = true;

                    break;
                }
            }
        }

        return $result;
    }
}
