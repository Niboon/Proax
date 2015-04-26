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
 * @copyright Copyright (c) 2011-2014 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Module\XC\RESTAPI\Core\Schema;

/**
 * Native schema
 */
class Native extends \XLite\Module\XC\RESTAPI\Core\Schema\ASchema
{

    /**
     * Schema code
     */
    const CODE = 'default';

    /**
     * Check - schema is own this request or not
     *
     * @param string $schema Schema
     *
     * @return boolean
     */
    public static function isOwn($schema)
    {
        return !$schema || parent::isOwn($schema);
    }

    // {{{ GET

    /**
     * Find data for getAll request
     *
     * @return mixed
     */
    protected function findForGetAll()
    {
        $repository = $this->config->repository;

        return iterator_count($this->config->cnd)
            ? $repository->search($this->config->cnd)
            : $repository->findAllForREST();
    }

    /**
     * Find data for getOne request
     *
     * @return \XLite\Model\AEntity
     */
    protected function findForGetOne()
    {
        return $this->config->id
            ? $this->config->repository->findOneForREST($this->config->id)
            : null;
    }

    // }}}

    // {{{ PUT

    /**
     * Find data for putOne request
     *
     * @param mixed $id Id
     *
     * @return \XLite\Model\AEntity
     */
    protected function findForPutOne($id)
    {
        return $this->config->repository->findOneForREST($id);
    }

    // }}}

    // {{{ DELETE

    /**
     * Find data for deleteAll request
     *
     * @return mixed
     */
    protected function findForDeleteAll()
    {
        return $this->config->repository->findAllForREST();
    }

    /**
     * Find data for deleteOne request
     *
     * @param mixed $id Id
     *
     * @return \XLite\Model\AEntity
     */
    protected function findForDeleteOne($id)
    {
        return $this->config->repository->findOneForREST($id);
    }

    // }}}

    // {{{ Common routines

    /**
     * Detect entity class
     *
     * @return string
     */
    protected function detectEntityClass()
    {
        return $this->config->class;
    }

    /**
     * Convert model
     *
     * @param mixed   $model            Model OPTIONAL
     * @param boolean $withAssociations Convert with associations OPTIONAL
     *
     * @return mixed
     */
    protected function convertModel($model = null, $withAssociations = true)
    {
        return $model ? $model->buildDataForREST($withAssociations) : null;
    }

    /**
     * Assemble repository posprocess method name
     *
     * @param string $method Method name
     *
     * @return string
     */
    protected function assembleRepoPosprocessMethodName($method)
    {
        return 'postprocess' . ucfirst($method) . 'RESTRequest';
    }

    // }}}

    // {{{ Utils

    /**
     * Get entity class
     *
     * @param string $path Path
     *
     * @return string
     */
    protected function getEntityClass($path)
    {
        $parts = array_map('ucfirst', explode('-', strtolower($path)));

        $path = $this->normalizeFilePath('XLite' . LC_DS . 'Model' . LC_DS . implode(LC_DS, $parts) . '.php');
        $class = $path ? str_replace(LC_DS, '\\', substr($path, 0, -4)) : null;
        if (!$path || !\XLite\Core\Operator::isClassExists($class)) {
            $class = null;
        }

        if (!$class && 2 < count($parts)) {
            $module = \XLite\Core\Database::getRepo('XLite\Model\Module')
                ->findOneBy(array('author' => $parts[0], 'name' => $parts[1]));
            if ($module && $module->getEnabled()) {
                $path = $this->normalizeFilePath(
                    'XLite' . LC_DS . 'Module' . LC_DS
                    . $parts[0] . LC_DS . $parts[1] . LC_DS . 'Model'
                    . LC_DS . implode(LC_DS, array_slice($parts, 2)) . '.php'
                );
                $class = $path ? str_replace(LC_DS, '\\', substr($path, 0, -4)) : null;
                if (!$path || !\XLite\Core\Operator::isClassExists($class)) {
                    $class = null;
                }
            }
        }

        return $class && $this->isAllowedEntityClass($class) ? $class : null;
    }

    /**
     * Check - entity class is allowed or not
     * 
     * @param string $class Entity class name
     *  
     * @return boolean
     */
    protected function isAllowedEntityClass($class)
    {
        return true;
    }

    /**
     * Normalize file path
     *
     * @param string $relPath Relative file path
     *
     * @return string
     */
    protected function normalizeFilePath($relPath)
    {
        $baseDir = LC_DIR_CACHE_CLASSES;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($baseDir),
            \RecursiveIteratorIterator::LEAVES_ONLY,
            \FilesystemIterator::SKIP_DOTS
        );

        $baseLength = strlen($baseDir);
        $lowPath = strtolower($relPath);
        $result = null;
        foreach ($iterator as $path => $file) {
            $path = substr($path, $baseLength);
            if (strtolower($path) == $lowPath) {
                $result = $path;
                break;
            }
        }

        return $result;
    }

    // }}}

}
