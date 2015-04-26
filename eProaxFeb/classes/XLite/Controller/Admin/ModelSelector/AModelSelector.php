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

namespace XLite\Controller\Admin\ModelSelector;

/**
 * Model selector abstract
 */
abstract class AModelSelector extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Get data of the model request
     *
     * @return \Doctrine\ORM\PersistentCollection | array
     */
    abstract protected function getData();

    /**
     * Format model text presentation
     *
     * @param mixed $item Model item
     *
     * @return string
     */
    abstract protected function formatItem($item);

    /**
     * Defines the model value
     *
     * @param mixed $item Model item
     *
     * @return string
     */
    abstract protected function getItemValue($item);

    /**
     * Define specific data structure which will be sent in the triggering event (model.selected)
     *
     * @param mixed $item Model item
     *
     * @return string
     */
    protected function defineDataItem($item)
    {
        return array(
            'presentation' => $this->formatItem($item),
        );
    }

    /**
     * Process request
     *
     * @return void
     */
    public function processRequest()
    {
        header('Content-Type: text/html; charset=utf-8');

        \Includes\Utils\Operator::flush($this->getJSONData());
    }

    /**
     * Final data presentation. JSON array:
     * key => array(data)
     *
     * @return string
     */
    protected function getJSONData()
    {
        $data = $this->getData();
        array_walk($data, array($this, 'prepareItem'));

        return json_encode(
            array(
                $this->getKey() => (false === $data ? array() : $data),
            )
        );
    }

    /**
     * Format the value for the method: $this->getJSONData()
     *
     * @param mixed   &$item
     * @param integer $index
     *
     * @return void
     */
    public function prepareItem(&$item, $index)
    {
        $item = array(
            'text_presentation'  => $this->formatItem($item),
            'value'              => $this->getItemValue($item),
            'data'               => $this->defineDataItem($item)
        );
    }

    /**
     * The main value to search between the models
     *
     * @return string
     */
    protected function getKey()
    {
        return \XLite\Core\Request::getInstance()->search;
    }
}
