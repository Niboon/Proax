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

namespace XLite\Controller\Admin;

/**
 * File upload controller
 */
class Files extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Check if current page is accessible
     *
     * @return boolean
     */
    public function checkAccess()
    {
        return parent::checkAccess() && $this->isAJAX();
    }

    /**
     * Check ACL permissions
     *
     * @return boolean
     */
    public function checkACL()
    {
        return true;
    }

    /**
     * Check file
     *
     * @param mixed $file File
     *
     * @return void
     */
    protected function checkFile($file)
    {
        if ($file
            && \XLite\Core\Request::getInstance()->is_image
            && !$file->isImage()
        ) {
            $file->removeFile();
            $this->getContent(null, static::t('File is not an image'));
        }
    }

    /**
     * Upload from file action
     *
     * @return void
     */
    protected function doActionUploadFromFile()
    {
        $file = new \XLite\Model\TemporaryFile();
        $message = '';
        if ($file->loadFromRequest('file')) {
            $this->checkFile($file);
            \XLite\Core\Database::getEM()->persist($file);
            \XLite\Core\Database::getEM()->flush();

        } else {
            $message = static::t('File is not uploaded');
        }

        $this->getContent($file, $message);
    }

    /**
     * Upload from URL action
     *
     * @return void
     */
    protected function doActionUploadFromURL()
    {
        $file = new \XLite\Model\TemporaryFile();
        $message = '';
        if ($file->loadFromURL(\XLite\Core\Request::getInstance()->url, \XLite\Core\Request::getInstance()->copy)) {
            $this->checkFile($file);
            \XLite\Core\Database::getEM()->persist($file);
            \XLite\Core\Database::getEM()->flush();

        } else {
            $message = static::t('File is not uploaded');
        }

        $this->getContent($file, $message);
    }

    /**
     * Return content
     *
     * @param mixed  $file    File
     * @param string $message Message OPTIONAL
     *
     * @return void
     */
    protected function getContent($file, $message = '')
    {
        $viewer = new \XLite\View\FileUploader(
            array(
                \XLite\View\FileUploader::PARAM_NAME         => \XLite\Core\Request::getInstance()->name,
                \XLite\View\FileUploader::PARAM_MULTIPLE     => \XLite\Core\Request::getInstance()->multiple,
                \XLite\View\FileUploader::PARAM_OBJECT       => $file,
                \XLite\View\FileUploader::PARAM_OBJECT_ID    => \XLite\Core\Request::getInstance()->object_id,
                \XLite\View\FileUploader::PARAM_MESSAGE      => $message,
                \XLite\View\FileUploader::PARAM_IS_TEMPORARY => true,
                \XLite\View\FileUploader::PARAM_MAX_WIDTH    => \XLite\Core\Request::getInstance()->max_width,
                \XLite\View\FileUploader::PARAM_MAX_HEIGHT   => \XLite\Core\Request::getInstance()->max_height,
            )
        );

        $this->printAJAXOutput($viewer);
        exit(0);
    }

    /**
     * Set if the form id is needed to make an actions
     * Form class uses this method to check if the form id should be added
     *
     * @return boolean
     */
    public static function needFormId()
    {
        return false;
    }
}
