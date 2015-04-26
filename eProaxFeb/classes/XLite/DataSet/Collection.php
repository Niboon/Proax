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

namespace XLite\DataSet;

/**
 * Collection
 */
class Collection extends \Doctrine\Common\Collections\ArrayCollection
{
    // {{{ Elements checking

    /**
     * Constructor
     *
     * @param array $elements Elements OPTIONAL
     *
     * @return void
     */
    public function __construct(array $elements = array())
    {
        parent::__construct($elements);
        $this->filterElements();
    }

    /**
     * ArrayAccess implementation of  offsetSet()
     *
     * @param mixed $offset Offset
     * @param mixed $value  Value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if ($this->checkElement($value, $offset)) {
            parent::offsetSet($offset, $value);
        }
    }

    /**
     * Filter elements
     *
     * @return void
     */
    protected function filterElements()
    {
        foreach ($this as $i => $e) {
            if (!$this->checkElement($e, $i)) {
                unset($this[$i]);
            }
        }
    }

    /**
     * Check element
     *
     * @param mixed $element Element
     * @param mixed $key     Element key
     *
     * @return boolean
     */
    protected function checkElement($element, $key)
    {
        return true;
    }

    // }}}

    // {{{ Siblings

    /**
     * Get element previous siblings
     *
     * @param mixed $element Element
     *
     * @return array
     */
    public function getPreviousSiblings($element)
    {
        $previous = array();

        foreach ($this as $i => $e) {
            if ($e == $element) {
                break;
            }

            $previous[$i] = $e;
        }

        return $previous;
    }

    /**
     * Get element next siblings
     *
     * @param mixed $element Element
     *
     * @return array
     */
    public function getNextSiblings($element)
    {
        $next = array();
        $found = false;

        foreach ($this as $i => $e) {
            if ($found) {
                $next[$i] = $e;
            }

            if ($e == $element) {
                $found = true;
            }
        }

        return $next;
    }

    // }}}
}
