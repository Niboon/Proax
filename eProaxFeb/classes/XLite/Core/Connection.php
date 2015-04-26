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
 * Doctrine-based connection
 */
class Connection extends \Doctrine\DBAL\Connection
{
    /**
     * Prepares an SQL statement
     *
     * @param string $statement The SQL statement to prepare
     *
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function prepare($statement, $options = null)
    {
        $this->connect();

        return new \XLite\Core\Statement($statement, $this);
    }

    /**
     * Executes an, optionally parameterized, SQL query.
     *
     * If the query is parameterized, a prepared statement is used.
     * If an SQLLogger is configured, the execution is logged.
     *
     * @param string                                 $query  The SQL query to execute
     * @param array                                  $params The parameters to bind to the query, if any OPTIONAL
     * @param array                                  $types  The parameters types to bind to the query, if any OPTIONAL
     * @param \Doctrine\DBAL\Cache\QueryCacheProfile $qcp    Cache profile OPTIONAL
     *
     * @return \Doctrine\DBAL\Driver\Statement
     * @throws \XLite\Core\PDOException
     */
    public function executeQuery(
        $query,
        array $params = array(),
        $types = array(),
        \Doctrine\DBAL\Cache\QueryCacheProfile $qcp = null
    ) {
        try {
            $result = parent::executeQuery($query, $params, $types, $qcp);

        } catch (\PDOException $exception) {
            throw new \XLite\Core\PDOException($exception, $query, $params);
        }

        return $result;
    }

    /**
     * Executes an SQL INSERT/UPDATE/DELETE query with the given parameters
     * and returns the number of affected rows.
     *
     * This method supports PDO binding types as well as DBAL mapping types.
     *
     * @param string $query  The SQL query
     * @param array  $params The query parameters OPTIONAL
     * @param array  $types  The parameter types OPTIONAL
     *
     * @return integer The number of affected rows
     * @throws \XLite\Core\PDOException
     */
    public function executeUpdate($query, array $params = array(), array $types = array())
    {
        try {
            $result = parent::executeUpdate($query, $params, $types);

        } catch (\PDOException $e) {
            throw new \XLite\Core\PDOException($e, $query, $params);
        }

        return $result;
    }

    /**
     * Replace query
     *
     * @param string $tableName Table name
     * @param array  $data      Data
     *
     * @return integer
     */
    public function replace($tableName, array $data)
    {
        $this->connect();

        // column names are specified as array keys
        $cols = array();
        $placeholders = array();

        foreach ($data as $columnName => $value) {
            $cols[] = $columnName;
            $placeholders[] = '?';
        }

        $query = 'REPLACE INTO ' . $tableName
               . ' (' . implode(', ', $cols) . ')'
               . ' VALUES (' . implode(', ', $placeholders) . ')';

        return $this->executeUpdate($query, array_values($data));
    }
}
