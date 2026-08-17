<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.6.5                                                       |
// +---------------------------------------------------------------------------+
// | mysql_install.php                                                        |
// |                                                                          |
// | MySQL database schema used to install the Store plugin.                  |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// |                                                                          |
// | This program is free software; you can redistribute it and/or            |
// | modify it under the terms of the GNU General Public License              |
// | as published by the Free Software Foundation; either version 2           |
// | of the License, or (at your option) any later version.                   |
// |                                                                          |
// | This program is distributed in the hope that it will be useful,          |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with this program; if not, write to the Free Software              |
// | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA                |
// | 02111-1307, USA.                                                         |
// |                                                                          |
// +---------------------------------------------------------------------------+

$_SQL[] = "
CREATE TABLE {$_TABLES['store_categories']} (
  id int(10) unsigned NOT NULL auto_increment,
  slug varchar(190) NOT NULL default '',
  name varchar(255) NOT NULL default '',
  description text NULL,
  active tinyint(1) NOT NULL default '1',
  created datetime NULL,
  modified datetime NULL,
  PRIMARY KEY (id),
  UNIQUE KEY store_category_slug (slug),
  KEY store_category_active (active)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['store_products']} (
  id int(10) unsigned NOT NULL auto_increment,
  category_id int(10) unsigned NOT NULL default '0',
  sku varchar(64) NOT NULL default '',
  slug varchar(190) NOT NULL default '',
  name varchar(255) NOT NULL default '',
  short_description text NULL,
  description mediumtext NULL,
  image_url varchar(1000) NOT NULL default '',
  price decimal(12,2) NOT NULL default '0.00',
  currency char(3) NOT NULL default 'EUR',
  stock int(11) NOT NULL default '0',
  product_type varchar(20) NOT NULL default 'physical',
  active tinyint(1) NOT NULL default '1',
  created datetime NULL,
  modified datetime NULL,
  PRIMARY KEY (id),
  UNIQUE KEY store_slug (slug),
  KEY store_sku (sku),
  KEY store_category (category_id),
  KEY store_active (active)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['store_product_images']} (
  id int(10) unsigned NOT NULL auto_increment,
  product_id int(10) unsigned NOT NULL,
  image_url varchar(1000) NOT NULL default '',
  is_primary tinyint(1) NOT NULL default '0',
  sort_order int(11) NOT NULL default '0',
  created datetime NULL,
  PRIMARY KEY (id),
  KEY store_image_product (product_id),
  KEY store_image_primary (product_id,is_primary)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['store_orders']} (
  id int(10) unsigned NOT NULL auto_increment,
  order_number varchar(32) NOT NULL default '',
  user_id int(10) unsigned NOT NULL default '0',
  customer_name varchar(255) NOT NULL default '',
  customer_email varchar(255) NOT NULL default '',
  address1 varchar(255) NOT NULL default '',
  address2 varchar(255) NOT NULL default '',
  postal_code varchar(32) NOT NULL default '',
  city varchar(190) NOT NULL default '',
  country varchar(190) NOT NULL default '',
  status varchar(20) NOT NULL default 'pending',
  payment_method varchar(32) NOT NULL default 'manual',
  payment_status varchar(20) NOT NULL default 'pending',
  payment_reference varchar(255) NOT NULL default '',
  paid_at datetime NULL,
  currency char(3) NOT NULL default 'EUR',
  total decimal(12,2) NOT NULL default '0.00',
  stock_released tinyint(1) NOT NULL default '0',
  order_source varchar(20) NOT NULL default 'online',
  created datetime NULL,
  modified datetime NULL,
  PRIMARY KEY (id),
  UNIQUE KEY store_order_number (order_number),
  KEY store_order_user (user_id),
  KEY store_order_status (status),
  KEY store_order_source (order_source),
  KEY store_order_created (created)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['store_order_items']} (
  id int(10) unsigned NOT NULL auto_increment,
  order_id int(10) unsigned NOT NULL,
  product_id int(10) unsigned NOT NULL default '0',
  sku varchar(64) NOT NULL default '',
  name varchar(255) NOT NULL default '',
  product_type varchar(20) NOT NULL default 'physical',
  unit_price decimal(12,2) NOT NULL default '0.00',
  quantity int(10) unsigned NOT NULL default '1',
  line_total decimal(12,2) NOT NULL default '0.00',
  PRIMARY KEY (id),
  KEY store_item_order (order_id),
  KEY store_item_product (product_id)
) ENGINE=MyISAM
";


$_SQL[] = "
CREATE TABLE {$_TABLES['store_payments']} (
  id int(10) unsigned NOT NULL auto_increment,
  order_id int(10) unsigned NOT NULL,
  provider varchar(32) NOT NULL default 'manual',
  status varchar(20) NOT NULL default 'pending',
  amount decimal(12,2) NOT NULL default '0.00',
  currency char(3) NOT NULL default 'EUR',
  reference varchar(255) NOT NULL default '',
  transaction_id varchar(255) NOT NULL default '',
  details text NULL,
  created datetime NULL,
  modified datetime NULL,
  paid_at datetime NULL,
  PRIMARY KEY (id),
  KEY store_payment_order (order_id),
  KEY store_payment_status (status),
  KEY store_payment_provider (provider)
) ENGINE=MyISAM
";
