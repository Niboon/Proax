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
 * Measures repository
 */
class Measure extends \XLite\Model\Repo\ARepo
{
    /**
     * Get total score
     *
     * @return float
     */
    public function getScore()
    {
        $result = $this->createQueryBuilder('m')
            ->select('AVG(m.fsTime) fsTime')
            ->addSelect('AVG(m.dbTime) dbTime')
            ->addSelect('AVG(m.cpuTime) cpuTime')
            ->getArrayResult();
        $result = reset($result);

        return $result
            ? round($result['fsTime'] + $result['dbTime'] + $result['cpuTime'], 0)
            : 0;
    }

    /**
     * Get file system score
     *
     * @return float
     */
    public function getFilesystemScore()
    {
        $result = $this->createQueryBuilder('m')
            ->select('AVG(m.fsTime) time')
            ->getArrayResult();
        $result = reset($result);

        return $result ? round($result['time'], 0) : 0;
    }

    /**
     * Get database score
     *
     * @return float
     */
    public function getDatabaseScore()
    {
        $result = $this->createQueryBuilder('m')
            ->select('AVG(m.dbTime) time')
            ->getArrayResult();
        $result = reset($result);

        return $result ? round($result['time'], 0) : 0;
    }

    /**
     * Get computation score
     *
     * @return float
     */
    public function getComputationScore()
    {
        $result = $this->createQueryBuilder('m')
            ->select('AVG(m.cpuTime) time')
            ->getArrayResult();
        $result = reset($result);

        return $result ? round($result['time'], 0) : 0;
    }

    /**
     * Get last date
     *
     * @return integer
     */
    public function getLastDate()
    {
        $result = $this->createQueryBuilder('m')
            ->orderBy('m.date', 'desc')
            ->setMaxResults(1)
            ->getSingleResult();

        return $result ? $result->getDate() : null;
    }
}
