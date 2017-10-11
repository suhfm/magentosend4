<?php
$installer = $this;

$installer->startSetup();

/*$installer->run("
ALTER TABLE sales_flat_quote_shipping_rate add dot_id int(10) 
");*/

$installer->run("

create table sfm_send4_order(
	entity_id int(10) AUTO_INCREMENT NOT NULL,
	order_id int(10),	
	rate_id int(10),
	shipping_company varchar(255),
	value decimal(12,4),
	insurance_value decimal(12,4),
	photo varchar(255),
	signature varchar(255),
	ordered_at datetime,

	code varchar(255),
	updated_at_send4 datetime,
	created_at_send4 datetime,
	id int(10),
	customer_send4_id int(10),
	status_send4_id int(10),
	dot_send4_id int(10),

	created_at datetime,
	updated_at datetime,
	CONSTRAINT PK_send4_quote PRIMARY KEY (entity_id)
);

create table sfm_send4_customer(
	entity_id int(10) AUTO_INCREMENT NOT NULL,
	id_send4 int(10),
	name varchar(255),
	email varchar(255),
	nin varchar(255),
	phone varchar(255),
	created_at datetime,
	updated_at datetime,
	deleted_at datetime,
	CONSTRAINT PK_send4_customer PRIMARY KEY (entity_id)
);

create table sfm_send4_status(
	entity_id int(10) AUTO_INCREMENT NOT NULL,
	id_send4 int(10),
	name varchar(255),
	CONSTRAINT PK_send4_status PRIMARY KEY (entity_id)
);

create table sfm_send4_rates(
	entity_id int(10) AUTO_INCREMENT NOT NULL,
	quote_id int(10),
	rate_id int(10),
	dot_id int(10),
	company_name varchar(255),
	trade_name varchar(255),
	display_name varchar(255),
	cnpj varchar(20),
	email varchar(200),
	address varchar(255),
	complement varchar (255),
	neighbor varchar(200),
	zip_code varchar(10),
	city varchar(100),
	country varchar(100),
	created_at datetime,
	updated_at datetime,
	selected tinyint(2),
	orderflag tinyint(2),
	CONSTRAINT PK_send4_quote PRIMARY KEY (entity_id)
);

");

$installer->endSetup();