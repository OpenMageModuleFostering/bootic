<?php

/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

$installer = $this;

$installer->startSetup();
$installer->run("

  ALTER TABLE {$this->getTable('bootic_category')} MODIFY category_id BIGINT(22) UNSIGNED;

  ALTER TABLE {$this->getTable('bootic_category_mapping')} MODIFY magento_category_id BIGINT(22) UNSIGNED;
  ALTER TABLE {$this->getTable('bootic_category_mapping')} MODIFY bootic_category_id BIGINT(22) UNSIGNED;

  ALTER TABLE {$this->getTable('bootic_category_parent')} MODIFY category_id BIGINT(22) UNSIGNED;
  ALTER TABLE {$this->getTable('bootic_category_parent')} MODIFY parent_id BIGINT(22) UNSIGNED;

");

$installer->endSetup();