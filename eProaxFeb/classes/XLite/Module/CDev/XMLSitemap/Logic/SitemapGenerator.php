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

namespace XLite\Module\CDev\XMLSitemap\Logic;

/**
 * Sitemap generator 
 */
class SitemapGenerator extends \XLite\Base\Singleton
{
    const TTL = 3600;

    /**
     * File index 
     * 
     * @var integer
     */
    protected $fileIndex;

    /**
     * Empty file flag
     * 
     * @var boolean
     */
    protected $emptyFile = false;

    /**
     * Get sitemap index 
     * 
     * @return string
     */
    public function getIndex()
    {
        $string = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . PHP_EOL
            . '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
        $list = glob(LC_DIR_DATA . 'xmlsitemap.*.xml');
        if ($list) {
            foreach ($list as $path) {
                $name = basename($path);
                $loc = \XLite::getInstance()->getShopURL(
                    \XLite\Core\Converter::buildURL('sitemap', '', array('index' => substr($name, 11, -4)), \XLite::CART_SELF)
                );
                $time = filemtime($path);
                $string .= '<sitemap>'
                    . '<loc>' . htmlentities($loc, ENT_COMPAT, 'UTF-8') . '</loc>'
                    . '<lastmod>' . date('Y-m-d', $time) . 'T' . date('H:m:s', $time) . 'Z</lastmod>'
                    . '</sitemap>';
            }
        }

        return $string . '</sitemapindex>';
    }

    /**
     * Get sitemap by index
     * 
     * @param integer $index Index
     *  
     * @return string
     */
    public function getSitemap($index)
    {
        $path = LC_DIR_DATA . 'xmlsitemap.' . $index . '.xml';

        return \Includes\Utils\FileManager::isExists($path) ? file_get_contents($path) : null;
    }

    /**
     * Check - sitemap empty or not
     * 
     * @return boolean
     */
    public function isEmpty()
    {
        return 0 == $this->getIterator()->count();
    }

    /**
     * Check - sitemaps files generated or not
     * 
     * @return boolean
     */
    public function isGenerated()
    {
        $list = glob(LC_DIR_DATA . 'xmlsitemap.*.xml');

        return $list && 0 < count($list);
    }

    /**
     * Check - sitemap file is obsolete or not
     * 
     * @param integer $ttl TTL OPTIONAL
     *  
     * @return boolean
     */
    public function isObsolete($ttl = self::TTL)
    {
        $time = null;

        $list = glob(LC_DIR_DATA . 'xmlsitemap.*.xml');
        if ($list) {
            foreach ($list as $path) {
                $time = $time ? min($time, filemtime($path)) : filemtime($path);
            }
        }

        return $time && $time + $ttl < \XLite\Core\Converter::time();
    }

    // {{{ Generate sitemaps

    /**
     * Generate index file and sitemap files
     * 
     * @return void
     */
    public function generate()
    {
        $this->clear();
        $this->generateSitemaps();
    }

    /**
     * Clear files directory
     * 
     * @return void
     */
    public function clear()
    {
        $list = glob(LC_DIR_DATA . 'xmlsitemap.*.xml');
        if ($list) {
            foreach ($list as $path) {
                \Includes\Utils\FileManager::deleteFile($path);
            }
        }
    }

    /**
     * Generate sitemap files
     * 
     * @return void
     */
    protected function generateSitemaps()
    {
        $this->initializeWrite();

        foreach ($this->getIterator() as $record) {
            if (!empty($record)) {
                $this->writeRecord($this->assembleRecord($record));
            }
        }

        $this->finalizeWrite();
    }

    /**
     * Get head 
     * 
     * @return string
     */
    protected function getHead()
    {
        return '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . PHP_EOL
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
    }

    /**
     * Get footer 
     * 
     * @return string
     */
    protected function getFooter()
    {
        return '</urlset>';
    }

    /**
     * Assemble record 
     * 
     * @param array $record Record
     *  
     * @return string
     */
    protected function assembleRecord(array $record)
    {
        $record['loc'] = \XLite::getInstance()->getShopURL($this->buildLoc($record['loc']));
        $time = $record['lastmod'];
        $record['lastmod'] = date('Y-m-d', $time) . 'T' . date('H:m:s', $time) . 'Z';

        $string = '<url>';
        foreach ($record as $name => $value) {
            $string .= '<' . $name . '>' . htmlentities($value) . '</' . $name . '>';
        }

        return $string . '</url>';
    }

    /**
     * Build location URL
     * 
     * @param array $loc Locationb as array
     *  
     * @return string
     */
    protected function buildLoc(array $loc)
    {
        $target = $loc['target'];
        unset($loc['target']);

        return \XLite\Core\Converter::buildURL($target, '', $loc, \XLite::CART_SELF);
    }

    /**
     * Get iterator 
     * 
     * @return \XLite\Module\CDev\XMLSitemap\Logic\SitemapIterator
     */
    protected function getIterator()
    {
        return new \XLite\Module\CDev\XMLSitemap\Logic\SitemapIterator;
    }

    /**
     * Initialize write 
     * 
     * @return void
     */
    protected function initializeWrite()
    {
        if (!\Includes\Utils\FileManager::isExists(LC_DIR_DATA)) {
            \Includes\Utils\FileManager::mkdir(LC_DIR_DATA);
            if (!\Includes\Utils\FileManager::isExists(LC_DIR_DATA)) {
                \XLite\Logger::getInstance()->log(
                    'The directory ' . LC_DIR_DATA . ' can not be created.'
                    . ' Check the permissions to create directories.',
                    LOG_ERR
                );
            }
        }
        $this->fileIndex = null;
        $this->emptyFile = true;
    }

    /**
     * Finalize write 
     * 
     * @return void
     */
    protected function finalizeWrite()
    {
        if ($this->emptyFile) {
            if ($this->fileIndex) {
                \Includes\Utils\FileManager::deleteFile($this->getSitemapPath());
            }

        } else {
            \Includes\Utils\FileManager::write($this->getSitemapPath(), $this->getFooter(), FILE_APPEND);
        }
    }

    /**
     * Write record 
     * 
     * @param string $string String
     *  
     * @return void
     */
    protected function writeRecord($string)
    {
        if (!isset($this->fileIndex)) {
            $this->fileIndex = 1;
            \Includes\Utils\FileManager::write($this->getSitemapPath(), $this->getHead());
        }

        \Includes\Utils\FileManager::write($this->getSitemapPath(), $string, FILE_APPEND);
        $this->emptyFile = false;

        if ($this->needSwitch()) {
            \Includes\Utils\FileManager::write($this->getSitemapPath(), $this->getFooter(), FILE_APPEND);
            $this->fileIndex++;
            \Includes\Utils\FileManager::write($this->getSitemapPath(), $this->getHead(), FILE_APPEND);
            $this->emptyFile = true;
        }
    }

    /**
     * Get sitemap path
     * 
     * @return string
     */
    protected function getSitemapPath()
    {
        return LC_DIR_DATA . 'xmlsitemap.' . $this->fileIndex . '.xml';
    }

    /**
     * Check - need switch to next file or not
     * 
     * @return boolean
     */
    protected function needSwitch()
    {
        return 9000000 < filesize($this->getSitemapPath());
    }

    // }}}
}

