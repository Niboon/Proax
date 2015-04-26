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
 * Role repository 
 */
class Role extends \XLite\Model\Repo\Base\I18n
{
    /**
     * Find one role by permisssion code
     *
     * @param string $code Permission code
     *
     * @return \XLite\Model\Role
     */
    public function findOneByPermissionCode($code)
    {
        return $this->defineFindOneByPermissionCodeQuery($code)->getSingleResult();
    }

    /**
     * Find one role by name 
     * 
     * @param string $name Name
     *  
     * @return \XLite\Model\Role
     */
    public function findOneByName($name)
    {
        return $this->defineFindOneByNameQuery($name)->getSingleResult();
    }

    /**
     * Find one by record
     *
     * @param array                $data   Record
     * @param \XLite\Model\AEntity $parent Parent model OPTIONAL
     *
     * @return \XLite\Model\AEntity|void
     */
    public function findOneByRecord(array $data, \XLite\Model\AEntity $parent = null)
    {
        $model = parent::findOneByRecord($data, $parent);

        if (!$model && !empty($data['translations'])) {
            foreach ($data['translations'] as $translation) {
               $model = $this->findOneByName($translation['name']) ;
                if ($model) {
                    break;
                }
            }
        }

        return $model;
    }

    /**
     * Find one root-based role
     * 
     * @return \XLite\Model\Role
     */
    public function findOneRoot()
    {
        return $this->defineFindOneRootQuery()->getSingleResult();
    }

    /**
     * Define query for findOneByPermissionCode() method
     *
     * @param string $code Permission code
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function defineFindOneByPermissionCodeQuery($code)
    {
        return $this->createQueryBuilder('r')
            ->linkInner('r.permissions')
            ->andWhere('permissions.code = :code')
            ->setParameter('code', $code)
            ->setMaxResults(1);
    }

    /**
     * Define query for findOneByName() method
     * 
     * @param string $name Name
     *  
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function defineFindOneByNameQuery($name)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('translations.name = :name')
            ->setParameter('name', $name)
            ->setMaxResults(1);
    }

    /**
     * Define query for findOneRoot() method
     * 
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function defineFindOneRootQuery()
    {
        return $this->createQueryBuilder('r')
            ->linkInner('r.permissions')
            ->andWhere('permissions.code = :root')
            ->setMaxResults(1)
            ->setParameter('root', \XLite\Model\Role\Permission::ROOT_ACCESS);
    }
}
