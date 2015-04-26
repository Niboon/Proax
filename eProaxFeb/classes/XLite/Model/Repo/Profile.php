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
 * The Profile model repository
 */
class Profile extends \XLite\Model\Repo\ARepo
{
    /**
     * Allowable search params
     */
    const SEARCH_PROFILE_ID     = 'profile_id';
    const SEARCH_ORDER_ID       = 'order_id';
    const SEARCH_REFERER        = 'referer';
    const SEARCH_MEMBERSHIP     = 'membership';
    const SEARCH_ROLES          = 'roles';
    const SEARCH_PERMISSIONS    = 'permissions';
    const SEARCH_LANGUAGE       = 'language';
    const SEARCH_PATTERN        = 'pattern';
    const SEARCH_LOGIN          = 'login';
    const SEARCH_PHONE          = 'phone';
    const SEARCH_COUNTRY        = 'country';
    const SEARCH_STATE          = 'state';
    const SEARCH_CUSTOM_STATE   = 'custom_state';
    const SEARCH_ADDRESS        = 'address';
    const SEARCH_USER_TYPE      = 'user_type';
    const SEARCH_DATE_TYPE      = 'date_type';
    const SEARCH_DATE_PERIOD    = 'date_period';
    const SEARCH_START_DATE     = 'startDate';
    const SEARCH_END_DATE       = 'endDate';
    const SEARCH_DATE_RANGE     = 'dateRange';
    const SEARCH_ORDERBY        = 'order_by';
    const SEARCH_LIMIT          = 'limit';
    const SEARCH_STATUS         = 'status';
    const SEARCH_ONLY_REAL      = 'onlyReal';

    /**
     * Password length
     */
    const PASSWORD_LENGTH = 12;

    /**
     * Default Recent administrators list length
     */
    const DEFAULT_RECENT_ADMINS_LENGTH = 8;

    /**
     * Repository type
     *
     * @var string
     */
    protected $type = self::TYPE_SERVICE;

    /**
     * currentSearchCnd
     *
     * @var \XLite\Core\CommonCell
     */
    protected $currentSearchCnd = null;

    /**
     * Password characters list
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
     * Common search
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Flag: return items list or only items count OPTIONAL
     *
     * @return \Doctrine\ORM\PersistentCollection|integer
     */
    public function search(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->leftJoin('p.addresses', 'addresses')
            ->leftJoin('addresses.country', 'country')
            ->leftJoin('addresses.state', 'state');

        $this->currentSearchCnd = $this->preprocessCnd($cnd);

        foreach ($this->currentSearchCnd as $key => $value) {
            if (!$countOnly || self::SEARCH_ORDERBY != $key) {
                $this->callSearchConditionHandler($value, $key, $queryBuilder);
            }
        }

        if (!$countOnly) {
            $queryBuilder->addGroupBy('p.profile_id');
        }

        return $countOnly
            ? $this->searchCount($queryBuilder)
            : $this->searchResult($queryBuilder);
    }

    /**
     * Search count only routine.
     *
     * @param \Doctrine\ORM\QueryBuilder $qb Query builder routine
     *
     * @return \Doctrine\ORM\PersistentCollection|integer
     */
    public function searchCount(\Doctrine\ORM\QueryBuilder $qb)
    {
        $qb->select('COUNT(DISTINCT p.profile_id)');

        return intval($qb->getSingleScalarResult());
    }

    /**
     * Search result routine.
     *
     * @param \Doctrine\ORM\QueryBuilder $qb Query builder routine
     *
     * @return \Doctrine\ORM\PersistentCollection|integer
     */
    public function searchResult(\Doctrine\ORM\QueryBuilder $qb)
    {
        return $qb->getOnlyEntities();
    }

    /**
     * Find profile by CMS identifiers
     *
     * @param array $fields CMS identifiers
     *
     * @return \XLite\Model\Profile|void
     */
    public function findOneByCMSId(array $fields)
    {
        return $this->defineFindOneByCMSIdQuery($fields)->getSingleResult();
    }

    /**
     * Search profile by login
     *
     * @param string $login User's login
     *
     * @return \XLite\Model\Profile
     */
    public function findByLogin($login)
    {
        return $this->findByLoginPassword($login);
    }

    /**
     * Search profile by login and password
     *
     * @param string  $login    User's login
     * @param string  $password User's password OPTIONAL
     * @param integer $orderId  Order ID related to the profile OPTIONAL
     *
     * @return \XLite\Model\Profile
     */
    public function findByLoginPassword($login, $password = null, $orderId = 0)
    {
        return $this->defineFindByLoginPasswordQuery($login, $password, $orderId)->getSingleResult();
    }

    /**
     * Find recently logged in administrators
     *
     * @param integer $length List length OPTIONAL
     *
     * @return array
     */
    public function findRecentAdmins($length = self::DEFAULT_RECENT_ADMINS_LENGTH)
    {
        return $this->defineFindRecentAdminsQuery($length)->getResult();
    }

    /**
     * Find user with same login
     *
     * @param \XLite\Model\Profile $profile Profile object
     *
     * @return \XLite\Model\Profile|void
     */
    public function findUserWithSameLogin(\XLite\Model\Profile $profile)
    {
        return $this->defineFindUserWithSameLoginQuery($profile)->getSingleResult();
    }

    /**
     * Find the count of administrator accounts
     *
     * @return integer
     */
    public function findCountOfAdminAccounts()
    {
        return intval($this->defineFindCountOfAdminAccountsQuery()->getSingleScalarResult());
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
        if (
            isset($data['login'])
            && (
                isset($data['order_id'])
                && 0 == $data['order_id']
                || 1 == count($data)
            )
        ) {
            $entity = $this->defineOneByRecord($data['login'])->getSingleResult();

        } else {
            $entity = parent::findOneByRecord($data, $parent);
        }

        return $entity;
    }

    /**
     * Find anonymous profile by another profile
     *
     * @param \XLite\Model\Profile $profile Profile
     *
     * @return \XLite\Model\Profile
     */
    public function findOneAnonymousByProfile(\XLite\Model\Profile $profile)
    {
        return $this->defineFindOneAnonymousByProfileQuery($profile)->getSingleResult();
    }

    /**
     * Iterate by customers 
     * 
     * @return \Iterator
     */
    public function iterateByCustomers()
    {
        return $this->defineIterateByCustomersQuery()->iterate();
    }

    /**
     * Generate password
     *
     * @return string
     */
    public function generatePassword()
    {
        return \XLite\Core\Operator::getInstance()->generateToken(static::PASSWORD_LENGTH, $this->chars);
    }

    /**
     * Preprocess condition. Order id must be placed into condition in any case.
     *
     * @param \XLite\Core\CommonCell $cnd Condition
     *
     * @return \XLite\Core\CommonCell
     */
    protected function preprocessCnd(\XLite\Core\CommonCell $cnd)
    {
        if (!$cnd->{static::SEARCH_ORDER_ID}) {
            $cnd->{static::SEARCH_ORDER_ID} = 0;
        }

        return $cnd;
    }

    /**
     * Return list of handling search params
     *
     * @return array
     */
    protected function getHandlingSearchParams()
    {
        return array(
            static::SEARCH_PROFILE_ID,
            static::SEARCH_ORDER_ID,
            static::SEARCH_REFERER,
            static::SEARCH_MEMBERSHIP,
            static::SEARCH_PERMISSIONS,
            static::SEARCH_ROLES,
            static::SEARCH_LANGUAGE,
            static::SEARCH_PATTERN,
            static::SEARCH_LOGIN,
            static::SEARCH_PHONE,
            static::SEARCH_COUNTRY,
            static::SEARCH_STATE,
            static::SEARCH_CUSTOM_STATE,
            static::SEARCH_ADDRESS,
            static::SEARCH_USER_TYPE,
            static::SEARCH_DATE_TYPE,
            static::SEARCH_ORDERBY,
            static::SEARCH_LIMIT,
            static::SEARCH_STATUS,
        );
    }

    /**
     * Check if param can be used for search
     *
     * @param string $param Name of param to check
     *
     * @return boolean
     */
    protected function isSearchParamHasHandler($param)
    {
        return in_array($param, $this->getHandlingSearchParams());
    }

    /**
     * Call corresponded method to handle a search condition
     *
     * @param mixed                      $value        Condition data
     * @param string                     $key          Condition name
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     *
     * @return void
     */
    protected function callSearchConditionHandler($value, $key, \Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        if ($this->isSearchParamHasHandler($key)) {
            $methodName = 'prepareCnd' . \XLite\Core\Converter::getInstance()->convertToCamelCase($key);

            // Call method for preparing param condition
            $this->$methodName($queryBuilder, $value);

        } else {

            // TODO - add logging here
        }
    }

    /**
     * prepareCndProfileId
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param mixed                      $value        Searchable value
     *
     * @return void
     */
    protected function prepareCndProfileId(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $queryBuilder->bindAndCondition('p.profile_id', $value);
    }

    /**
     * prepareCndOrderId
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param mixed                      $value        Searchable value
     *
     * @return void
     */
    protected function prepareCndOrderId(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if ($value) {
            $queryBuilder->bindOrder($value);
        } else {
            $queryBuilder->bindVisible();
        }
    }

    /**
     * prepareCndReferer
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param mixed                      $value        Searchable value
     *
     * @return void
     */
    protected function prepareCndReferer(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $queryBuilder->bindAndCondition('p.referer', '%' . $value . '%', 'LIKE');
    }

    /**
     * prepareCndMembership
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param mixed                      $value        Searchable value
     *
     * @return void
     */
    protected function prepareCndMembership(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if ('A' !== $this->currentSearchCnd->{self::SEARCH_USER_TYPE}) {
            $queryBuilder->bindMembership($value);
        }
    }

    /**
     * Search condition by role(s)
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param mixed                      $value        Searchable value
     *
     * @return void
     */
    protected function prepareCndRoles(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if (!empty($value)) {
            $queryBuilder->bindRoles($value);
        }
    }

    /**
     * Search condition by permission(s)
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param mixed                      $value        Searchable value
     *
     * @return void
     */
    protected function prepareCndPermissions(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if (!empty($value)) {
            $queryBuilder->bindPermissions($value);
        }
    }

    /**
     * prepareCndLanguage
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param mixed                      $value        Searchable value
     *
     * @return void
     */
    protected function prepareCndLanguage(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $queryBuilder->bindAndCondition('p.language', $value);
    }

    /**
     * prepareCndPattern
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param mixed                      $value        Searchable value
     *
     * @return void
     */
    protected function prepareCndPattern(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $queryBuilder->bindPattern($value);
    }

    /**
     * prepareCndPhone
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param mixed                      $value        Searchable value
     *
     * @return void
     */
    protected function prepareCndPhone(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $queryBuilder->bindFieldAndCondition('name', '%' . $value . '%', 'LIKE');
    }

    /**
     * prepareCndLogin
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param mixed                      $value        Searchable value
     *
     * @return void
     */
    protected function prepareCndLogin(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $queryBuilder->bindOrCondition('p.login', '%' . $value . '%', 'LIKE');
    }

    /**
     * prepareCndCountry
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param mixed                      $value        Searchable value
     *
     * @return void
     */
    protected function prepareCndCountry(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $queryBuilder->bindAndCondition('country.code', $value);
    }

    /**
     * prepareCndState
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param mixed                      $value        Searchable value
     *
     * @return void
     */
    protected function prepareCndState(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if (!empty($this->currentSearchCnd->{static::SEARCH_COUNTRY})) {
            $queryBuilder->bindAndCondition('state.state_id', $value);
        }
    }

    /**
     * prepareCndCustomState
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param mixed                      $value        Searchable value
     *
     * @return void
     */
    protected function prepareCndCustomState(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if (!empty($this->currentSearchCnd->{static::SEARCH_COUNTRY})) {
            $queryBuilder->bindFieldAndCondition('custom_state', $value);
        }
    }

    /**
     * prepareCndAddress
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param mixed                      $value        Searchable value
     *
     * @return void
     */
    protected function prepareCndAddress(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $queryBuilder->bindAddress($value);
    }

    /**
     * prepareCndUserType
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param mixed                      $value        Searchable value
     *
     * @return void
     */
    protected function prepareCndUserType(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $roles = array();

        if (!is_array($value)) {
            $value = array($value);
        }

        $condition = $queryBuilder->expr()->orX();

        foreach ($value as $selectedType) {
            if (is_numeric($selectedType)) {
                $roles[] = $selectedType;

            } elseif ('C' == $selectedType) {
                $condition->add(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->getRegisteredCondition(),
                        $queryBuilder->getCustomerCondition()
                    )
                );

                $queryBuilder->setParameter('anonymous', true)
                    ->setParameter('adminAccessLevel', \XLite\Core\Auth::getInstance()->getAdminAccessLevel());

            } elseif ('N' == $selectedType) {
                $condition->add(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->getAnonymousCondition(),
                        $queryBuilder->getCustomerCondition()
                    )
                );

                $queryBuilder->setParameter('anonymous', true)
                    ->setParameter('adminAccessLevel', \XLite\Core\Auth::getInstance()->getAdminAccessLevel());
            }
        }

        if ($roles) {
            $condition->add(
                $queryBuilder->expr()->andX(
                    $queryBuilder->getRegisteredCondition(),
                    $queryBuilder->getAdminCondition(),
                    $queryBuilder->getRolesCondition($roles)
                )
            );

            $queryBuilder->linkLeft('p.roles')
                ->setParameter('anonymous', true)
                ->setParameter('adminAccessLevel', \XLite\Core\Auth::getInstance()->getAdminAccessLevel());
        }

        if ($condition->count()) {
            $queryBuilder->andWhere($condition);
        }
    }

    /**
     * prepareCndDateType
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param mixed                      $value        Searchable value
     *
     * @return void
     */
    protected function prepareCndDateType(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $dateRange = $this->getDateRange();

        if (isset($dateRange) && in_array($value, array('R', 'L'))) {
            $field = 'R' == $value ? 'p.added' : 'p.last_login';
            $queryBuilder->bindMacroDate($field, $dateRange->startDate, $dateRange->endDate);
        }
    }

    /**
     * getDateRange
     *
     * :FIXME: simplify
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getDateRange()
    {
        $result = null;

        $paramDatePeriod = self::SEARCH_DATE_PERIOD;

        if (isset($this->currentSearchCnd->$paramDatePeriod)) {

            $endDate = \XLite\Core\Converter::time();

            if ('M' == $this->currentSearchCnd->$paramDatePeriod) {

                $startDate = mktime(0, 0, 0, date('n', $endDate), 1, date('Y', $endDate));

            } elseif ('W' == $this->currentSearchCnd->$paramDatePeriod) {

                $startDay = $endDate - (date('w', $endDate) * 86400);

                $startDate = mktime(0, 0, 0, date('n', $startDay), date('j', $startDay), date('Y', $startDay));

            } elseif ('D' == $this->currentSearchCnd->$paramDatePeriod) {

                $startDate = mktime(0, 0, 0, date('n', $endDate), date('j', $endDate), date('Y', $endDate));

            } elseif ('C' == $this->currentSearchCnd->$paramDatePeriod) {

                $paramStartDate = static::SEARCH_START_DATE;
                $paramEndDate = static::SEARCH_END_DATE;
                $paramDateRange = static::SEARCH_DATE_RANGE;

                if (
                    !empty($this->currentSearchCnd->$paramStartDate)
                    && !empty($this->currentSearchCnd->$paramEndDate)
                ) {

                    $tmpDate = strtotime($this->currentSearchCnd->$paramStartDate);

                    if (false !== $tmpDate) {
                        $startDate = mktime(0, 0, 0, date('n', $tmpDate), date('j', $tmpDate), date('Y', $tmpDate));
                    }

                    $tmpDate = strtotime($this->currentSearchCnd->$paramEndDate);

                    if (false !== $tmpDate) {
                        $endDate = mktime(23, 59, 59, date('n', $tmpDate), date('j', $tmpDate), date('Y', $tmpDate));
                    }

                } elseif (!empty($this->currentSearchCnd->$paramDateRange)) {

                    list($startDate, $endDate) = \XLite\View\FormField\Input\Text\DateRange::convertToArray(
                        $this->currentSearchCnd->$paramDateRange
                    );
                }

            }

            if (
                isset($startDate)
                && false !== $startDate
                && false !== $endDate
            ) {
                $result = new \XLite\Core\CommonCell();
                $result->startDate = $startDate;
                $result->endDate = $endDate;
            }
        }

        return $result;
    }

    /**
     * Prepare fields for fullname value (for 'order by')
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder object
     * @param string                     $fieldName    Field name
     *
     * @return void
     */
    protected function prepareOrderByAddressField(\Doctrine\ORM\QueryBuilder $queryBuilder, $fieldName)
    {
        $addressField = \XLite\Core\Database::getRepo('XLite\Model\AddressField')
            ->findOneBy(array('serviceName' => $fieldName));

        $queryBuilder->leftJoin(
            'addresses.addressFields',
            'orderby_field_value_' . $fieldName,
            \Doctrine\ORM\Query\Expr\Join::WITH,
            'orderby_field_value_' . $fieldName . '.addressField = :' . $fieldName
        )->setParameter($fieldName, $addressField);
    }

    /**
     * Generate fullname by firstname and lastname values
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     *
     * @return void
     */
    protected function prepareCndOrderByFullname(\Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        $this->prepareOrderByAddressField($queryBuilder, 'firstname');
        $this->prepareOrderByAddressField($queryBuilder, 'lastname');

        $queryBuilder->addSelect(
            'CONCAT(CONCAT(orderby_field_value_firstname.value, \' \'),
            orderby_field_value_lastname.value) as fullname'
        );
    }

    /**
     * prepareCndOrderBy
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param array                      $value        Searchable value
     *
     * @return void
     */
    protected function prepareCndOrderBy(\Doctrine\ORM\QueryBuilder $queryBuilder, array $value)
    {
        list($sort, $order) = $this->getSortOrderValue($value);

        if (\XLite\View\ItemsList\Model\Profile::SORT_BY_MODE_NAME == $sort) {
            $this->prepareCndOrderByFullname($queryBuilder);
        }

        $queryBuilder->addOrderBy($sort, $order);
    }

    /**
     * prepareCndLimit
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param mixed                      $value        Searchable value
     *
     * @return void
     */
    protected function prepareCndLimit(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $queryBuilder->setFrameResults($value[0], $value[1]);
    }

    /**
     * Prepare 'status' condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param string                     $value        Searchable value
     *
     * @return void
     */
    protected function prepareCndStatus(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if (!empty($value)) {
            $queryBuilder->bindAndCondition('p.status', $value);
        }
    }

    /**
     * Prepare 'onlyReal' condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param string                     $value        Searchable value
     *
     * @return void
     */
    protected function prepareCndOnlyReal(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if (!empty($value)) {
            $queryBuilder->bindVisible();
        }
    }

    /**
     * Define query for findRecentAdmins() method
     *
     * @param integer $length List length OPTIONAL
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineFindRecentAdminsQuery($length)
    {
        return $this->createQueryBuilder()
            ->bindAdmin()
            ->bindLogged()
            ->addOrderBy('p.last_login')
            ->setMaxResults($length);
    }

    /**
     * Define query for findUserWithSameLogin() method
     *
     * @param \XLite\Model\Profile $profile Profile object
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineFindUserWithSameLoginQuery(\XLite\Model\Profile $profile)
    {
        $queryBuilder = $this->createQueryBuilder()
            ->bindSameLogin($profile);

        if ($profile->getOrder()) {
            $queryBuilder->bindOrder($profile->getOrder());

        } else {
            $queryBuilder->bindRegistered();
        }

        return $queryBuilder;
    }

    /**
     * Define query for findCountOfAdminAccounts()
     *
     * @return \Doctrine\ORM\PersistentCollection
     */
    protected function defineFindCountOfAdminAccountsQuery()
    {
        return $this->createQueryBuilder()
            ->selectCount()
            ->bindAdmin()
            ->bindAndCondition('p.status', \XLite\Model\Profile::STATUS_ENABLED);
    }

    /**
     * Define query for findOneByCMSId()
     *
     * @param array $fields Fields
     *
     * @return \Doctrine\ORM\PersistentCollection
     */
    protected function defineFindOneByCMSIdQuery(array $fields)
    {
        return $this->createQueryBuilder()
            ->bindVisible()
            ->mapAndConditions($fields);
    }

    /**
     * Define query for findByLoginPassword() method
     *
     * @param string  $login    User's login
     * @param string  $password User's password
     * @param integer $orderId  Order ID related to the profile OPTIONAL
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineFindByLoginPasswordQuery($login, $password, $orderId)
    {
        $conditions = array(
            'login'  => $login,
            'status' => \XLite\Model\Profile::STATUS_ENABLED,
        );

        $queryBuilder = $this->createQueryBuilder();

        if (isset($password)) {
            $conditions['password'] = $password;
        }

        if ($orderId) {
            $queryBuilder->bindOrder($orderId);
        } else {
            $queryBuilder->bindRegistered();
        }

        return $queryBuilder->mapAndConditions($conditions);
    }

    /**
     * Collect alternative identifiers by record
     *
     * @param array $data Record
     *
     * @return boolean|array(mixed)
     */
    protected function collectAlternativeIdentifiersByRecord(array $data)
    {
        $indetifiers = parent::collectAlternativeIdentifiersByRecord($data);
        if (
            !$indetifiers
            && !empty($data['login'])
            && isset($data['order_id'])
            && !$data['order_id']
        ) {
            $indetifiers = array(
                'login' => $data['login'],
                'order' => null,
            );
        }

        return $indetifiers;
    }

    /**
     * Link loaded entity to parent object
     *
     * @param \XLite\Model\AEntity $entity      Loaded entity
     * @param \XLite\Model\AEntity $parent      Entity parent callback
     * @param array                $parentAssoc Entity mapped propery method
     *
     * @return void
     */
    protected function linkLoadedEntity(\XLite\Model\AEntity $entity, \XLite\Model\AEntity $parent, array $parentAssoc)
    {
        if (
            $parent instanceof \XLite\Model\Order
            && !$parentAssoc['mappedSetter']
            && 'setProfile' == $parentAssoc['setter']
        ) {
            // Add order to profile if this profile - copy of original profile
            $parentAssoc['mappedSetter'] = 'setOrder';
        }

        parent::linkLoadedEntity($entity, $parent, $parentAssoc);
    }

    /**
     * Define query for findOneByRecord () method
     *
     * @param string $login Login
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineOneByRecord($login)
    {
        return $this->createQueryBuilder()
            ->bindCustomer()
            ->bindAndCondition('p.login', $login);
    }

    /**
     * Define query for findOneAnonymousByProfile() method
     *
     * @param \XLite\Model\Profile $profile Profile
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function defineFindOneAnonymousByProfileQuery(\XLite\Model\Profile $profile)
    {
        return $this->createQueryBuilder()
            ->bindAnonymous()
            ->bindAndCondition('p.login', $profile->getLogin());
    }

    /**
     * Define query for iterateByCustomers() method
     * 
     * @return \XLite\Model\QueryBuilder\AQueryBuiilder
     */
    protected function defineIterateByCustomersQuery()
    {
        return $this->createQueryBuilder()
            ->bindCustomer();
    }

    // {{{ Export routines

    /**
     * Define query builder for COUNT query
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function defineCountForExportQuery()
    {
        return parent::defineCountForExportQuery()
            ->bindCustomer();
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
        return parent::defineExportIteratorQueryBuilder($position)
            ->bindCustomer();
    }

    // }}}

    // {{{ Import

    /**
     * Define import query builder
     *
     * @param array $conditions Conditions
     *
     * @return \XLite\Model\QueryBuilder\AQueryBuilder
     */
    protected function defineFindOneByImportConditionsQueryBuilder(array $conditions)
    {
        return parent::defineFindOneByImportConditionsQueryBuilder($conditions)
            ->bindCustomer();
    }

    // }}}

}
