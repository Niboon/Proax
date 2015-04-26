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

namespace XLite\Model\Repo\Order;

/**
 * Order modifier repository
 */
class Modifier extends \XLite\Model\Repo\ARepo
{
    /**
     * Default 'order by' field name
     *
     * @var string
     */
    protected $defaultOrderBy = 'weight';

    /**
     * Alternative record identifiers
     *
     * @var array
     */
    protected $alternativeIdentifier = array(
        array('class'),
    );

    /**
     * Find all active modifiers
     *
     * @return array
     */
    public function findActive()
    {
        $list = $this->createQueryBuilder()->getResult();

        $list = is_array($list) ? new \XLite\DataSet\Collection\OrderModifier($list) : null;

        if ($list) {
            foreach ($list as $i => $item) {
                if (!\XLite\Core\Operator::isClassExists($item->getClass())) {
                    unset($list[$i]);
                }
            }
        }

        return $list;
    }

    /**
     * Define query for findActive() method
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineFindActiveQuery()
    {
        return $this->createQueryBuilder();
    }

}
