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
 * @copyright Copyright (c) 2011-2014 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Module\XC\ThemeTweaker\View\Tabs;

/**
 * Tabs related to theme tweaker
 */
class CssJs extends \XLite\View\Tabs\CssJs implements \XLite\Base\IDecorator
{
    /**
     * Define and set handler attributes; initialize handler
     *
     * @param array $params Handler params OPTIONAL
     *
     * @return void
     */
    public function __construct(array $params = array())
    {
        if (!isset($this->tabs['custom_css'])) {
            $this->tabs = array_merge(
                array(
                    'custom_css' => array(
                        'weight'    => 200,
                        'title'     => 'Custom CSS',
                        'template'  => 'modules/XC/ThemeTweaker/custom_css.tpl',
                    ),
                    'custom_js' => array(
                        'weight'    => 300,
                        'title'     => 'Custom JavaScript',
                        'template'  => 'modules/XC/ThemeTweaker/custom_js.tpl',
                    ),
                    'custom_css_images' => array(
                        'weight'    => 400,
                        'title'     => 'Custom images',
                        'template'  => 'modules/XC/ThemeTweaker/custom_css_images.tpl',
                    ),
                ),
                $this->tabs
            );
        }

        parent::__construct();
    }
}
