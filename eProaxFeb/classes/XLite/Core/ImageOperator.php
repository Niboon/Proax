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

namespace XLite\Core;

/**
 * Image operator
 */
class ImageOperator extends \XLite\Base\SuperClass
{
    /**
     * Engine
     *
     * @var \XLite\Core\ImageOperator\AImageOperator
     */
    protected static $engine;

    /**
     * Model
     *
     * @var \XLite\Model\Base\Image
     */
    protected $model;

    /**
     * Prepared flag
     *
     * @var boolean
     */
    protected $prepared = false;


    /**
     * Call engine (static)
     *
     * @param string $method Method name
     * @param array  $args   Arguments OPTIONAL
     *
     * @return mixed
     */
    public static function __callStatic($method, array $args = array())
    {
        return call_user_func_array(array(get_class(static::getEngine()), $method), $args);
    }


    /**
     * Get engine
     *
     * @return \XLite\Core\ImageOperator\AImageOperator
     */
    protected static function getEngine()
    {
        // Binary ImageMagic
        if (!isset(static::$engine)) {
            if (\XLite\Core\ImageOperator\ImageMagic::isEnabled()) {
                static::$engine = new \XLite\Core\ImageOperator\ImageMagic;

            } elseif (\XLite\Core\ImageOperator\GD::isEnabled()) {
                static::$engine = new \XLite\Core\ImageOperator\GD;
            }
        }

        return static::$engine;
    }

    /**
     * Constructor
     *
     * @param \XLite\Model\Base\Image $image Image
     *
     * @return void
     */
    public function __construct(\XLite\Model\Base\Image $image)
    {
        $this->model = $image;
    }

    /**
     * Call engine
     *
     * @param string $method Method name
     * @param array  $args   Arguments OPTIONAL
     *
     * @return mixed
     */
    public function __call($method, array $args = array())
    {
        $this->prepare();

        return $this->prepare() ? call_user_func_array(array(static::getEngine(), $method), $args) : false;
    }


    /**
     * Prepare image
     *
     * @return boolean
     */
    protected function prepare()
    {
        $result = true;
        if (!$this->prepared) {
            $result = static::getEngine()->setImage($this->model);
            $this->prepared = true;
        }

        return $result;
    }
}
