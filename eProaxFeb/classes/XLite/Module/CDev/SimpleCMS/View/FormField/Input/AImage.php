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
 * Image
 *
 */
abstract class AImage extends \XLite\View\FormField\Input\AInput
{
    /**
     * Return field type
     *
     * @return string
     */
    public function getFieldType()
    {
        return 'file';
    }

    /**
     * Return CSS files list
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = $this->getDir() . '/form_field/style.css';

        return $list;
    }

    /**
     * Return the image URL value
     *
     * @return string
     */
    abstract protected function getImage();

    /**
     * Return the image URL value
     *
     * @return string
     */
    abstract protected function getReturnToDefaultLabel();

    /**
     * Return the inner name for widget.
     * It is used in model widget identification of the "useDefaultImage" value
     *
     * @return string
     */
    abstract protected function getImageName();

    /**
     * Get common attributes
     *
     * @return array
     */
    protected function getCommonAttributes()
    {
        $list = parent::getCommonAttributes();
        // We encorage to upload the image files only. HTML5 support
        $list['accept'] = 'image/*';

        return $list;
    }

    /**
     * Return field template
     *
     * @return string
     */
    protected function getFieldTemplate()
    {
        return '/form_field/image.tpl';
    }

    /**
     * getDir
     *
     * @return string
     */
    protected function getDir()
    {
        return 'modules/CDev/SimpleCMS';
    }
}
