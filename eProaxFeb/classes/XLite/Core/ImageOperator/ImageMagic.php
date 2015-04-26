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
 * ImageMagic
 */
class ImageMagic extends \XLite\Core\ImageOperator\AImageOperator
{
    /**
     * Image file store
     *
     * @var string
     */
    protected $image;

    /**
     * Image Magick installation path
     *
     * @var string
     */
    protected $imageMagick = '';

    /**
     * Return Image Magick executable
     *
     * @return string
     */
    public static function getImageMagickExecutable()
    {
        $imageMagickPath = \Includes\Utils\ConfigParser::getOptions(array('images', 'image_magick_path'));

        return !empty($imageMagickPath)
            ? \Includes\Utils\FileManager::findExecutable($imageMagickPath . 'convert')
            : '';
    }

    /**
     * Check - enabled or not
     *
     * @return boolean
     */
    public static function isEnabled()
    {
        return parent::isEnabled()
            && (bool) self::getImageMagickExecutable();
    }

    /**
     * Set image
     *
     * @param \XLite\Model\Base\Image $image Image
     *
     * @return void
     */
    public function setImage(\XLite\Model\Base\Image $image)
    {
        parent::setImage($image);

        $this->image = tempnam(LC_DIR_TMP, 'image');

        file_put_contents($this->image, $image->getBody());
    }

    /**
     * Get image content
     *
     * @return string
     */
    public function getImage()
    {
        return file_get_contents($this->image);
    }

    /**
     * Resize procedure
     *
     * @param integer $width  Width
     * @param integer $height Height
     *
     * @return boolean
     */
    public function resize($width, $height)
    {
        $new = tempnam(LC_DIR_TMP, 'image.new');

        $result = $this->execFilmStripLook($new);

        if (0 === $result) {

            $result = $this->execResize($new, $width, $height);

            if (0 === $result) {
                $this->image = $new;
            }
        }

        return 0 === $result;
    }

    /**
     * Execution of preparing film strip look
     *
     * @param string $newImage File path to new image
     *
     * @return integer
     */
    protected function execFilmStripLook($newImage)
    {
        exec(
            '"' . self::getImageMagickExecutable()
                . '" ' . $this->image . ' -coalesce '
                . $newImage,
            $output,
            $result
        );

        return $result;
    }

    /**
     * Execution of resizing
     *
     * @param string  $newImage File path to new image
     * @param integer $width    Width
     * @param integer $height   Height
     *
     * @return integer
     */
    protected function execResize($newImage, $width, $height)
    {
        exec(
            '"' . self::getImageMagickExecutable() . '" '
                . $newImage
                . ' -adaptive-resize '
                . $width . 'x' . $height . ' '
                . $newImage,
            $output,
            $result
        );

        return $result;
    }
}
