#
# Table structure for table 'tx_storefinder_domain_model_location'
#
CREATE TABLE tx_storefinder_domain_model_location (
	name varchar(255) DEFAULT '' NOT NULL,
	storeid varchar(60) DEFAULT '' NOT NULL,
	address varchar(255) DEFAULT '' NOT NULL,
	additionaladdress varchar(255) DEFAULT '' NOT NULL,
	person varchar(255) DEFAULT '' NOT NULL,
	city varchar(255) DEFAULT '' NOT NULL,
	zipcode varchar(255) DEFAULT '' NOT NULL,
	state varchar(255) DEFAULT '' NOT NULL,
	country varchar(11) DEFAULT '' NOT NULL,
	products varchar(255) DEFAULT '' NOT NULL,
	phone varchar(255) DEFAULT '' NOT NULL,
	mobile varchar(255) DEFAULT '' NOT NULL,
	fax varchar(255) DEFAULT '' NOT NULL,
	hours tinytext,
	email varchar(255) DEFAULT '' NOT NULL,
	url varchar(2048) DEFAULT '' NOT NULL,
	notes text,

	attributes int(11) unsigned DEFAULT '0' NOT NULL,
	categories int(11) unsigned DEFAULT '0' NOT NULL,
	content int(11) unsigned DEFAULT '0' NOT NULL,
	related int(11) unsigned DEFAULT '0' NOT NULL,
	image int(11) unsigned DEFAULT '0' NOT NULL,
	media int(11) unsigned DEFAULT '0' NOT NULL,
	icon int(11) unsigned DEFAULT '0' NOT NULL,

	map int(11) unsigned DEFAULT '0' NOT NULL,
	latitude double(11,7) DEFAULT '0.0000000' NOT NULL,
	longitude double(11,7) DEFAULT '0.0000000' NOT NULL,
	center int(4) DEFAULT '0' NOT NULL,
	geocode int(4) DEFAULT '0' NOT NULL,

	import_id int(11) DEFAULT '0' NOT NULL,

	KEY import_id (import_id)
);


#
# Table structure for table 'tx_storefinder_domain_model_attribute'
#
CREATE TABLE tx_storefinder_domain_model_attribute (
	icon varchar(255) DEFAULT '' NOT NULL,
	name varchar(255) DEFAULT '' NOT NULL,

	import_id int(11) DEFAULT '0' NOT NULL,

	KEY import_id (import_id)
);


#
# Table structure for table 'tx_storefinder_location_attribute_mm'
#
CREATE TABLE tx_storefinder_location_attribute_mm (
	uid_local int(11) DEFAULT '0' NOT NULL,
	uid_foreign int(11) DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(255) DEFAULT '' NOT NULL,
	fieldname varchar(255) DEFAULT '' NOT NULL,

	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


#
# Table structure for table 'tx_storefinder_location_location_mm'
#
CREATE TABLE tx_storefinder_location_location_mm (
	uid_local int(11) DEFAULT '0' NOT NULL,
	uid_foreign int(11) DEFAULT '0' NOT NULL,
	sorting int(11) DEFAULT '0' NOT NULL,
	sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(255) DEFAULT '' NOT NULL,
	fieldname varchar(255) DEFAULT '' NOT NULL,

	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


#
# Table structure for table 'sys_category'
#
CREATE TABLE sys_category (
	children int(11) unsigned DEFAULT '0' NOT NULL,
	import_id varchar(100) DEFAULT '' NOT NULL
);
