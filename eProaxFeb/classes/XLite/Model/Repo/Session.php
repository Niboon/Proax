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
 * Session repository
 */
class Session extends \XLite\Model\Repo\ARepo
{
    /**
     * Public session id length
     */
    const PUBLIC_SESSION_ID_LENGTH = 32;


    /**
     * Repository type
     *
     * @var string
     */
    protected $type = self::TYPE_SERVICE;

    /**
     * Public session id characters list
     *
     * @var array
     */
    protected $chars = array(
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j',
        'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't',
        'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D',
        'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N',
        'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X',
        'Y', 'Z',
    );

    /**
     * Load raw fixture
     *
     * @param \XLite\Model\AEntity $entity  Entity
     * @param array                $record  Record
     * @param array                $regular Regular fields info OPTIONAL
     * @param array                $assocs  Associations info OPTIONAL
     *
     * @return void
     */
    public function loadRawFixture(\XLite\Model\AEntity $entity, array $record, array $regular = array(), array $assocs = array())
    {
        parent::loadRawFixture($entity, $record, $regular, $assocs);

        if (isset($record['cells']) && is_array($record['cells'])) {
            foreach ($record['cells'] as $cell) {
                $entity->__set($cell['name'], $cell['value']);
            }
        }
    }

    // {{{ Remove expired sessions

    /**
     * Find cell by session id and name
     *
     * @return \XLite\Model\SessionCell|void
     */
    public function removeExpired()
    {
        return $this->defineRemoveExpiredQuery()->execute();
    }

    /**
     * Define query for removeExpired() method
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineRemoveExpiredQuery()
    {
        return $this->getQueryBuilder()
            ->delete($this->_entityName, 's')
            ->andWhere('s.expiry < :time')
            ->setParameter('time', \XLite\Core\Converter::time());
    }

    // }}}

    // {{{ countBySid

    /**
     * Count session by public session id
     *
     * @param string $sid Public session id
     *
     * @return integer
     */
    public function countBySid($sid)
    {
        return intval($this->defineCountBySidQuery($sid)->getSingleScalarResult());
    }

    /**
     * Define query for countBySid() method
     *
     * @param string $sid Public session id
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineCountBySidQuery($sid)
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(s)')
            ->andWhere('s.sid = :sid')
            ->setParameter('sid', $sid);
    }

    // }}}

    // {{{

    /**
     * Generate public session id
     *
     * @return string
     */
    public function generatePublicSessionId()
    {
        $iterationLimit = 10;
        $limit = count($this->chars) - 1;

        do {
            $x = explode('.', uniqid('', true));
            mt_srand(microtime(true) + intval(hexdec($x[0])) + $x[1]);
            $sid = '';
            for ($i = 0; self::PUBLIC_SESSION_ID_LENGTH > $i; $i++) {
                $sid .= $this->chars[mt_rand(0, $limit)];
            }
            $iterationLimit--;

        } while (0 < $this->countBySid($sid) && 0 < $iterationLimit);

        if (0 == $iterationLimit) {
            // TODO - add throw exception
        }

        return $sid;
    }

    /**
     * Check - public session id is valid or not
     *
     * @param string $sid Public session id
     *
     * @return boolean
     */
    public function isPublicSessionIdValid($sid)
    {
        static $regexp = null;

        if (!isset($regexp)) {
            $regexp = '/^[' . preg_quote(implode('', $this->chars), '/') . ']'
                . '{' . self::PUBLIC_SESSION_ID_LENGTH . '}$/Ss';
        }

        return is_string($sid) && (bool)preg_match($regexp, $sid);
    }

    // }}}
}
