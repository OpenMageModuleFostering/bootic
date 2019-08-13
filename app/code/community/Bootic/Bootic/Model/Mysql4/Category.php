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
    public function lookupParentIds($id)
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
    public function lookupChildrenIds($id)
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
}
