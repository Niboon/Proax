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
 * Form id repository
 */
class FormId extends \XLite\Model\Repo\ARepo
{
    /**
     * Form id length
     */
    const FORM_ID_LENGTH = 32;


    /**
     * Repository type
     *
     * @var string
     */
    protected $type = self::TYPE_SERVICE;

    /**
     * Default 'order by' field name
     *
     * @var string
     */
    protected $defaultOrderBy = array(
        'date' => false,
        'id'   => false,
    );

    /**
     * Form id characters list
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
     * Frontier length
     *
     * @var integer
     */
    protected $frontierLength = 100;

    /**
     * Count session by public session id
     *
     * @param string  $formId    Form id
     * @param integer $sessionId Session id OPTIONAL
     *
     * @return integer
     */
    public function countByFormIdAndSessionId($formId, $sessionId = null)
    {
        if (!isset($sessionId)) {
            $sessionId = \XLite\Core\Session::getInstance()->getModel()->getId();
        }

        return intval($this->defineByFormIdAndSessionIdQuery($formId, $sessionId)->getSingleScalarResult());
    }

    /**
     * Generate public session id
     *
     * @param integer $sessionId Session id OPTIONAL
     *
     * @return string
     */
    public function generateFormId($sessionId = null)
    {
        if (!isset($sessionId)) {
            $sessionId = \XLite\Core\Session::getInstance()->getModel()->getId();
        }
        $iterationLimit = 30;
        $limit = count($this->chars) - 1;

        do {
            $id = '';
            for ($i = 0; self::FORM_ID_LENGTH > $i; $i++) {
                $id .= $this->chars[mt_rand(0, $limit)];
            }
            $iterationLimit--;
        } while (0 < $this->countByFormIdAndSessionId($id, $sessionId) && 0 < $iterationLimit);

        if (0 == $iterationLimit) {
            // TODO - add throw exception
            \XLite\Logger::logCustom(
                'FORM_ID_GENERATOR',
                'Iteration limit has been reached during the Form ID generation procedure. sessionID=' . $sessionId . ', formId=' . $id
            );
        }

        return $id;
    }

    /**
     * Remove expired form IDs
     *
     * @param integer $sessionId Session id OPTIONAL OPTIONAL
     *
     * @return void
     */
    public function removeExpired($sessionId = null)
    {
        if (!isset($sessionId)) {
            $sessionId = \XLite\Core\Session::getInstance()->getSessionFormId();
        }

        $id = $this->getFrontierId($sessionId);
        if ($id) {
            $this->defineRemoveExpiredQuery($id, $sessionId)->execute();
        }
    }

    /**
     * Process DB schema
     *
     * @param array  $schema Schema
     * @param string $type   Schema type
     *
     * @return array
     */
    public function processSchema(array $schema, $type)
    {
        $schema = parent::processSchema($schema, $type);

        // Add the unique table prefix to prevent multi-installation collision
        $constraintName = \XLite\Core\Database::getInstance()->getTablePrefix() . 'formid_to_session';
        if (\XLite\Core\Database::SCHEMA_CREATE == $type) {
            $schema[] = 'ALTER TABLE `' . $this->getClassMetadata()->getTableName() . '`'
                . ' ADD CONSTRAINT `' . $constraintName . '` FOREIGN KEY `session_id` (`session_id`)'
                . ' REFERENCES `' . $this->_em->getClassMetadata('XLite\Model\Session')->getTableName() . '` (`id`)'
                . ' ON DELETE CASCADE ON UPDATE CASCADE';

        } elseif (\XLite\Core\Database::SCHEMA_UPDATE == $type) {
            $schema = preg_grep('/DROP FOREIGN KEY `?' . $constraintName . '`?/Ss', $schema, PREG_GREP_INVERT);
        }

        return $schema;
    }


    /**
     * Define query for countByFormIdAndSessionId) method
     *
     * @param string  $formId    Form id
     * @param integer $sessionId Session id
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineByFormIdAndSessionIdQuery($formId, $sessionId)
    {
        return $this->createQueryBuilder('f')
            ->select('COUNT(f)')
            ->andWhere('f.session_id = :sid AND f.form_id = :fid')
            ->setParameter('sid', $sessionId)
            ->setParameter('fid', $formId);
    }

    /**
     * Get frontier date
     *
     * @param integer $sessionId Session id
     *
     * @return integer|void
     */
    protected function getFrontierId($sessionId)
    {
        return $this->defineGetFrontierQuery($this->frontierLength, $sessionId)->getSingleScalarResult() ?: null;
    }

    /**
     * Define query for getFrontierId() method
     *
     * @param integer $frontier  Frontier length
     * @param integer $sessionId Session id
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineGetFrontierQuery($frontier, $sessionId)
    {
        return $this->createQueryBuilder('f')
            ->select('f.id')
            ->andWhere('f.session_id = :sid')
            ->setFirstResult($frontier)
            ->setMaxResults(1)
            ->setParameter('sid', $sessionId);
    }

    /**
     * Define query for removeExpired() method
     *
     * @param integer $id        Frontier id
     * @param integer $sessionId Session id
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineRemoveExpiredQuery($id, $sessionId)
    {
        return $this->getQueryBuilder()
            ->delete($this->_entityName, 'f')
            ->andWhere('f.date < :time AND f.session_id = :sid')
            ->setParameter('time', \XLite\Core\Converter::time())
            ->setParameter('sid', $sessionId);
    }
}
