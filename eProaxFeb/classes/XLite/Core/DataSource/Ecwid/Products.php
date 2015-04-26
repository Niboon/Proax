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

namespace XLite\Core\DataSource\Ecwid;

/**
 * Ecwid products collection
 */
class Products extends \XLite\Core\DataSource\Base\Products
{

    /**
     * Stores current iterator position
     * 
     * @var integer
     */
    protected $position;

    /**
     * Contains all products (though information for each is restrained)
     * 
     * @var array
     */
    protected $allProducts;

    /**
     * An array with cached products (full info)
     * 
     * @var array
     */
    protected $products = array();

    /**
     * Constructor 
     * 
     * @param \XLite\Core\DataSource\Ecwid $dataSource Ecwid data source
     *  
     * @return void
     */
    public function __construct(\XLite\Core\DataSource\Ecwid $dataSource)
    {
        parent::__construct($dataSource);

        $this->allProducts = $this->getDataSource()->callApi('products');

        $this->rewind();

        // May be neccessary to use Ecwid `Batch` method to preload products data
    }

    /**
     * Countable::count 
     * 
     * @return integer
     */
    public function count()
    {
        return count($this->allProducts);
    }

    /**
     * SeekableIterator::key 
     * Returns current product index
     * 
     * @return integer
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * SeekableIterator::rewind
     * Sets position to the start
     * 
     * @return void
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * SeekableIterator::next
     * Advances position one step forward
     * 
     * @return void
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * SeekableIterator::valid
     * Checks if current position is valid
     * 
     * @return boolean
     */
    public function valid()
    {
        return 0 <= $this->key() && $this->key() < $this->count();
    }

    /**
     * SeekableIterator::seek
     * Seeks to the specified position
     * 
     * @param mixed $position Position to go to
     *  
     * @return void
     * @throws OutOfBoundException
     */
    public function seek($position)
    {
        $this->position = $position;

        if (!$this->valid()) {
            throw new \OutOfBoundsException("Ecwid Products: invalid seek position ($position)");
        }
    }

    /**
     * SeekableIterator::current
     * Returns current product
     * 
     * @return void
     * @throws OutOfBoundException
     */
    public function current()
    {
        if (!$this->valid()) {
            throw new \OutOfBoundsException("Ecwid Products: invalid position ($position)");
        }

        if (!isset($this->products[$this->position])) {
            // Do a batch call to prefetch next 100 (or 15?) products (maximum)
            // (optimized for forward access)
            $max = 15;
            $num = min($max, $this->count() - $this->position);

            $params = array();
            foreach (range($this->position, $this->position + $num - 1) as $index) {
                $params[$index] = array(
                    'method' => 'product',
                    'params' => array(
                        'id' => $this->allProducts[$index]['id'],
                    )
                );
            }

            foreach ($this->getDataSource()->callBatchApi($params) as $key => $p) {
                $this->products[$key] = $this->normalizeProduct($p);
            }
        }

        return $this->products[$this->position];
    }

    /**
     * Normalize product 
     * 
     * @param array $data Raw data
     *  
     * @return array
     */
    protected function normalizeProduct(array $data)
    {
        $product = array(
            'id'           => $data['id'],
            'categories'   => array(),
            'images'       => array(),
            'url'          => $data['url'],
            'price'        => doubleval($data['price']),
            'name'         => $data['name'],
            'description'  => empty($data['description']) ? '' : $data['description'],
            'sku'          => empty($data['sku']) ? '' : $data['sku'],
            'quantity'     => empty($data['quantity']) ? \XLite\Model\Inventory::AMOUNT_DEFAULT_INV_TRACK : intval($data['quantity']),
            'optionGroups' => array(),
        );

        if (!empty($data['imageUrl'])) {
            $product['images'][] = array(
                'url'    => $data['imageUrl'],
                'main'   => true,
                'width'  => 0,
                'height' => 0,
                'type'   => '',
            );
        }

        if (!empty($data['galleryImages']) && is_array($data['galleryImages'])) {
            foreach ($data['galleryImages'] as $image) {
                $product['images'][] = array(
                    'url'    => $image['url'],
                    'main'   => false,
                    'width'  => 0,
                    'height' => 0,
                    'type'   => '',
                );
            }
        }

        if (!empty($data['categories']) && is_array($data['categories'])) {
            $product['categories'] = $data['categories'];
        }

        if (!empty($data['options']) && is_array($data['options'])) {
            foreach ($data['options'] as $option) {
                $opt = array(
                    'name'    => $option['name'],
                    'options' => array(),
                );

                if (!empty($option['choices']) && is_array($option['choices'])) {
                    foreach ($option['choices'] as $choice) {
                        $opt['options'][] = array(
                            'name'      => $choice['text'],
                            'modifiers' => array(
                                'price' => array(
                                    'type'  => 'ABSOLUTE' == $choice['priceModifierType'] ? '$' : '%',
                                    'value' => $choice['priceModifier'],
                                ),
                                'weight' => array(
                                    'type'  => '$',
                                    'value' => 0,
                                ),
                            ),
                        );
                    }
                }

                $product['optionGroups'][] = $opt;
            }
        }

        return $product;
    }
}
