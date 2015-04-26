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

namespace XLite\Model;

/**
 * List node
 */
class ListNode extends \XLite\Base\SuperClass
{
    /**
     * Link to previous list element or null
     *
     * @var \XLite_Model_ListNode or null
     */
    protected $prev = null;

    /**
     * Link to next list element or null
     *
     * @var \XLite_Model_ListNode|null
     */
    protected $next = null;

    /**
     * Node identifier
     *
     * @var string
     */
    protected $key = null;


    /**
     * Set node identifier
     *
     * @param string $key Node key
     *
     * @return void
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * Return link to previous list element
     *
     * @return \XLite\Model\ListNode
     */
    public function getPrev()
    {
        return $this->prev;
    }

    /**
     * Return link to next list element
     *
     * @return \XLite\Model\ListNode
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * Set link to previous list element
     *
     * @param \XLite\Model\ListNode $node Node link to set OPTIONAL
     *
     * @return void
     */
    public function setPrev(\XLite\Model\ListNode $node = null)
    {
        $this->prev = $node;
    }

    /**
     * Set link to next list element
     *
     * @param \XLite\Model\ListNode $node Node link to set OPTIONAL
     *
     * @return void
     */
    public function setNext(\XLite\Model\ListNode $node = null)
    {
        $this->next = $node;
    }

    /**
     * Return node identifier
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Callback to search node by its identifier
     *
     * @param string $key Node key
     *
     * @return boolean
     */
    public function checkKey($key)
    {
        return $key != $this->getKey();
    }
}
