<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.0                                                       |
// +---------------------------------------------------------------------------+
// | mysql_install.php                                                        |
// |                                                                           |
// | MySQL database schema used to install the Store plugin.                   |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
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
CREATE TABLE {$_TABLES['store_tax_classes']} (
  id int(10) unsigned NOT NULL auto_increment,
  code varchar(64) NOT NULL default '',
  name varchar(190) NOT NULL default '',
  active tinyint(1) NOT NULL default '1',
  created datetime NULL,
  modified datetime NULL,
  PRIMARY KEY (id),
  UNIQUE KEY store_tax_class_code (code),
  KEY store_tax_class_active (active)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['store_tax_zones']} (
  id int(10) unsigned NOT NULL auto_increment,
  name varchar(190) NOT NULL default '',
  active tinyint(1) NOT NULL default '1',
  priority int(11) NOT NULL default '100',
  created datetime NULL,
  modified datetime NULL,
  PRIMARY KEY (id),
  KEY store_tax_zone_active (active),
  KEY store_tax_zone_priority (priority)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['store_tax_zone_locations']} (
  id int(10) unsigned NOT NULL auto_increment,
  zone_id int(10) unsigned NOT NULL,
  country_code char(2) NOT NULL default '',
  region_code varchar(64) NOT NULL default '',
  postal_pattern varchar(190) NOT NULL default '',
  PRIMARY KEY (id),
  KEY store_tax_location_zone (zone_id),
  KEY store_tax_location_country (country_code),
  KEY store_tax_location_region (country_code,region_code)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['store_tax_rates']} (
  id int(10) unsigned NOT NULL auto_increment,
  zone_id int(10) unsigned NOT NULL,
  tax_class_id int(10) unsigned NOT NULL,
  name varchar(190) NOT NULL default '',
  rate decimal(9,4) NOT NULL default '0.0000',
  priority int(11) NOT NULL default '100',
  compound tinyint(1) NOT NULL default '0',
  active tinyint(1) NOT NULL default '1',
  created datetime NULL,
  modified datetime NULL,
  PRIMARY KEY (id),
  KEY store_tax_rate_zone (zone_id),
  KEY store_tax_rate_class (tax_class_id),
  KEY store_tax_rate_lookup (zone_id,tax_class_id,active)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['store_shipping_zones']} (
  id int(10) unsigned NOT NULL auto_increment,
  name varchar(190) NOT NULL default '',
  active tinyint(1) NOT NULL default '1',
  priority int(11) NOT NULL default '100',
  created datetime NULL,
  modified datetime NULL,
  PRIMARY KEY (id),
  KEY store_shipping_zone_active (active),
  KEY store_shipping_zone_priority (priority)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['store_shipping_zone_locations']} (
  id int(10) unsigned NOT NULL auto_increment,
  zone_id int(10) unsigned NOT NULL,
  country_code char(2) NOT NULL default '',
  region_code varchar(64) NOT NULL default '',
  postal_pattern varchar(190) NOT NULL default '',
  PRIMARY KEY (id),
  KEY store_shipping_location_zone (zone_id),
  KEY store_shipping_location_country (country_code),
  KEY store_shipping_location_region (country_code,region_code)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['store_shipping_methods']} (
  id int(10) unsigned NOT NULL auto_increment,
  zone_id int(10) unsigned NOT NULL,
  code varchar(64) NOT NULL default '',
  name varchar(190) NOT NULL default '',
  method_type varchar(32) NOT NULL default 'flat',
  price decimal(12,4) NOT NULL default '0.0000',
  free_above decimal(12,4) NOT NULL default '0.0000',
  weight_rate decimal(12,4) NOT NULL default '0.0000',
  minimum_weight decimal(12,4) NOT NULL default '0.0000',
  maximum_weight decimal(12,4) NOT NULL default '0.0000',
  tax_class_id int(10) unsigned NOT NULL default '0',
  active tinyint(1) NOT NULL default '1',
  sort_order int(11) NOT NULL default '100',
  created datetime NULL,
  modified datetime NULL,
  PRIMARY KEY (id),
  KEY store_shipping_method_zone (zone_id),
  KEY store_shipping_method_active (active),
  KEY store_shipping_method_code (code)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['store_products']} (
  id int(10) unsigned NOT NULL auto_increment,
  category_id int(10) unsigned NOT NULL default '0',
  tax_class_id int(10) unsigned NOT NULL default '0',
  sku varchar(64) NOT NULL default '',
  slug varchar(190) NOT NULL default '',
  name varchar(255) NOT NULL default '',
  short_description text NULL,
  description mediumtext NULL,
  image_url varchar(1000) NOT NULL default '',
  price decimal(12,4) NOT NULL default '0.0000',
  currency char(3) NOT NULL default 'EUR',
  stock int(11) NOT NULL default '0',
  weight decimal(12,4) NOT NULL default '0.0000',
  length decimal(12,4) NOT NULL default '0.0000',
  width decimal(12,4) NOT NULL default '0.0000',
  height decimal(12,4) NOT NULL default '0.0000',
  product_type varchar(20) NOT NULL default 'physical',
  active tinyint(1) NOT NULL default '1',
  created datetime NULL,
  modified datetime NULL,
  PRIMARY KEY (id),
  UNIQUE KEY store_slug (slug),
  KEY store_sku (sku),
  KEY store_category (category_id),
  KEY store_tax_class (tax_class_id),
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
  region varchar(190) NOT NULL default '',
  country varchar(190) NOT NULL default '',
  country_code char(2) NOT NULL default '',
  status varchar(20) NOT NULL default 'pending',
  payment_method varchar(32) NOT NULL default 'manual',
  payment_status varchar(20) NOT NULL default 'pending',
  payment_reference varchar(255) NOT NULL default '',
  paid_at datetime NULL,
  currency char(3) NOT NULL default 'EUR',
  items_subtotal decimal(12,4) NOT NULL default '0.0000',
  discount_total decimal(12,4) NOT NULL default '0.0000',
  shipping_method varchar(64) NOT NULL default '',
  shipping_label varchar(190) NOT NULL default '',
  shipping_subtotal decimal(12,4) NOT NULL default '0.0000',
  shipping_tax decimal(12,4) NOT NULL default '0.0000',
  tax_total decimal(12,4) NOT NULL default '0.0000',
  total decimal(12,4) NOT NULL default '0.0000',
  prices_include_tax tinyint(1) NOT NULL default '0',
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
  tax_class_id int(10) unsigned NOT NULL default '0',
  sku varchar(64) NOT NULL default '',
  name varchar(255) NOT NULL default '',
  product_type varchar(20) NOT NULL default 'physical',
  unit_price decimal(12,4) NOT NULL default '0.0000',
  quantity int(10) unsigned NOT NULL default '1',
  line_subtotal decimal(12,4) NOT NULL default '0.0000',
  tax_label varchar(190) NOT NULL default '',
  tax_rate decimal(9,4) NOT NULL default '0.0000',
  unit_tax decimal(12,4) NOT NULL default '0.0000',
  tax_total decimal(12,4) NOT NULL default '0.0000',
  line_total decimal(12,4) NOT NULL default '0.0000',
  PRIMARY KEY (id),
  KEY store_item_order (order_id),
  KEY store_item_product (product_id),
  KEY store_item_tax_class (tax_class_id)
) ENGINE=MyISAM
";

$_SQL[] = "
CREATE TABLE {$_TABLES['store_payments']} (
  id int(10) unsigned NOT NULL auto_increment,
  order_id int(10) unsigned NOT NULL,
  provider varchar(32) NOT NULL default 'manual',
  status varchar(20) NOT NULL default 'pending',
  amount decimal(12,4) NOT NULL default '0.0000',
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

// Neutral classes only: percentages are always configured by the merchant.
$_SQL[] = "INSERT INTO {$_TABLES['store_tax_classes']} (code,name,active,created,modified) VALUES
('standard','Standard',1,NOW(),NOW()),
('reduced','Reduced',1,NOW(),NOW()),
('zero','Zero rate',1,NOW(),NOW()),
('exempt','Exempt',1,NOW(),NOW())";
