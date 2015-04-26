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
 * Session cell repository
 */
class SessionCell extends \XLite\Model\Repo\ARepo
{
    /**
     * Repository type
     *
     * @var string
     */
    protected $type = self::TYPE_SERVICE;

    /**
     * Insert new cell
     *
     * @param integer $id    Session ID
     * @param string  $name  Cell name
     * @param mixed   $value Data to store
     *
     * @return \XLite\Model\SessionCell
     */
    public function insertCell($id, $name, $value)
    {
        $connection = $this->_em->getConnection();

        $data = $this->prepareDataForNewCell($id, $name, $value);

        $connection->connect();

        $cols = array();
        $placeholders = array();

        foreach ($data as $columnName => $value) {
            $cols[] = $columnName;
            $placeholders[] = '?';
        }

        $query = 'REPLACE INTO ' . $this->_class->getTableName()
               . ' (' . implode(', ', $cols) . ')'
               . ' VALUES (' . implode(', ', $placeholders) . ')';

        $connection->executeUpdate($query, array_values($data));

        return $this->findOneBy(
            array(
                'id'   => $id,
                'name' => $name,
            )
        );
    }

    /**
     * Update existsing cell
     *
     * @param \XLite\Model\SessionCell $cell  Cell to update
     * @param mixed                    $value Value to set
     *
     * @return void
     */
    public function updateCell(\XLite\Model\SessionCell $cell, $value)
    {
        $cell->setValue($value);
        $this->update($cell, array(), false);
        $em = $this->getEntityManager();
        $em->persist($cell);
        $em->flush($cell);
    }

    /**
     * Remove cell
     *
     * @param \XLite\Model\SessionCell $cell Cell to delete
     *
     * @return void
     */
    public function removeCell(\XLite\Model\SessionCell $cell)
    {
        if (
            !$cell->isDetached()
            && \XLite\Core\Database::getEM()->contains($cell)
        ) {
            $this->delete($cell);
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
        $constraintName = \XLite\Core\Database::getInstance()->getTablePrefix() . 'session_cell_to_session';
        if (\XLite\Core\Database::SCHEMA_CREATE == $type) {
            $schema[] = 'ALTER TABLE `' . $this->getClassMetadata()->getTableName() . '`'
                . ' ADD CONSTRAINT `' . $constraintName . '` FOREIGN KEY `id` (`id`)'
                . ' REFERENCES `' . $this->_em->getClassMetadata('XLite\Model\Session')->getTableName() . '` (`id`)'
                . ' ON DELETE CASCADE ON UPDATE CASCADE';

        } elseif (\XLite\Core\Database::SCHEMA_UPDATE == $type) {
            $schema = preg_grep('/DROP FOREIGN KEY `?' . $constraintName . '`?/Ss', $schema, PREG_GREP_INVERT);
        }

        return $schema;
    }

    /**
     * Prepare data for cell insert
     *
     * @param integer $id    Session ID
     * @param string  $name  Cell name
     * @param mixed   $value Data to store
     *
     * @return array
     */
    protected function prepareDataForNewCell($id, $name, $value)
    {
        return array(
            'id'    => $id,
            'name'  => $name,
        ) + $this->prepareDataForExistingCell($value);
    }

    /**
     * Prepare data for cell update
     *
     * @param mixed                    $value Data to store
     * @param \XLite\Model\SessionCell $cell  Cell to update OPTIONAL
     *
     * @return array
     */
    protected function prepareDataForExistingCell($value, \XLite\Model\SessionCell $cell = null)
    {
        return array(
            'value' => \XLite\Model\SessionCell::prepareValueForSet($value),
            'type'  => \XLite\Model\SessionCell::getTypeByValue($value),
        );
    }
}
