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

namespace XLite\Model\Repo\Base;

/**
 * Translations-owner abstract reporitory
 */
abstract class I18n extends \XLite\Model\Repo\ARepo
{
    /**
     * Create a new QueryBuilder instance that is prepopulated for this entity name
     *
     * @param string $alias   Table alias OPTIONAL
     * @param string $indexBy The index for the from.
     * @param string $code    Language code OPTIONAL
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    public function createQueryBuilder($alias = null, $indexBy = null, $code = null)
    {
        return $this->addLanguageQuery(parent::createQueryBuilder($alias, $indexBy), $alias, $code);
    }

    /**
     * Get translation repository
     *
     * @return \XLite\Model\repo\ARepo
     */
    public function getTranslationRepository()
    {
        return \XLite\Core\Database::getRepo($this->_entityName . 'Translation');
    }

    /**
     * Add language subquery with language code relation
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder      Query builder
     * @param string                     $alias             Main model alias OPTIONAL
     * @param string                     $code              Language code OPTIONAL
     * @param string                     $translationsAlias Translation alias OPTIONAL
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function addLanguageQuery(\Doctrine\ORM\QueryBuilder $queryBuilder, $alias = null, $code = null, $translationsAlias = 'translations')
    {
        if (!isset($alias)) {
            $alias = $this->getMainAlias($queryBuilder);
        }

        if (!isset($code)
            && \XLite\Logic\Import\Importer::getLanguageCode()
        ) {
            $code = \XLite\Logic\Import\Importer::getLanguageCode();
        }

        if (!isset($code)) {
            $code = $this->getTranslationCode();
        }

        return $this->addTranslationJoins($queryBuilder, $alias, $translationsAlias, $code);
    }

    /**
     * Add the specific joints with the translation table
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string                     $alias
     * @param string                     $translationsAlias
     * @param string                     $code
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function addTranslationJoins($queryBuilder, $alias, $translationsAlias, $code)
    {
        if ($code) {
            $queryBuilder
                ->leftJoin(
                    $alias . '.translations',
                    $translationsAlias,
                    \Doctrine\ORM\Query\Expr\Join::WITH,
                    $translationsAlias . '.code = :lng'
                )
                ->setParameter('lng', $code);
        }

        return $queryBuilder;
    }

    /**
     * Return current translation code
     *
     * @return string
     */
    protected function getTranslationCode()
    {
        $code = 'en';

        if (\XLite\Logic\Import\Importer::getLanguageCode()) {
            $code = \XLite\Logic\Import\Importer::getLanguageCode();

        } elseif (!\XLite::isCacheBuilding()
            && \XLite\Core\Session::getInstance()->getLanguage()
        ) {
            $code = \XLite\Core\Session::getInstance()->getLanguage()->getCode();
        }

        return $code;
    }

    /**
     * Define export iterator query builder
     *
     * @param integer $position Position
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function defineExportIteratorQueryBuilder($position)
    {
        return $this->createPureQueryBuilder()
            ->setFirstResult($position)
            ->setMaxResults(1000000000);
    }
}
