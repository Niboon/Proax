<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLite\Module\ProaxIT\CustomCategoryFilter\View;

/**
 * Form filter for Quick Orders
 *
 * @ListChild (list="sidebar.first", zone="customer", weight="10")
 * @ListChild (list="sidebar.single", zone="customer", weight="10")
 */
class Filter extends \XLite\View\SideBarBox
{
    /**
     * Get widget templates directory
     *
     * @return string
     */
    protected function getDir()
    {
        return 'modules/ProaxIT/CustomCategoryFilter/category_tree/';
    }

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'modules/ProaxIT/CustomCategoryFilter/category_tree/style.css';
        $list[] = 'modules/ProaxIT/CustomCategoryFilter/category_tree/dist/themes/default/style.css';
        return $list;
    }

    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = 'modules/ProaxIT/CustomCategoryFilter/category_tree/script.js';
        $list[] = 'modules/ProaxIT/CustomCategoryFilter/category_tree/dist/jstree.min.js';

        return $list;
    }

    /**
     * Get widget title
     *
     * @return string
     */
    protected function getHead()
    {
        return \XLite\Core\Translation::getInstance()->translate('Refine Search');
    }

    /**
     * Register the CSS classes for this block
     *
     * @return string
     */
    protected function getBlockClasses()
    {
        return parent::getBlockClasses() . ' custom-category-filter';
    }

    /**
     * Return list of targets allowed for this widget
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $result = parent::getAllowedTargets();
        $result[] = 'category';

        return $result;
    }

    /**
     * @inherit
     */
    protected function isVisible()
    {
        return parent::isVisible() ||
        $this->getTarget() == 'search'  ||
        $this->getTarget() == 'category';
    }


    /**
     * Get requested category path
     *
     * @return string
     */
    protected function getCategoryRoot()
    {
        $category = \XLite\Core\Database::getRepo('XLite\Model\Category')->find($this->getCategoryId());

        if ($category) {
            return "ROOT;" . $category->getName() . ";";
        } else {
            $search = \XLite\View\ItemsList\Product\Customer\Search::getInstance();
            return $search ? $search->get("categoryPath") : null;
        }
    }

    /**
     * Get requested category
     *
     * @return integer
     */
    protected function getCategoryId()
    {
        return \XLite\Core\Request::getInstance()->category_id;
    }

    /**
     * Get requested substring
     *
     * @return integer
     */
    protected function getSearchString()
    {
        $search = \XLite\View\ItemsList\Product\Customer\Search::getInstance();

        return !$this->getCategoryId() ? $search->get("substring") : null;
    }

    /**
     * Get requested substring
     *
     * @return integer
     */
    protected function getAttributeFilters()
    {
        $search = \XLite\View\ItemsList\Product\Customer\Search::getInstance();

        return $search->get("attribute");
    }

    /**
     * Get cache parameters
     *
     * @return array
     */
    protected function getCacheParameters()
    {
        $list = parent::getCacheParameters();
        $list[] = $this->getCategoryId();

        return $list;
    }

}
