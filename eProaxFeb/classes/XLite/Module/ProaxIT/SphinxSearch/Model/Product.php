<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\SphinxSearch\Model;

use Doctrine\ORM\Query\QueryException;
use mysqli;

class Product extends \XLite\Model\Repo\Product implements \XLite\Base\IDecorator
{
    const WILDCARD_CHAR = '*';
    const KEY_VALUE_DELIMITER = 'vvv';


    protected $itemsCount;
    /**
     * Common search
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return \Doctrine\ORM\PersistentCollection|integer
     */
    public function search(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        $mysqli = mysqli_connect('127.0.0.1:9306');
        if (!$mysqli) {
            die('Could not connect: ' . mysql_error());
        }

        try {
            if ($case = (is_string($cnd->case) ? $cnd->case : null)) {
                switch ($case) {
                    case "categoryNames":
                        $countOnly = false;
                        $categoryDepth = is_numeric($cnd->categoryDepth) ? $cnd->categoryDepth : 1;
                        $cnd->including = is_string($cnd->including) ? $cnd->including : "skip";
                        $cnd->attribute = false;
                        $cnd->substring = false;
                        $cnd->limit = 500000;
                        $matcher = $this->getMatcher($cnd, $countOnly);
                        $data = $this->getCategoryNames($cnd, $matcher, $mysqli, $categoryDepth);

                        break;
                    case "categoryProductsCount":
                        $countOnly = true;
                        $matcher = $this->getMatcher($cnd, $countOnly);

                        $cnd->{"table"} = "main";
                        $data = $this->getCount($matcher, $mysqli);
                        $mysqli->close();

                        break;
                    case "categoryProductsPaths":
                        $countOnly = false;
                        $matcher = $this->getMatcher($cnd, $countOnly);

                        $cnd->{"table"} = "main";
                        $cnd->limit = 500000;
                        $data = $this->getFullPaths($cnd, $matcher, $mysqli);

                        break;
                    case "attributeNames":
                        $countOnly = false;
                        $cnd->including = is_string($cnd->including) ? $cnd->including : "skip";
                        $cnd->attribute = false;
                        $cnd->substring = false;
                        $cnd->limit = 500000;
                        $matcher = $this->getMatcher($cnd, $countOnly);
                        $data = $this->getAttributeNames($cnd, $matcher, $mysqli);

                        break;
                    case "attributeValues":
                        $countOnly = false;
                        $matcher = $this->getMatcher($cnd, $countOnly);
                        $cnd->select = "description";
                        $cnd->limit = 500000;
                        $data = $this->getResult($cnd, $matcher,$mysqli);
                        $mysqli->close();

                        break;
                    case "attributeProductsCount":
                        $countOnly = true;
                        $matcher = $this->getMatcher($cnd, $countOnly);
                        $cnd->{"table"} = "main";
                        $data = $this->getCount($matcher, $mysqli);
                        $mysqli->close();

                        break;
                    default:
                        $cnd->including = is_string($cnd->including) ? $cnd->including : "skip";
                        $matcher = $this->getMatcher($cnd, $countOnly);

                        $data = $countOnly ? $this->getCount($matcher, $mysqli) : $this->getResult($cnd, $matcher,
                            $mysqli);
                        $mysqli->close();
                }
            } elseif (is_string($cnd->substring) || is_string($cnd->categoryPath) || is_string($cnd->attribute)) {
                $matcher = $this->getMatcher($cnd, $countOnly);

                $data = $countOnly ? $this->getCount($matcher, $mysqli) : $this->getResult($cnd, $matcher, $mysqli);
                $mysqli->close();
            } else {
                $data = parent::search($cnd, $countOnly);
            }
        } catch (Exception $e) {
            $data = parent::search($cnd, $countOnly);
        }
        return $data;
    }

    protected function escapeSphinxQL ( $string )
    {
        $from = array ( '&');
        $to   = array ( '\\\&');
        return str_replace ( $from, $to, $string );
    }

    /**
     * @param string $matcher
     * @param mysqli $mysqli
     * @return int
     */
    protected function getCount($matcher, $mysqli)
    {
        $limit = 1000000;

        $query = "SELECT count(*) as cnt FROM main WHERE MATCH({$matcher}) LIMIT {$limit} OPTION max_matches={$limit};";

        $result = $mysqli->query($query);

        while ($result !== false && $itemsCount = $result->fetch_array()) {
            $this->itemsCount = $itemsCount["cnt"];
        }
        if ($result !== false)
            $result->close();

        return $this->itemsCount;
    }

    /**
     * @param \XLite\Core\CommonCell $cnd Search condition
     * @param string $matcher
     * @param mysqli $mysqli
     * @return array
     * @throws QueryException
     */
    protected function getResult($cnd, $matcher, $mysqli)
    {
        $table = is_string($cnd->table) ? $cnd->table : 'main';
        $select = is_string($cnd->select) ? $cnd->select : 'id';
        $sortBy = is_string($cnd->sortBy) ? $cnd->sortBy : 'i.amount';
        $sortOrder = is_string($cnd->sortOrder) ? $cnd->sortOrder : 'desc';
        $limit = is_numeric($cnd->limit) ? $cnd->limit : 15;
        $pageId = is_numeric($cnd->pageId) ? $cnd->pageId : 1;
        $countMinus1 = ($this->itemsCount - 1) >= 0 ? ($this->itemsCount - 1) : 0;
        $offset = min(($limit * ($pageId - 1)), $countMinus1);
        $sphinx_matches = $limit + $offset;
        if ($table == "paths") {
            $query = "SELECT {$select} FROM {$table} WHERE MATCH({$matcher}) LIMIT 10000 OPTION max_matches=10000;";
            $result = $mysqli->query($query);

            if ($result) {
                $data = [];
                while ($row = $result->fetch_array()) {
                    $data[] = $row[$select];
                }
                $result->close();
            } else {
                throw new QueryException();
            }

            return $data;
        } else {
            $query = "SELECT {$select} FROM {$table} WHERE MATCH({$matcher})  ORDER BY {$sortBy} {$sortOrder} LIMIT {$offset},{$limit} OPTION max_matches={$sphinx_matches};";
            $result = $mysqli->query($query);

            if ($result && $select == "id") {
                $products = [];
                while ($product = $result->fetch_array()) {
                    $products[] = \XLite\Core\Database::getRepo('XLite\Model\Product')->find($product['id']);
                }
                $result->close();
                return $products;
            } elseif ($result && $select == "description") {
                $data = [];
                while ($row = $result->fetch_array()) {
                    $data[] = $row[$select];
                }

                $result->close();
                $attributeValues = [];
                foreach ($data as $values) {
                    $namesArr = explode(self::KEY_VALUE_DELIMITER, $values);
                    $attributeValues = array_merge($attributeValues, $namesArr);
                }
                $data = array_unique($attributeValues);
                return $data;
            } else {
                throw new QueryException();
            }
        }
    }

    /**
     * @param \XLite\Core\CommonCell $cnd Search condition
     * @param string $matcher
     * @param mysqli $mysqli
     * @return array
     * @throws QueryException
     */
    protected function getFullPaths($cnd, $matcher, $mysqli)
    {
        $table = is_string($cnd->table) ? $cnd->table : 'main';
        $query = "SELECT path FROM {$table} WHERE MATCH({$matcher}) LIMIT 10000 OPTION max_matches=10000;";
        $result = $mysqli->query($query);

        if ($result) {
            $paths = [];
            while ($path = $result->fetch_array()) {
                $paths[] = $path["path"];
            }
            $result->close();
        } else {
            throw new QueryException();
        }
        $mysqli->close();

        return $paths;
    }

    /**
     * @param $includingParam
     * @param $searchInput
     * @return string
     */
    protected function formatMatcherWithIncluding($includingParam, $searchInput)
    {
        if ($searchInput == '') {
            return "''";
        }
        switch ($includingParam) {
            case (\XLite\Model\Repo\Product::INCLUDING_ALL): {
                $booleanCondition = "&";
                $tempMatcher = str_replace(' ', $this::WILDCARD_CHAR . $booleanCondition . $this::WILDCARD_CHAR,
                    $this->cleanStr($searchInput));
                $matcher = $this::WILDCARD_CHAR . $tempMatcher . $this::WILDCARD_CHAR;
                break;
            }
            case (\XLite\Model\Repo\Product::INCLUDING_ANY): {
                $booleanCondition = "|";
                $tempMatcher = str_replace(' ', $this::WILDCARD_CHAR . $booleanCondition . $this::WILDCARD_CHAR,
                    $this->cleanStr($searchInput));
                $matcher = $this::WILDCARD_CHAR . $tempMatcher . $this::WILDCARD_CHAR;
                break;
            }
            case (\XLite\Model\Repo\Product::INCLUDING_PHRASE): {
                $matcher = '"' . $this->cleanStr($searchInput) . '"';
                break;
            }
            default : {
                $booleanCondition = "&";
                $tempMatcher = str_replace(' ', $this::WILDCARD_CHAR . $booleanCondition . $this::WILDCARD_CHAR,
                    $this->cleanStr($searchInput));
                $matcher = $this::WILDCARD_CHAR . $tempMatcher . $this::WILDCARD_CHAR;
                break;
            }
        };

        return "'" . $matcher . "'";
    }

    protected function cleanStr($str) {
        return trim($str);
//        return trim(preg_replace('/[^A-Za-z0-9\-]/', ' ', $str));
    }

    /**
     * @param \XLite\Core\CommonCell $cnd
     * @param $matcher
     * @param $mysqli
     * @param $categoryDepth
     * @return array
     * @throws QueryException
     */
    protected function getCategoryNames(\XLite\Core\CommonCell $cnd, $matcher, $mysqli, $categoryDepth)
    {
        $cnd->table = 'paths';
        $cnd->select = 'path';
        $data = $this->getResult($cnd, $matcher, $mysqli);
        $mysqli->close();
        $names = [];
        foreach ($data as $path) {
            $pathsArr = explode(';', $path);
            $names[] = $pathsArr[$categoryDepth - 1];
        }
        $data = array_unique($names);

        return $data;
    }

    /**
     * @param \XLite\Core\CommonCell $cnd
     * @param $matcher
     * @param $mysqli
     * @return array
     * @throws QueryException
     */
    protected function getAttributeNames(\XLite\Core\CommonCell $cnd, $matcher, $mysqli)
    {
        $cnd->table = 'paths';
        $cnd->select = 'attributes';
        $data = $this->getResult($cnd, $matcher, $mysqli);
        $mysqli->close();
        $attributeNames = [];
        foreach ($data as $attributes) {
            $namesArr = explode(self::KEY_VALUE_DELIMITER, $attributes);
            $attributeNames = array_merge($attributeNames, $namesArr);
        }
        $data = array_unique($attributeNames);
        return $data;
    }

    /**
     * @param $countOnly
     * @param $cnd
     * @param $searchStr
     * @return mixed|string
     */
    protected function handleSearchWithinCaching($countOnly, $cnd, $searchStr)
    {
        $searchWithin = is_bool($cnd->searchWithin) ? $cnd->searchWithin : (($cnd->searchWithin === "on") ? true : false);
        if ($searchWithin) {
            if ($countOnly) {
                $searchStr = \XLite\Core\Session::getInstance()->searchStr . " " . $searchStr;
                // Save searchStr in session for search within again
                \XLite\Core\Session::getInstance()->searchStr = $searchStr;

                return $searchStr;
            } else {
                $searchStr = \XLite\Core\Session::getInstance()->searchStr;

                return $searchStr;
            }
        } else {
            // Save matcher in session for search within
            \XLite\Core\Session::getInstance()->searchStr = $searchStr;

            return $searchStr;
        }
    }

    /**
     * @param \XLite\Core\CommonCell $cnd
     * @param $countOnly
     * @return string
     */
    protected function getMatcher(\XLite\Core\CommonCell $cnd, $countOnly)
    {
        if (is_string($cnd->attribute)) {
            $attribute = $this->escapeSphinxQL($cnd->attribute);
            $attribute = urldecode($attribute);
        } elseif ($cnd->attribute === false) {
            // No-Opt
        } else {
            $attribute = $this->escapeSphinxQL(\XLite\Core\Request::getInstance()->attribute);
            $attribute = urldecode($attribute);
        }
        $categoryPath = is_string($cnd->categoryPath) ? $this->escapeSphinxQL($cnd->categoryPath) : $this->escapeSphinxQL(\XLite\Core\Request::getInstance()->categoryPath);
        if ($searchStr = (is_string($cnd->substring) ? $cnd->substring : null)) {
            $searchStr = $this->handleSearchWithinCaching($countOnly, $cnd, $searchStr);
            $includingParam = is_string($cnd->including) ? $cnd->including : \XLite\Model\Repo\Product::INCLUDING_ALL;
            $formattedSearchStr = $includingParam === "skip" ? trim($searchStr, "'") : trim($this->formatMatcherWithIncluding($includingParam, $searchStr), "'");
        }
        $matcher = "'";
        $matcher .= !empty($formattedSearchStr) ? $formattedSearchStr : null;
        $matcher .= !empty($attribute) ? " @description({$attribute})" : null;
        $matcher .= !empty($categoryPath) ? " @path(\"{$categoryPath}\")" : null;
        $matcher .= "'";
        return $matcher;
    }
}