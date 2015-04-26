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

namespace XLite\Module\CDev\FileAttachments\Model\Product\Attachment;

/**
 * Product attchament's storage 
 *
 * @Entity
 * @Table  (name="product_attachment_storages")
 */
class Storage extends \XLite\Model\Base\Storage
{
    // {{{ Associations

    /**
     * Relation to a attachment
     *
     * @var \XLite\Module\CDev\FileAttachments\Model\Product\Attachment
     *
     * @OneToOne  (targetEntity="XLite\Module\CDev\FileAttachments\Model\Product\Attachment", inversedBy="storage")
     * @JoinColumn (name="attachment_id", referencedColumnName="id")
     */
    protected $attachment;

    // }}}

    // {{{ Service operations

    /**
     * Get valid file system storage root
     *
     * @return string
     */
    protected function getValidFileSystemRoot()
    {
        $path = parent::getValidFileSystemRoot();

        if (!file_exists($path . LC_DS . '.htaccess')) {
            file_put_contents(
                $path . LC_DS . '.htaccess',
                'Options -Indexes' . PHP_EOL
                . 'Allow from all' . PHP_EOL
            );
        }

        return $path;
    }

    /**
     * Assemble path for save into DB
     *
     * @param string $path Path
     *
     * @return string
     */
    protected function assembleSavePath($path)
    {
        return $this->getAttachment()->getProduct()->getProductId() . LC_DS . parent::assembleSavePath($path);
    }

    /**
     * Get valid file system storage root
     *
     * @return string
     */
    protected function getStoreFileSystemRoot()
    {
        $path = parent::getStoreFileSystemRoot() . $this->getAttachment()->getProduct()->getProductId() . LC_DS;
        \Includes\Utils\FileManager::mkdirRecursive($path);

        return $path;
    }

    /**
     * Clone for attachment
     *
     * @param \XLite\Module\CDev\FileAttachments\Model\Product\Attachment $attachment Attachment
     *
     * @return \XLite\Model\AEntity
     */
    public function cloneEntityForAttachment(\XLite\Module\CDev\FileAttachments\Model\Product\Attachment $attachment)
    {
        $newStorage = parent::cloneEntity();

        $attachment->setStorage($newStorage);
        $newStorage->setAttachment($attachment);

        $newStorage->setPath('');
        $newStorage->loadFromURL($this->getURL(), true);

        return $newStorage;
    }

    // }}}
}

