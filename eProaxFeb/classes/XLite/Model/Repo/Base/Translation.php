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
 * Common translation repository
 */
class Translation extends \XLite\Model\Repo\ARepo
{
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
        if (empty($data['code'])) {
            $data['code'] = \XLite\Model\Base\Translation::DEFAULT_LANGUAGE;
        }

        return isset($parent) ? $parent->getTranslation($data['code']) : parent::findOneByRecord($data, $parent);
    }

    /**
     * Get repository type
     *
     * @return string
     */
    public function getRepoType()
    {
        return isset($this->_class->associationMappings['owner'])
            ? \XLite\Core\Database::getRepo($this->_class->associationMappings['owner']['targetEntity'])->getRepoType()
            : parent::getRepoType();
    }

    /**
     * Get used language codes 
     * 
     * @return array
     */
    public function getUsedLanguageCodes()
    {
        $result = array();

        foreach ($this->defineGetUsedLanguageCodesQuery()->getResult() as $row) {
            $result[] = $row['code'];
        }

        return $result;
    }

    /**
     * Define query for getUsedLanguageCodes() methods
     * 
     * @return void
     */
    protected function defineGetUsedLanguageCodesQuery()
    {
        $qb = $this->createQueryBuilder();

        return $qb->select('DISTINCT ' . $qb->getMainAlias() . '.code');
    }
}
