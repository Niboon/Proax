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

namespace XLite\Core\Validator;

/**
 * Integer
 */
class Float extends \XLite\Core\Validator\Scalar
{
    /**
     * Range minimum
     *
     * @var float
     */
    protected $min;

    /**
     * Range maximum
     *
     * @var float
     */
    protected $max;

    /**
     * Set range
     *
     * @param float $min Minimum
     * @param float $max Maximum OPTIONAL
     *
     * @return void
     */
    public function setRange($min, $max = null)
    {
        if (isset($min) && is_numeric($min)) {
            $this->min = $min;
        }

        if (isset($max) && is_numeric($max)) {
            $this->max = $max;
        }
    }

    /**
     * Validate
     *
     * @param mixed $data Data
     *
     * @return void
     * @throws \XLite\Core\Validator\Exception
     */
    public function validate($data)
    {
        if (!is_numeric($data)) {
            throw $this->throwError('Not numeric');
        }

        $data = $this->sanitize($data);

        if (isset($this->min) && $data < $this->min) {
            throw $this->throwError('Minimum limit is broken', array('min' => $this->min));
        }

        if (isset($this->max) && $data > $this->max) {
            throw $this->throwError('Maximum limit is broken', array('max' => $this->max));
        }
    }

    /**
     * Sanitaize
     *
     * @param mixed $data Daa
     *
     * @return mixed
     */
    public function sanitize($data)
    {
        return doubleval($data);
    }

}
