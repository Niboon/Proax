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

namespace XLite\Module\CDev\SimpleCMS\View\FormField\Input;

/**
 * Logo
 */
class Logo extends \XLite\Module\CDev\SimpleCMS\View\FormField\Input\AImage
{
    /**
     * Return the image URL value
     *
     * @return string
     */
    protected function getImage()
    {
        return $this->getLogo();
    }

    /**
     * Return the default label
     *
     * @return string
     */
    protected function getReturnToDefaultLabel()
    {
        return 'Return to default logo';
    }

    /**
     * Return the inner name for widget.
     * It is used in model widget identification of the "useDefaultImage" value
     *
     * @return string
     */
    protected function getImageName()
    {
        return 'logo';
    }

}
