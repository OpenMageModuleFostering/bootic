<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Helper_Category extends Bootic_Bootic_Helper_Abstract
{
    /** @var Array */
    protected $_booticCategories;

    /**
     * Lists all original categories on Bootic
     *
     * @param int $safe
     * @param bool $empty
     * @return array|mixed
     */
    public function listOrigCategories()
    {
        return $this->getBootic()->listCategories();
    }

    /**
     * @return Array
     */
    public function getBooticCategories()
    {
        if (isset($this->_booticCategories)) {
            return $this->_booticCategories;
        }

        $booticCategoryCollection = Mage::getResourceModel('bootic/category_collection')->load();

        $booticCategories = array();
        foreach ($booticCategoryCollection as $booticCategory) {
            $booticCategories[$booticCategory->getId()] = Mage::getModel('bootic/category')->load($booticCategory->getId());
        }

        $this->_booticCategories = $booticCategories;

        return $this->_booticCategories;
    }

    public function getJsonFormattedBooticCategories()
    {
        $origBooticCategories = $this->getBooticCategories();

        $tree = $this->_buildBooticCategoryTree($origBooticCategories);

        foreach ($tree as &$subTree) {
            $temp = array();
            $temp['id'] = $subTree['category_id'];
            $temp['label'] = $subTree['name'];
            if (count($subTree['children']) > 0) {
                $flatTree = $this->_flatenBooticTree($subTree);

                usort($flatTree, function($a, $b) {
                    if ($a['label'] < $b['label']) {
                        return -1;
                    } else {
                        return 1;
                    }
                });

                $temp['children'] = $flatTree;
            }

            $subTree = $temp;
        }

        return Zend_Json::encode($tree);
    }

    /**
     * @param $categories
     * @param $level
     * @return array
     */
    private function _flatenBooticTree($tree, $firstLevel = true, $parentLabel = null)
    {
        $result = array();

        $preLabel = ($parentLabel) ? $parentLabel : $tree['name'];

        foreach ($tree['children'] as $key => $category) {
            $label = $firstLevel ? $category['name'] : $preLabel . ' -> ' . $category['name'];
            $result[$key] = array(
                'id'    => $category['category_id'],
                'label' => $label
            );

            if (count($category['children']) > 0) {
                $flatTree = $this->_flatenBooticTree($category, false, $label);
                foreach($flatTree as $k => $v)
                {
                    $result[$k] = $v;
                }
            }
        }

        return $result;
    }

    private function _buildBooticCategoryTree($categories)
    {
        $tree = array();
        foreach ($categories as $key => &$category) {
            $children = array();
            foreach ($category->getChildren() as $childrenId) {
                $children[$childrenId] = $categories[$childrenId];
            }

            $category->setChildren($children);

            if (count($category->getChildren()) > 0 && count($category->getParents()) == 0) {
                $tree[$key] = $category;
            }
        }

        return $this->_extractData($tree);
    }

    private function _extractData($tree)
    {
        if (is_array($tree)) {
            foreach ($tree as &$item) {
                $item = $item->getData();

                if (count($item['children']) > 0) {
                    foreach ($item['children'] as &$child) {
                        $child = $this->_extractData($child);
                    }
                }
            }
        } else {
            $tree = $tree->getData();

            if (count($tree['children']) > 0) {
                foreach ($tree['children'] as &$child) {
                    $child = $this->_extractData($child);
                }
            }
        }

        return $tree;
    }

    /**
     * Gets Selected Magento Categories
     *
     * @return array
     */
    public function getMagentoCategories()
    {
        try {
            $rootCategory = $this->getRootCategory();
            return $this->_createCategoryTree($rootCategory);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Gets the selected root category from config
     *
     * @return Mage_Core_Model_Abstract
     */
    public function getRootCategory()
    {
        $rootCategoryId = Mage::getStoreConfig('bootic/product/root_category');
        if ($rootCategoryId) {
            return Mage::getModel('catalog/category')->load($rootCategoryId);
        } else {
            Mage::throwException('Root category is not set in system/configuration/bootic');
        }
    }

    /**
     * Creates Category Tree from selected root category
     *
     * @param $rootCategory
     * @return array
     */
    private function _createCategoryTree($rootCategory)
    {
        $tree = Mage::getResourceSingleton('catalog/category_tree')->load();
        $root = $tree->getNodeById($rootCategory->getId());

        $collection = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('is_active')
            ->addAttributeToSelect('name');

        $tree->addCollectionData($collection, true);

        $arrayTree =  $this->_nodeToArray($root);

        return $this->_flatenTreeArray($arrayTree);
    }

    /**
     * Create the Tree array
     *
     * @param Varien_Data_Tree_Node $node
     * @return array
     */
    private function _nodeToArray(Varien_Data_Tree_Node $node)
    {
        $result = array();
        $result['category_id'] = $node->getId();
        $result['parent_id'] = $node->getParentId();
        $result['name'] = $node->getName();
        $result['is_active'] = $node->getIsActive();
        $result['position'] = $node->getPosition();
        $result['level'] = $node->getLevel();
        $result['children'] = array();

        foreach ($node->getChildren() as $child) {
            $result['children'][] = $this->_nodeToArray($child);
        }
    
        return $result;
    }

    private function _flatenTreeArray(array $arrayTree)
    {
        $result = array();
        $arrow = ($arrayTree['level'] == 2) ? '' : '> ';

        if ($arrayTree['level'] != 1) {
            $result[] = array(
                'id'    => $arrayTree['category_id'],
                'value' => str_repeat("|-------", (int) ($arrayTree['level'] - 2)) . $arrow . $arrayTree['name']
            );
        }

        if (count($arrayTree['children']) > 0) {
            foreach ($arrayTree['children'] as $child) {
                $result = array_merge($result, $this->_flatenTreeArray($child));
            }
        }

        return $result;
    }

    /**
     * Gets categories from Bootic and stores them locally
     */
    public function pullBooticCategories()
    {
        $result = $this->listOrigCategories();
        $booticCategories = $result->getData();

        foreach ($booticCategories as $booticCategory) {
            $category = Mage::getModel('bootic/category')->load($booticCategory['id']);
            $category->setId($booticCategory['id']);
            $category->setName($booticCategory['name']);
            $category->setParents($booticCategory['parents']);
            $category->save();
        }
    }


    /**
     * Get collection of product categories with additional matching bootic field
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public function getMappedCategoryCollection($product)
    {
        $collection = Mage::getResourceModel('catalog/product')->getCategoryCollection($product)
            ->joinField('bootic_category_id',
                'bootic/category_mapping',
                'bootic_category_id',
                'magento_category_id=entity_id',
                null)
            ->addAttributeToSort('level', 'DESC');

        return $collection;
    }
}
