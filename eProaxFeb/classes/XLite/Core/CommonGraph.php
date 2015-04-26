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
 * Common Graph class
 */
class CommonGraph extends \Includes\DataStructure\Graph
{
    /**
     * Parent
     *
     * @var \Includes\DataStructure\Graph
     */
    protected $parent = null;

    /**
     * Data
     *
     * @var mixed
     */
    protected $data = null;

    /**
     * Set parent
     *
     * @param \Includes\DataStructure\Graph $node Node
     *
     * @return void
     */
    public function setParent(\Includes\DataStructure\Graph $node)
    {
        $this->parent = $node;
    }

    /**
     * Returns parent
     *
     * @return \Includes\DataStructure\Graph
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set node data
     *
     * @param mixed $data Data
     *
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Returns node data
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Add child node
     *
     * @param \Includes\DataStructure\Graph $node Node to add
     *
     * @return void
     */
    public function addChild(\Includes\DataStructure\Graph $node)
    {
        $node->setParent($this);

        parent::addChild($node);
    }

    /**
     * Remove child node
     *
     * @param \Includes\DataStructure\Graph $node Node to remove
     *
     * @return void
     */
    public function removeChild(\Includes\DataStructure\Graph $node)
    {
        foreach ($this->getChildren() as $index => $child) {
            if ($node->getKey() === $child->getKey()) {
                $node->setParent(null);
            }
        }

        parent::removeChild($node);
    }
}
