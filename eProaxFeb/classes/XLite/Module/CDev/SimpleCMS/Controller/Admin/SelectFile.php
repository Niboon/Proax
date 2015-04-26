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

namespace XLite\Module\CDev\SimpleCMS\Controller\Admin;

/**
 * Select File controller
 *
 */
class SelectFile extends \XLite\Controller\Admin\SelectFile implements \XLite\Base\IDecorator
{
    /**
     * Return parameters array for "Page" target
     *
     * @return string
     */
    protected function getParamsObjectPage()
    {
        return array(
            'id' => \XLite\Core\Request::getInstance()->objectId,
        );
    }

    // {{{ Page image

    /**
     * Common handler for page images.
     *
     * @param string $methodToLoad Method to use for getting images
     * @param array  $paramsToLoad Parameters to use in image getter method
     *
     * @return void
     */
    protected function doActionSelectPageImage($methodToLoad, array $paramsToLoad)
    {
        $pageId = intval(\XLite\Core\Request::getInstance()->objectId);

        $page = \XLite\Core\Database::getRepo('XLite\Module\CDev\SimpleCMS\Model\Page')->find($pageId);

        $image = $page->getImage();

        if (!$image) {
            $image = new \XLite\Module\CDev\SimpleCMS\Model\Image\Page\Image();
        }

        if (call_user_func_array(array($image, $methodToLoad), $paramsToLoad)) {

            $image->setPage($page);

            $page->setImage($image);

            \XLite\Core\Database::getEM()->persist($image);
            \XLite\Core\Database::getEM()->flush();

            \XLite\Core\TopMessage::addInfo(
                'The image has been updated'
            );

        } else {
            \XLite\Core\TopMessage::addError(
                'Failed to update page image'
            );
        }
    }

    /**
     * "Upload" handler for page images.
     *
     * @return void
     */
    protected function doActionSelectUploadPageImage()
    {
        $this->doActionSelectPageImage('loadFromRequest', array('uploaded_file'));
    }

    /**
     * "URL" handler for page images.
     *
     * @return void
     */
    protected function doActionSelectUrlPageImage()
    {
        $this->doActionSelectPageImage(
            'loadFromURL',
            array(
                \XLite\Core\Request::getInstance()->url,
                (bool) \XLite\Core\Request::getInstance()->url_copy_to_local
            )
        );
    }

    /**
     * "Local file" handler for page images.
     *
     * @return void
     */
    protected function doActionSelectLocalPageImage()
    {
        $file = \XLite\View\BrowseServer::getNormalizedPath(\XLite\Core\Request::getInstance()->local_server_file);

        $this->doActionSelectPageImage(
            'loadFromLocalFile',
            array($file)
        );
    }

    // }}}

}
