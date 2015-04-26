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

namespace XLite\Module\CDev\ProductAdvisor\Controller\Customer;

/**
 * Checkout controller extention
 */
class Checkout extends \XLite\Controller\Customer\Checkout implements \XLite\Base\IDecorator
{
    /**
     * Order placement is success
     *
     * @return void
     */
    public function processSucceed($doCloneProfile = true)
    {
        $this->saveProductStats(\XLite\Module\CDev\ProductAdvisor\Main::getProductIds());

        parent::processSucceed($doCloneProfile);
    }

    /**
     * Order placement is success
     *
     * @return void
     */
    protected function saveProductStats($viewedProductIds)
    {
        $viewedProducts = \XLite\Core\Database::getRepo('XLite\Model\Product')->findByProductIds($viewedProductIds);

        if ($viewedProducts) {

            $orderItems = $this->getCart()->getItems();
            $orderedProducts = array();

            foreach ($orderItems as $item) {
                if ($item->getProduct() && 0 < $item->getProduct()->getProductId()) {
                    $orderedProducts[$item->getProduct()->getProductId()] = $item->getProduct();
                }
            }

            // Find existing statistics records
            $foundStats = \XLite\Core\Database::getRepo('XLite\Module\CDev\ProductAdvisor\Model\ProductStats')
                ->findStats($viewedProductIds, array_keys($orderedProducts));

            // Prepare array of pairs 'A-B', 'C-D',... where A,C - viewed product ID, B,D - ordered product ID
            // This will make comparison easy 
            $foundStatsPairs = array();

            if ($foundStats) {
                foreach ($foundStats as $stats) {
                    $foundStatsPairs[] = sprintf(
                        '%d-%d',
                        $stats->getViewedProduct()->getProductId(),
                        $stats->getBoughtProduct()->getProductId()
                    );
                }
            }

            // Update exsisting statistics
            \XLite\Core\Database::getRepo('XLite\Module\CDev\ProductAdvisor\Model\ProductStats')
                ->updateStats($foundStats);

            $statsCreated = false;

            foreach ($orderedProducts as $opid => $orderedProduct) {

                foreach ($viewedProducts as $viewedProduct) {

                    if (
                        !in_array(sprintf('%d-%d', $viewedProduct->getProductId(), $opid), $foundStatsPairs)
                        && $viewedProduct->getProductId() != $opid
                    ) {

                        // Create statistics record
                        $stats = new \XLite\Module\CDev\ProductAdvisor\Model\ProductStats();
                        $stats->setViewedProduct($viewedProduct);
                        $stats->setBoughtProduct($orderedProduct);
        
                        \XLite\Core\Database::getEM()->persist($stats);

                        $statsCreated = true;
                    }
                }
            }

            if ($statsCreated) {
                \XLite\Core\Database::getEM()->flush();
            }
        }
    }
}
