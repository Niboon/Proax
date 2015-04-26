<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\CustomCategoryFilter\Controller\Customer;

/**
 * Custom Category Filter controller
 */
class CustomCategoryFilter extends \XLite\Controller\Customer\Search
{

    const VERIFICATION_THRESHOLD = 5000;
    const ATTRIBUTES_VISIBLE_THRESHOLD = 3;
    const KEY_VALUE_DELIMITER = "qqq";
    const PAIR_DELIMITER = "vvv";
    const WILDCARD_CHAR = "*";

    /**
     * Check controller visibility
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible();
//        && 1 < $this->getCategory()->getProductsCount();
    }

    /**
     * Do action get filter attributes tree
     *
     * @return void
     */
    protected function doActionFilterAttributes()
    {
        $searchStr = trim( urldecode(\XLite\Core\Request::getInstance()->{"searchString"} ? : " "), "\"");
        $path = urldecode (\XLite\Core\Request::getInstance()->{"path"} ? : 'ROOT;');
        $oldAttribute = urldecode (\XLite\Core\Request::getInstance()->{"attribute"} ? : null);
        $categoryDepth = count(explode(';', rtrim($path, ';')));
        if ($categoryDepth <= self::ATTRIBUTES_VISIBLE_THRESHOLD) {
            echo json_encode([]);
            die();
        }
        $attributeName = urldecode(\XLite\Core\Request::getInstance()->{"id"});

        if ($attributeName) {
            if ($attributeName === '#') {
                $response = [];
                $response[] = [
                    "id" => "Attributes",
                    "text" => "<b>FILTER ATTRIBUTES</b>",
                    "children" => true,
                    'state' => [
                        'opened' => true,
                        'disabled' => true
                    ]
                ];
            } elseif ($attributeName === 'Attributes') {
                $cnd = new \XLite\Core\CommonCell();
                $cnd->{"substring"} = $searchStr;
                $cnd->{"categoryPath"} = $path;
                $cnd->{"case"} = "attributeNames";
                $attributeNames = \XLite\Core\Database::getRepo('XLite\Model\Product')->search($cnd);
                $response = [];
                foreach ($attributeNames as $attribute) {
                    if (is_string($attribute) && !empty($attribute)) {
                        $attribute = $this->getAttribute($path, $attribute, $searchStr, $oldAttribute);
                        if ($attribute) {
                            $response[] = $attribute;
                        }
                    }
                }
            } else {
                $products = $this->getAttributeValues($path, $searchStr, $attributeName, $oldAttribute);
                if (is_array($products)) {
                    foreach ($products as $product)
                        $response[] = $product;
                }
            }
        }
        if (isset($response))
            echo json_encode($response);

        die();
    }


    /**
     * @param $path
     * @param $attribute
     * @param $searchStr
     * @param $oldAttribute
     * @return array
     * @internal param $response
     */
    function getAttribute($path, $attribute, $searchStr, $oldAttribute)
    {
        $id = self::WILDCARD_CHAR . self::PAIR_DELIMITER . $attribute . self::KEY_VALUE_DELIMITER;
        $productCount = $this->getAttributeProductsCount($path, $searchStr, self::WILDCARD_CHAR . self::PAIR_DELIMITER . $attribute . self::KEY_VALUE_DELIMITER, $oldAttribute);
        if ($productCount > 0) {
            $text = $attribute . " <span style=\"color:#31c7f3\">({$productCount})</span>";

            $baseSearch = "?target=search&mode=search";
            $searchParam = "&substring=" . urlencode($searchStr);
            $pathParam = "&categoryPath=" . urlencode($path);
            $attrParam = "&attribute=\"" . urlencode($id) . "\"";
            $link = [ "href" => $baseSearch . $searchParam . $pathParam . $attrParam];

            $response = ["id" => urlencode($id), "text" => $text, "children" => true, "a_attr" => $link];
            return $response;
        } else {
            return null;
        }
    }

    /**
     * @param $path
     * @param $searchStr
     * @param $attributeName
     * @param $oldAttribute
     * @return array
     * @internal param $response
     */
    function getAttributeValues($path, $searchStr, $attributeName, $oldAttribute)
    {
        $cnd = new \XLite\Core\CommonCell();
        $cnd->{"substring"} = $searchStr;
        $cnd->{"categoryPath"} = $path;
        $cnd->{"attribute"} = $attributeName;
        $cnd->{"attribute"} .= $oldAttribute ? : null;
        $cnd->{"case"} = "attributeValues";
        $attributeValues = \XLite\Core\Database::getRepo('XLite\Model\Product')->search($cnd);
        $matchedAttributes = [];
        foreach ($attributeValues as $attribute) {
            if ((strpos($attribute, ltrim($attributeName, self::WILDCARD_CHAR . self::PAIR_DELIMITER))) !== FALSE) {
                $firstPos = strpos($attribute, self::KEY_VALUE_DELIMITER) + strlen(self::KEY_VALUE_DELIMITER);
                $temp = substr($attribute, $firstPos);
                $matchedAttributes[] = $temp;
            }
        }
        $attributeValues = array_unique($matchedAttributes);
        $response = [];
        if (!empty($attributeValues)) {
            foreach ($attributeValues as $matchedValue) {
                $id = $matchedValue;
                $productCount = $this->getAttributeProductsCount($path, $searchStr, $attributeName . $matchedValue . self::PAIR_DELIMITER . self::WILDCARD_CHAR, $oldAttribute);
                if ($productCount > 0) {
                    $text = $matchedValue . " <span style=\"color:#31c7f3\">({$productCount})</span>";

                    $baseSearch = "?target=search&mode=search";
                    $searchParam = "&substring=" . urlencode($searchStr);
                    $pathParam = "&categoryPath=" . urlencode($path);
                    $attrParam = "&attribute=\"" . urlencode( $attributeName . $matchedValue . self::WILDCARD_CHAR . self::PAIR_DELIMITER . "\"");
                    $link = [ "href" => $baseSearch . $searchParam . $pathParam . $attrParam];

                    $response[] = ["id" => urlencode( $attributeName . $id . self::PAIR_DELIMITER . self::WILDCARD_CHAR), "text" => $text, "a_attr" => $link];
                }
            }
        } else {
            $response = [];
        };
        return $response;
    }


    /**
     * @param $path
     * @param $searchStr
     * @param $attribute
     * @param $oldAttribute
     * @return int
     */
    function getAttributeProductsCount($path, $searchStr, $attribute, $oldAttribute)
    {
        $cnd = new \XLite\Core\CommonCell();
        $cnd->{"substring"} = $searchStr;
        $cnd->{"categoryPath"} = $path;
        $cnd->{"attribute"} = '"' . $attribute . '"';
        $cnd->{"attribute"} .= $oldAttribute ? $oldAttribute :  null;
        $cnd->{"case"} = "attributeProductsCount";
        $productCount = \XLite\Core\Database::getRepo('XLite\Model\Product')->search($cnd);

        return $productCount;
    }


    /**
     * Do action get filter tree
     *
     * @return void
     */
    protected function doActionFilterTree()
    {
        $searchStr = trim( urldecode(\XLite\Core\Request::getInstance()->{"searchString"} ? : " "), "\"");
        $path = urldecode (\XLite\Core\Request::getInstance()->{"path"});
        $attribute = urldecode (\XLite\Core\Request::getInstance()->{"attribute"});

        if ($path) {
            if ($path === '#') {
                $path = "ROOT;";
            }

            $productCount = $this->getCategoryProductsCount($path, $searchStr, $attribute);

            if ($productCount > 0) {
                $cnd = new \XLite\Core\CommonCell();
                $cnd->{"categoryPath"} = $path;
                $cnd->{"categoryDepth"} = count(explode(';', rtrim($path, ';'))) + 1;
                $cnd->{"case"} = "categoryNames";
                $categoryNames = \XLite\Core\Database::getRepo('XLite\Model\Product')->search($cnd);
                $response = [];
                foreach ($categoryNames as $category) {
                    if (is_string($category) && !empty($category)) {
                        $category = $this->getCategory($path, $category, $searchStr, $attribute);
                        if ($category) {
                            $response[] = $category;
                        }
                    }
                }
            }
        }
        if (!isset($response) || empty($response))
            $response = [];

        echo json_encode($response);
        die();
    }



    /**
     * @param $path
     * @return array
     * @internal param $response
     */
    function getProductPaths($path)
    {
        $cnd = new \XLite\Core\CommonCell();
        $cnd->{"categoryPath"} = $path;
        $cnd->{"case"} = "categoryProductsPaths";
        return \XLite\Core\Database::getRepo('XLite\Model\Product')->search($cnd);
    }


    /**
     * @param $path
     * @param $category
     * @param $searchStr
     * @param $attribute
     * @return array
     */
    function getCategory($path, $category, $searchStr, $attribute)
    {
        $id = $path . $category . ";";
        $productCount = $this->getCategoryProductsCount($id, $searchStr, $attribute);
        if ($productCount > 0) {

            if ($productCount < self::VERIFICATION_THRESHOLD) {
                if (!$this->verifyPaths($id)) {
                    return null;
                }
            }

            $text = $category . " <span style=\"color:#31c7f3\">({$productCount})</span>";

            $baseSearch = "?target=search&mode=search";
            $searchParam = "&substring=" . urlencode($searchStr);
            $pathParam = "&categoryPath=" . urlencode($id);
            $attributeParam = "&attribute=" . urlencode($attribute);
            $link = [ "href" => $baseSearch . $searchParam . $pathParam . $attributeParam];

            $response = ["id" => urlencode($id), "text" => $text, "children" => true, "a_attr" => $link];
            return $response;
        } else {
            return null;
        }
    }

    /**
     * @param $id
     * @return array
     */
    function verifyPaths($id)
    {
        $paths = $this->getProductPaths($id);
        foreach ($paths as $ret) {
            if (!(stripos($ret, $id) !== FALSE)) {
                return FALSE;
            }
        }
        return true;
    }

    /**
     * @param $path
     * @param $searchStr
     * @param $attribute
     * @return int
     */
    function getCategoryProductsCount($path, $searchStr, $attribute)
    {
        $cnd = new \XLite\Core\CommonCell();
        $cnd->{"substring"} = $searchStr;
        $cnd->{"categoryPath"} = $path;
        $cnd->{"attribute"} = $attribute;
        $cnd->{"case"} = "categoryProductsCount";
        $productCount = \XLite\Core\Database::getRepo('XLite\Model\Product')->search($cnd);

        return $productCount;
    }

}
