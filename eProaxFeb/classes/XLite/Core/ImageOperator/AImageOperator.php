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

namespace XLite\Core\ImageOperator;

/**
 * Abstract image operator enagine
 */
abstract class AImageOperator extends \XLite\Base\Singleton
{
    /**
     * MIME type
     *
     * @var string
     */
    protected $mimeType;

    /**
     * Width
     *
     * @var integer
     */
    protected $width;

    /**
     * Height
     *
     * @var integer
     */
    protected $height;


    /**
     * Get image content
     *
     * @return string
     */
    abstract public function getImage();

    /**
     * Resize
     *
     * @param integer $width  Width
     * @param integer $height Height
     *
     * @return boolean
     */
    abstract public function resize($width, $height);


    /**
     * Check - enabled engine or not
     *
     * @return boolean
     */
    public static function isEnabled()
    {
        return true;
    }

    /**
     * Get cropped dimensions
     *
     * @param integer $w    Original width
     * @param integer $h    Original height
     * @param integer $maxw Maximum width
     * @param integer $maxh Maximum height
     *
     * @return array (new width & height)
     */
    public static function getCroppedDimensions($w, $h, $maxw, $maxh)
    {
        $maxw = max(0, intval($maxw));
        $maxh = max(0, intval($maxh));

        $properties = array(
            'width'  => 0 < $w ? $w : $maxw,
            'height' => 0 < $h ? $h : $maxh,
        );

        if (0 < $w && 0 < $h && (0 < $maxw || 0 < $maxh)) {

            if (0 < $maxw && 0 < $maxh) {
                $kw = $w > $maxw ? $maxw / $w : 1;
                $kh = $h > $maxh ? $maxh / $h : 1;
                $k = $kw < $kh ? $kw : $kh;

            } elseif (0 < $maxw) {
                $k = $w > $maxw ? $maxw / $w : 1;

            } elseif (0 < $maxh) {
                $k = $h > $maxh ? $maxh / $h : 1;

            }

            $properties['width'] = max(1, round($k * $w, 0));
            $properties['height'] = max(1, round($k * $h, 0));
        }

        if (0 == $properties['width']) {
            $properties['width'] = null;
        }

        if (0 == $properties['height']) {
            $properties['height'] = null;
        }

        return array($properties['width'], $properties['height']);
    }


    /**
     * Set image
     *
     * @param \XLite\Model\Base\Image $image Image
     *
     * @return boolean
     */
    public function setImage(\XLite\Model\Base\Image $image)
    {
        $this->mimeType = $image->getMime();
        $this->width = $image->getWidth();
        $this->height = $image->getHeight();

        return true;
    }

    /**
     * Get width
     *
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Get height
     *
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Get MIME type
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Resize down  by limits
     *
     * @param integer $width  Width top limit OPTIONAL
     * @param integer $height Height top limt OPTIONAL
     *
     * @return array New width, new height and operation result
     */
    public function resizeDown($width = null, $height = null)
    {
        return ($width != $this->width || $height != $this->height)
            ? array($width, $height, $this->resize($width, $height))
            : array($width, $height, false);
    }
}
