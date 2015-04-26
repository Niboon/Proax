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

namespace XLite\Core\ColumnType;

/**
 * Money (value without currency)
 */
class Money extends \Doctrine\DBAL\Types\DecimalType
{

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'money';
    }

    /**
     * Get SQL declaration
     *
     * @param array                                     $fieldDeclaration Field declaration
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform         Platform
     *
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, \Doctrine\DBAL\Platforms\AbstractPlatform $platform)
    {
        $fieldDeclaration['precision'] = 14;
        $fieldDeclaration['scale'] = 4;

        return parent::getSQLDeclaration($fieldDeclaration, $platform);
    }

    /**
     * Convert to PHP value
     *
     * @param string                                    $value    Value
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform Platform
     *
     * @return float
     */
    public function convertToPHPValue($value, \Doctrine\DBAL\Platforms\AbstractPlatform $platform)
    {
        return (null === $value) ? null : doubleval($value);
    }

    /**
     * If this Doctrine Type maps to an already mapped database type,
     * reverse schema engineering can't take them apart. You need to mark
     * one of those types as commented, which will have Doctrine use an SQL
     * comment to typehint the actual Doctrine Type.
     *
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     *
     * @return boolean
     */
    public function requiresSQLCommentHint(\Doctrine\DBAL\Platforms\AbstractPlatform $platform)
    {
        return true;
    }
}
