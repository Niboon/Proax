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

namespace XLite\Model\Repo;

/**
 * Order history events repository
 */
class OrderHistoryEvents extends \XLite\Model\Repo\ARepo
{
    /**
     * Search for events of the given order
     *
     * @param integer $orderId Order identificator
     *
     * @return \Doctrine\ORM\PersistentCollection|integer
     */
    public function search($orderId)
    {
        $queryBuilder = $this->createQueryBuilder('ohe')
            ->andWhere('ohe.order = :order_id')
            ->setParameter('order_id', $orderId)
            ->addOrderBy('ohe.date', 'DESC');

        return $queryBuilder->getResult();
    }

    /**
     * Register event to the order
     *
     * @param integer $orderId     Order identificator
     * @param string  $code        Event code
     * @param string  $description Event description
     * @param array   $data        Data for event description OPTIONAL
     * @param string  $comment     Event comment OPTIONAL
     * @param array   $details     Event details OPTIONAL
     *
     * @return void
     */
    public function registerEvent($orderId, $code, $description, array $data = array(), $comment = '', $details = array())
    {
        $order = \XLite\Core\Database::getRepo('XLite\Model\Order')->find($orderId);

        if (!$order->isRemoving()) {

            $event = new \XLite\Model\OrderHistoryEvents(
                array(
                    'date'         => \XLite\Core\Converter::time(),
                    'code'         => $code,
                    'description'  => $description,
                    'data'         => $data,
                    'comment'      => $comment,
                )
            );

            if (!empty($details)) {
                $event->setDetails($details);
            }

            if (\XLite\Core\Auth::getInstance()->getProfile()) {
                $event->setAuthor(\XLite\Core\Auth::getInstance()->getProfile());
            }

            $event->setOrder($order);

            $order->addEvents($event);

            $this->insert($event);
        }
    }
}
