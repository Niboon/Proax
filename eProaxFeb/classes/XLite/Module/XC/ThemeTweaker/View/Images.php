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

namespace XLite\Module\XC\ThemeTweaker\View;

/**
 * Images widget
 */
class Images extends \XLite\View\AView
{
    protected $images;

    /**
     * Return list of allowed targets
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        return array_merge(parent::getAllowedTargets(), array('custom_css_images'));
    }

   /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'items_list/model/table/style.css';
        $list[] = 'items_list/model/style.css';
        $list[] = 'modules/XC/ThemeTweaker/images/style.css';

        return $list;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/XC/ThemeTweaker/images/body.tpl';
    }


    /**
     * Get iterator for template files
     *
     * @return \Includes\Utils\FileFilter
     */
    protected function getImagesIterator()
    {
        return new \Includes\Utils\FileFilter(
            $this->getImagesDir()
        );
    }

    /**
     * Get images 
     *
     * @return array
     */
    protected function getImages()
    {
        if (!isset($this->images)) {
            $this->images = array();
            try {
                foreach ($this->getImagesIterator()->getIterator() as $file) {
                    if ($file->isFile()) {
                        $this->images[] = \Includes\Utils\FileManager::getRelativePath($file->getPathname(), $this->getImagesDir());
                    }
                }
            } catch (\Exception $e) {
            }
        }

        return $this->images;
    }

    /**
     * Get image dir
     *
     * @param string $image Image
     *
     * @return string
     */
    protected function getImageUrl($image)
    {
        return \XLite\Core\Layout::getInstance()->getResourceWebPath(
            'theme/images/' . $image,
            \XLite\Core\Layout::WEB_PATH_OUTPUT_URL,
            'custom'
        );
    }

    /**
     * Get images dir
     *
     * @return string
     */
    protected function getImagesDir()
    {
        return \XLite\Module\XC\ThemeTweaker\Main::getThemeDir() . 'images' . LC_DS;
    }
}
