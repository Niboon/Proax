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
 * Template patches repository
 */
class TemplatePatch extends \XLite\Model\Repo\ARepo
{
    /**
     * Repository type
     *
     * @var string
     */
    protected $type = self::TYPE_INTERNAL;

    /**
     * Default 'order by' field name
     *
     * @var array
     */
    protected $defaultOrderBy = array(
        'patch_type' => true,
        'patch_id'   => true,
    );


    // defineCacheCells

    /**
     * Define cache cells
     *
     * @return array
     */
    protected function defineCacheCells()
    {
        $list = parent::defineCacheCells();
        $list['all'] = array();

        return $list;
    }

    // }}}

    // {{{ findAllPatches

    /**
     * Find all patches
     *
     * @return array
     */
    public function findAllPatches()
    {
        $data = $this->getFromCache('all');
        if (is_null($data)) {
            $data = $this->defineAllPatchesQuery()->getResult();
            $data = $this->postprocessAllPatches($data);
            $this->saveToCache($data, 'all');
        }

        return $data;
    }

    /**
     * Define query builder for findAllPatches()
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineAllPatchesQuery()
    {
        return $this->createQueryBuilder()
            ->andWhere('t.version IS NULL');
    }

    /**
     * Data postprocessing for findAllPatches()
     *
     * @param array $data Raw data
     *
     * @return array
     */
    protected function postprocessAllPatches(array $data)
    {
        $result = array();

        foreach ($data as $patch) {
            $zone = $patch->zone;
            $lang = $patch->lang;
            $tpl = $patch->tpl;

            if (!isset($result[$zone])) {
                $result[$zone] = array($lang => array($tpl => array($patch)));

            } elseif (!isset($result[$zone][$lang])) {
                $result[$zone][$lang] = array($tpl => array($patch));

            } elseif (!isset($result[$zone][$lang][$tpl])) {
                $result[$zone][$lang][$tpl] = array($patch);

            } else {
                $result[$zone][$lang][$tpl][] = $patch;
            }
        }

        return $result;
    }

    // }}}

    // {{{ Operations

    /**
     * Delete obsolete template patches
     *
     * @param string $currentVersion Current version
     *
     * @return void
     */
    public function deleteObsolete($currentVersion)
    {
        $this->defineDeleteObsoleteQuery($currentVersion)
            ->execute();
    }

    /**
     * Mark current template patches as default
     *
     * @param string $currentVersion Current version
     *
     * @return void
     */
    public function markCurrentVersion($currentVersion)
    {
        $this->defineMarkCurrentVersionQuery($currentVersion)
            ->execute();
    }

    /**
     * Define query for deleteObsolete() method
     *
     * @param string $currentVersion Current version
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function defineDeleteObsoleteQuery($currentVersion)
    {
        return $this->createPureQueryBuilder('t', false)
            ->delete($this->_entityName, 't')
            ->andWhere('t.version != :version OR t.version IS NULL')
            ->setParameter('version', $currentVersion);
    }

    /**
     * Define query for markCurrentVersion() method
     *
     * @param string $currentVersion Current version
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function defineMarkCurrentVersionQuery($currentVersion)
    {
        return $this->createPureQueryBuilder('t', false)
            ->update($this->_entityName, 't')
            ->set('t.version', 'NULL')
            ->andWhere('t.version = :version')
            ->setParameter('version', $currentVersion);
    }

    // }}}

}
