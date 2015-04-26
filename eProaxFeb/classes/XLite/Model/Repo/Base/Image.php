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

namespace XLite\Model\Repo\Base;

/**
 * Image abstract repository
 */
abstract class Image extends \XLite\Model\Repo\Base\Storage
{
    /**
     * Get allowed file system root list
     *
     * @return array
     */
    public function getAllowedFileSystemRoots()
    {
        $list = parent::getAllowedFileSystemRoots();

        $list[] = LC_DIR_IMAGES;

        return $list;
    }

    /**
     * Get file system images storage root path
     *
     * @return string
     */
    public function getFileSystemRoot()
    {
        return LC_DIR_IMAGES . $this->getStorageName() . LC_DS;
    }

    /**
     * Get web images storage root path
     *
     * @return string
     */
    public function getWebRoot()
    {
        return LC_IMAGES_URL . '/' . $this->getStorageName() . '/';
    }

    /**
     * Get file system images cache storage root path
     *
     * @param string $sizeName Image size cell name
     *
     * @return string
     */
    public function getFileSystemCacheRoot($sizeName)
    {
        return LC_DIR_CACHE_IMAGES . $this->getStorageName() . LC_DS . $sizeName . LC_DS;
    }

    /**
     * Get web images cache storage root path
     *
     * @param string $sizeName Image size cell name
     *
     * @return string
     */
    public function getWebCacheRoot($sizeName)
    {
        return LC_IMAGES_CACHE_URL . '/' . $this->getStorageName() . '/' . $sizeName;
    }

    /**
     * Check - check image hash in Custoemr front-end or not
     *
     * @return boolean
     */
    public function isCheckImage()
    {
        return false;
    }

    // {{{ Resize

    /**
     * Resize only marked flag
     * 
     * @var boolean
     */
    protected static $resizeOnlyMarked = false;

    /**
     * Set 'resize only marked' flag 
     * 
     * @param boolean $flag Flag
     *  
     * @return void
     */
    public static function setResizeOnlyMarkedFlag($flag)
    {
       self::$resizeOnlyMarked = $flag;
    }

    /**
     * Count items for images resize routine
     *
     * @return integer
     */
    public function countForResize()
    {
        return \XLite\Model\Repo\Base\Image::$resizeOnlyMarked
            ? $this->countResizeMarked()
            : $this->countForExport();
    }

    /**
     * Define items iterator
     *
     * @param integer $position Position OPTIONAL
     *
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function getResizeIterator($position = 0)
    {
        return \XLite\Model\Repo\Base\Image::$resizeOnlyMarked
            ? $this->getResizeMarkedIterator($position)
            : $this->getExportIterator($position);
    }

    /**
     * Count marked for resize
     * 
     * @return integer
     */
    public function countResizeMarked()
    {
        $qb = $this->defineCountForExportQuery();
        $qb->andWhere($qb->getMainAlias() . '.needProcess = :needProcess')
            ->setParameter('needProcess', true);

        return intval($qb->getSingleScalarResult());
    }

    /**
     * Get resize marked iterator 
     * 
     * @param integer $position Position OPTIONAL
     *  
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function getResizeMarkedIterator($position = 0)
    {
        $qb = $this->defineExportIteratorQueryBuilder($position);
        $qb->andWhere($qb->getMainAlias() . '.needProcess = :needProcess')
            ->setParameter('needProcess', true);

        return $qb->iterate();
    }

    /**
     * Mark as processed 
     * 
     * @return void
     */
    public function markAsProcessed()
    {
        $this->createPureQueryBuilder('i')
            ->update($this->_entityName, 'i')
            ->set('i.needProcess', '1')
            ->execute();
    }

    /**
     * Unmark as processed
     *
     * @return void
     */
    public function unmarkAsProcessed()
    {
        $this->getQueryBuilder()
            ->update($this->_entityName, 'i')
            ->set('i.needProcess', '0')
            ->execute();
    }

    // }}}

}
