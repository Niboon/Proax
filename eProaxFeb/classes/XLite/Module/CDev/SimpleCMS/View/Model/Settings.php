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

namespace XLite\Module\CDev\SimpleCMS\View\Model;

/**
 * Settings dialog model widget
 */
abstract class Settings extends \XLite\View\Model\Settings implements \XLite\Base\IDecorator
{
    /**
     * Logo & Favicon fields
     *
     * @var array
     */
    static protected $logoFaviconFields = array('logo', 'favicon');

    /**
     * Logo & Favicon validation flag
     *
     * @var boolean
     */
    protected $logoFaviconValidation = true;

    /**
     * Defines the directory where images (logo, favicon) will be stored
     *
     * @return string
     */
    protected static function getLogoFaviconDir()
    {
        return LC_DIR;
    }

    /**
     * Check for the form errors
     *
     * @return boolean
     */
    public function isValid()
    {
        return parent::isValid() && $this->logoFaviconValidation;
    }

    /**
     * Populate model object properties by the passed data
     *
     * @param array $data Data to set
     *
     * @return void
     */
    protected function setModelProperties(array $data)
    {
        $options = $this->getEditableOptions();

        if ('logo_favicon' == $this->getTarget()) {
            foreach ($options as $k => $v) {
                if (in_array($v->name, static::$logoFaviconFields)) {
                    $data[$v->name] = $this->prepareImageData($v->value, $v->name);
                }
            }
        }

        parent::setModelProperties($data);
    }

    /**
     * Get schema fields
     *
     * @return array
     */
    public function getSchemaFields()
    {
        return $this->prepareOptions(parent::getSchemaFields());
    }

    /**
     * Get editable options
     *
     * @return array
     */
    protected function getEditableOptions()
    {
        return $this->prepareOptions(parent::getEditableOptions());
    }

    /**
     * Prepare options
     *
     * @param array $options Options
     *
     * @return array
     */
    protected function prepareOptions(array $options)
    {
        if (
            'logo_favicon' == $this->getTarget()
            || (
                'module' == $this->getTarget()
                && $this->getModule()
                && 'CDev\SimpleCMS' == $this->getModule()->getActualName()
            )
        ) {
            foreach ($options as $k => $v) {
                $id = is_object($v) && property_exists($v, 'name') ? $v->name : $k;
                if (
                    (
                        'logo_favicon' == $this->getTarget()
                        && !in_array($id, static::$logoFaviconFields)
                    )
                    || (
                        'logo_favicon' != $this->getTarget()
                        && in_array($id, static::$logoFaviconFields)
                    )
                ) {
                    unset($options[$k]);
                }
            }
        }

        return $options;
    }

    /**
     * Additional preparations for images.
     * Upload them into specific directory
     *
     * @param string $optionValue Option value
     * @param string $imageType   Image type
     *
     * @return string
     */
    protected function prepareImageData($optionValue, $imageType)
    {
        $dir = static::getLogoFaviconDir();
        if (
            $_FILES
            && $_FILES[$imageType]
            && $_FILES[$imageType]['name']
        ) {
            $path = null;

            if ($this->isImage($_FILES[$imageType]['tmp_name'])) {
                \Includes\Utils\FileManager::deleteFile(
                    $dir . LC_DS . ('favicon' === $imageType ? static::FAVICON : $_FILES[$imageType]['name'])
                );
                $path = \Includes\Utils\FileManager::moveUploadedFile(
                    $imageType,
                    $dir,
                    'favicon' === $imageType ? static::FAVICON : null
                );

                if ($path) {
                    if ($optionValue) {
                        \Includes\Utils\FileManager::deleteFile($dir . $optionValue);
                    }
                    $optionValue = basename($path);
                }
            }

            if (!isset($path)) {
                $this->logoFaviconValidation = false;
                \XLite\Core\TopMessage::addError(
                    'The "{{file}}" file was not uploaded',
                    array('file' => $_FILES[$imageType]['name'])
                );
            }
        } elseif (\XLite\Core\Request::getInstance()->useDefaultImage[$imageType]) {
            if ($optionValue) {
                \Includes\Utils\FileManager::deleteFile($dir . $optionValue);
            }
            $optionValue = '';
        }

        return $optionValue;
    }

    /**
     * Check if file is valid image
     *
     * @param string $path File path
     *
     * @return boolean
     */
    protected function isImage($path)
    {
        $data = @getimagesize($path);

        return is_array($data) && $data[0];
    }
}

