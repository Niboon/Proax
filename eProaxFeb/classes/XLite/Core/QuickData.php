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
 *  Quick data
 */
class QuickData extends \XLite\Base\Singleton implements \Countable
{
    /**
     * Processing chunk length 
     */
    const CHUNK_LENGTH = 100;

    /**
     * Memberships
     *
     * @var array
     */
    protected $memberships;

    /**
     * Update quick data
     *
     * @return void
     */
    public function update()
    {
        $i = 0;
        do {
            $processed = $this->updateChunk($i, static::CHUNK_LENGTH);
            if (0 < $processed) {
                \XLite\Core\Database::getEM()->clear();
            }
            $i += $processed;

        } while (0 < $processed);
    }

    /**
     * Update chunk 
     * 
     * @param integer $position Position OPTIONAL
     * @param integer $length   Length OPTIONAL
     *  
     * @return integer
     */
    public function updateChunk($position = 0, $length = self::CHUNK_LENGTH)
    {
        $processed = 0;
        foreach (\XLite\Core\Database::getRepo('XLite\Model\Product')->findFrame($position, $length) as $product) {
            $this->updateProductDataInternal($product);
            $processed++;
        }

        if (0 < $processed) {
            \XLite\Core\Database::getEM()->flush();
        }

        return $processed;
    }

    /**
     * Update chunk
     *
     * @param integer $length Length OPTIONAL
     *
     * @return integer
     */
    public function updateUnprocessedChunk($length = self::CHUNK_LENGTH)
    {
        $processed = 0;
        foreach (\XLite\Core\Database::getRepo('XLite\Model\Product')->findUnprocessedChunk($length) as $product) {
            $this->updateProductDataInternal($product);
            $processed++;
        }

        return $processed;
    }

    /**
     * Count
     *
     * @return integer
     */
    public function count()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Product')->count();
    }

    /**
     * Count unprocessed
     *
     * @return integer
     */
    public function countUnprocessed()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Product')->countUnprocessed();
    }

    /**
     * Update membership quick data
     *
     * @param \XLite\Model\Membership $membership Membership
     *
     * @return void
     */
    public function updateMembershipData(\XLite\Model\Membership $membership)
    {
        $i = 0;
        do {
            $processed = 0;
            foreach (\XLite\Core\Database::getRepo('XLite\Model\Product')->findFrame($i, static::CHUNK_LENGTH) as $product) {
                $this->updateData($product, $membership);
                $processed++;
            }

            if (0 < $processed) {
                \XLite\Core\Database::getEM()->flush();
                \XLite\Core\Database::getEM()->clear();
            }
            $i += $processed;

        } while (0 < $processed);
    }

    /**
     * Update product quick data
     *
     * @param \XLite\Model\Product $product Product
     *
     * @return void
     */
    public function updateProductData(\XLite\Model\Product $product)
    {
        $this->updateProductDataInternal($product);
        \XLite\Core\Database::getEM()->flush();
    }

    /**
     * Update product quick data
     *
     * @param \XLite\Model\Product $product Product
     *
     * @return void
     */
    public function updateProductDataInternal(\XLite\Model\Product $product)
    {
        foreach ($this->getMemberships() as $membership) {
            if (!isset($membership) || \XLite\Core\Database::getEM()->contains($membership)) {
                $this->updateData($product, $membership);
            }
        }
        $product->setNeedProcess(false);
    }

    /**
     * Get memberships
     *
     * @param \XLite\Model\Product $product    Product
     * @param mixed                $membership Membership
     *
     * @return \XLite\Model\QuickData
     */
    protected function updateData(\XLite\Model\Product $product, $membership)
    {
        $data = null;

        $quickData = $product->getQuickData() ?: array();

        foreach ($quickData as $qd) {
            if (
                ($qd->getMembership() && $membership && $qd->getMembership()->getMembershipId() == $membership->getMembershipId())
                || (!$qd->getMembership() && !$membership)
            ) {
                $data = $qd;
                break;
            }
        }

        if (!$data) {
            $data = new \XLite\Model\QuickData;
            $data->setProduct($product);
            $data->setMembership($membership);
            $product->addQuickData($data);
        }
        $data->setPrice($product->getQuickDataPrice());

        return $data;
    }

    /**
     * Detach products 
     * 
     * @param array $products Products
     *  
     * @return void
     */
    protected function detachProducts(array $products)
    {
        foreach ($products as $product) {
            \XLite\Core\Database::getEM()->detach($product);
        }
    }

    /**
     * Get memberships
     *
     * @return array
     */
    protected function getMemberships()
    {
        if (!isset($this->memberships)) {
            $this->memberships = \XLite\Core\Database::getRepo('XLite\Model\Membership')->findAll();
            $this->memberships[] = null;
        }

        return $this->memberships;
    }
}
