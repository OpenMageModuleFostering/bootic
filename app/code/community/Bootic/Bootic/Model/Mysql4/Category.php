<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Model_Mysql4_Category extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_isPkAutoIncrement    = false;

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('bootic/category', 'category_id');
    }

    /**
     * Perform operations after object load
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Bootic_Bootic_Model_Resource_Category
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if ($object->getId() !== false) {
            $parents = $this->lookupParentIds($object->getId());
            $children = $this->lookupChildrenIds($object->getId());
            $object->setData('parents', $parents);
            $object->setData('children', $children);
        }

        return parent::_afterLoad($object);
    }

    /**
     * Process category data before deleting
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Bootic_Bootic_Model_Resource_Category
     */
    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        $condition = array(
            'category_id = ?' => (int)$object->getId(),
        );

        $this->_getWriteAdapter()->delete($this->getTable('bootic/category_parent'), $condition);

        return parent::_beforeDelete($object);
    }

    /**
     * Perform operations before object save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Bootic_Bootic_Model_Resource_Category|Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getId()) {
            $object->setCreationTime(Mage::getSingleton('core/date')->gmtDate());
        }
        $object->setUpdateTime(Mage::getSingleton('core/date')->gmtDate());
        return $this;
    }

    /**
     * Perform operations after object save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Bootic_Bootic_Model_Resource_Category
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $oldParents = $this->lookupParentIds($object->getId());
        $newParents = (array)$object->getParents();

        $table  = $this->getTable('bootic/category_parent');
        $insert = array_diff($newParents, $oldParents);
        $delete = array_diff($oldParents, $newParents);

        if ($delete) {
            $where = array(
                'category_id = ?'   => (int) $object->getId(),
                'parent_id IN (?)'  => $delete
            );

            $this->_getWriteAdapter()->delete($table, $where);
        }

        if ($insert) {
            $data = array();

            foreach ($insert as $parentId) {
                $data[] = array(
                    'category_id'   => (int) $object->getId(),
                    'parent_id'     => (int) $parentId
                );
            }


            $this->_getWriteAdapter()->insertMultiple($table, $data);
        }

        return parent::_afterSave($object);

    }

    /**
     * Get parent category ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    protected function lookupParentIds($id)
    {
        $adapter = $this->_getReadAdapter();

        $select  = $adapter->select()
            ->from($this->getTable('bootic/category_parent'), 'parent_id')
            ->where('category_id = :category_id');

        $binds = array(
            ':category_id' => (int) $id
        );

        return $adapter->fetchCol($select, $binds);
    }

    /**
     * Get children category ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    protected function lookupChildrenIds($id)
    {
        $adapter = $this->_getReadAdapter();

        $select  = $adapter->select()
            ->from($this->getTable('bootic/category_parent'), 'category_id')
            ->where('parent_id = :category_id');

        $binds = array(
            ':category_id' => (int) $id
        );

        return $adapter->fetchCol($select, $binds);
    }

    public function insertCategories(array $categories, DateTime $lastRemoteUpdate)
    {
        // We clear our 2 tables
        $this->_getWriteAdapter()->delete($this->getMainTable());
        $this->_getWriteAdapter()->delete($this->getTable('bootic/category_parent'));

        $insertCategories = array();
        $insertCategoryParents = array();
        foreach ($categories as $category) {
            $insertCategories[] = array(
                'category_id' => $category['id'],
                'name' => $category['name'],
                'update_time' => $lastRemoteUpdate->format('Y-m-d H:i:s')
            );

            if (count($category['parents']) > 0) {
                foreach ($category['parents'] as $key => $parent) {
                    $insertCategoryParents[] = array(
                        'category_id' => $category['id'],
                        'parent_id' => $parent
                    );
                }
            }
        }

        $this->_getWriteAdapter()->insertMultiple($this->getMainTable(), $insertCategories);
        $this->_getWriteAdapter()->insertMultiple($this->getTable('bootic/category_parent'), $insertCategoryParents);
    }

    public function getAllCategories()
    {
        $read = $this->_getReadAdapter();

        $results = $read->fetchAll("SELECT c.*, p.parent_id FROM bootic_category c LEFT JOIN bootic_category_parent p ON p.category_id = c.category_id");

        $categories = array();
        foreach ($results as $result) {
            if (array_key_exists($result['category_id'], $categories)) {
                $categories[$result['category_id']]['parents'][] = $result['parent_id'];
            } else {
                $categories[$result['category_id']] = array(
                    'category_id'   => $result['category_id'],
                    'name'          => $result['name'],
                    'parents'       => ($result['parent_id'] !== null) ? array($result['parent_id']) : array(),
                    'children'      => array()
                );
            }
        }

        // We populate the children
        foreach ($categories as $category) {
            if (count($category['parents']) > 0) {
                foreach ($category['parents'] as $parent) {
                    if (array_key_exists($parent, $categories)) {
                        $categories[$parent]['children'][] = $category['category_id'];
                    }
                }
            }
        }

        $booticCategories = array();
        foreach ($categories as $category) {
            $c = Mage::getModel('bootic/category');

            $c->setCategoryId($category['category_id']);
            $c->setName($category['name']);
            $c->setParents($category['parents']);
            $c->setChildren($category['children']);

            $booticCategories[$category['category_id']] = $c;
        }

        return $booticCategories;
    }

    public function getLocalLastUpdate()
    {
        return $this
            ->_getReadAdapter()
            ->fetchOne("SELECT c.update_time FROM bootic_category c ORDER BY c.update_time DESC")
        ;
    }
}
