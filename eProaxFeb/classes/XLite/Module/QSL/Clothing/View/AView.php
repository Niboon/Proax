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

namespace XLite\Module\QSL\Clothing\View;

abstract class AView extends \XLite\View\AView implements \XLite\Base\IDecorator
{

    /**
     * Check if it is a Home page
     *
     * @return boolean
     */
    protected function isWelcomePage()
    {
        return 'main' == $this->getTarget()
            ? true
            : false;
    }

    
    protected function getThemeFiles($adminZone = null)
    {
        $list = parent::getThemeFiles($adminZone);

        $list[static::RESOURCE_JS][] = 'modules/QSL/Clothing/carousel/owl.carousel.js';

        $list[static::RESOURCE_CSS][] = 'modules/QSL/Clothing/style.css';
        $list[static::RESOURCE_CSS][] = 'modules/QSL/Clothing/carousel/owl.carousel.css';
        $list[static::RESOURCE_CSS][] = 'modules/QSL/Clothing/carousel/owl.theme.css';
        $list[static::RESOURCE_CSS][] = array(
                  'file' => 'modules/QSL/Clothing/print.css',
                  'media' => 'print',
        );

        $list[static::RESOURCE_JS][] = 'modules/QSL/Clothing/template_script.js';
        
        
        $list[static::RESOURCE_CSS][] = array(
            'file' => 'modules/QSL/Clothing/print.css',
            'media' => 'print',
        );
        $list[static::RESOURCE_CSS][] = array(
            'file' => 'modules/QSL/Clothing/style.less',
            'merge' => 'bootstrap/css/bootstrap.less',
        );

        return $list;
    }
}