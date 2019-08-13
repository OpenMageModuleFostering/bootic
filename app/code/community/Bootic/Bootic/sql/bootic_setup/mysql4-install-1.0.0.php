<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

$installer = $this;

//$installer->installEntities();

$installer->startSetup();
$installer->run("
  DROP TABLE IF EXISTS {$this->getTable('bootic_log')};
  CREATE TABLE {$this->getTable('bootic_log')} (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT(11) NOT NULL,
    `date` DATETIME NULL,
    `status` VARCHAR(255) NOT NULL,
    `message` VARCHAR(2550) NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  DROP TABLE IF EXISTS {$this->getTable('bootic_category')};
  CREATE TABLE {$this->getTable('bootic_category')}
  (
  `category_id` SMALLINT(6) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `creation_time` TIMESTAMP,
  `update_time` TIMESTAMP,
  PRIMARY KEY (`category_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  DROP TABLE IF EXISTS {$this->getTable('bootic_category_parent')};
  CREATE TABLE {$this->getTable('bootic_category_parent')}
  (
  `category_id` SMALLINT(6) NOT NULL,
  `parent_id` SMALLINT(6) NOT NULL,
  PRIMARY KEY (`category_id`, `parent_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  DROP TABLE IF EXISTS {$this->getTable('bootic_category_mapping')};
  CREATE TABLE {$this->getTable('bootic_category_mapping')}
  (
  `magento_category_id` SMALLINT(6) NOT NULL,
  `bootic_category_id` SMALLINT(6) NOT NULL,
  PRIMARY KEY (`magento_category_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  DROP TABLE IF EXISTS {$this->getTable('bootic_product_data')};
  CREATE TABLE {$this->getTable('bootic_product_data')}
  (
  `magento_product_id` INT(11) NOT NULL,
  `bootic_product_id` INT(11),
  `bootic_stock_id` varchar(50),
  `bootic_status` SMALLINT(1),
  `is_info_synced` TINYINT(1) unsigned NOT NULL default '0',
  `is_stock_synced` TINYINT(1) unsigned NOT NULL default '0',
  `update_time` TIMESTAMP,
  `creation_time` TIMESTAMP,
  `upload_failures` SMALLINT(1),
  PRIMARY KEY (`magento_product_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  DROP TABLE IF EXISTS {$this->getTable('bootic_message')};
  CREATE TABLE {$this->getTable('bootic_message')}
  (
  `magento_message_id` int(10) NOT NULL,
  `bootic_message_id` int(10) NOT NULL,
  PRIMARY KEY (`magento_message_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  
  DROP TABLE IF EXISTS {$this->getTable('bootic_order_data')};
  CREATE TABLE {$this->getTable('bootic_order_data')}
  (
  `order_id` int(10) NOT NULL,
  `bootic_order_id` int(10) NOT NULL,
  `transactions` text NOT NULL,
  `in_sync` SMALLINT(1) DEFAULT 0,
  `last_status` varchar(10),
  `updated_at` timestamp NULL DEFAULT NULL ,
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `UNQ_BOOTIC_ORDER_ID` (`bootic_order_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  
");

$installer->endSetup();
