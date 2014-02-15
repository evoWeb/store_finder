#
# Table structure for table 'tx_storefinder_domain_model_location'
#
CREATE TABLE tx_storefinder_domain_model_location (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumtext,

	name varchar(255) DEFAULT '' NOT NULL,
	storeid varchar(25) DEFAULT '' NOT NULL,
	address varchar(255) DEFAULT '' NOT NULL,
	additionaladdress varchar(255) DEFAULT '' NOT NULL,
	person varchar(255) DEFAULT '' NOT NULL,
	city varchar(255) DEFAULT '' NOT NULL,
	zipcode varchar(255) DEFAULT '' NOT NULL,
	state varchar(255) DEFAULT '' NOT NULL,
	country int(11) DEFAULT '0' NOT NULL,
	products varchar(255) DEFAULT '' NOT NULL,
	phone varchar(255) DEFAULT '' NOT NULL,
	mobile varchar(255) DEFAULT '' NOT NULL,
	fax varchar(255) DEFAULT '' NOT NULL,
	hours tinytext NOT NULL,
	email varchar(255) DEFAULT '' NOT NULL,
	url varchar(255) DEFAULT '' NOT NULL,
	notes text NOT NULL,

	media varchar(255) DEFAULT '' NOT NULL,
	image text NOT NULL,
	icon varchar(255) DEFAULT '' NOT NULL,
	content text,
	attributes varchar(255) DEFAULT '' NOT NULL,
	categories tinytext NOT NULL,
	related text NOT NULL,

	latitude double(11,7) DEFAULT '0.0000000' NOT NULL,
	longitude double(11,7) DEFAULT '0.0000000' NOT NULL,
	center int(4) DEFAULT '0' NOT NULL,
	distance double(11,3) DEFAULT '0.000' NOT NULL,

	import_id int(11) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_storefinder_domain_model_attribute'
#
CREATE TABLE tx_storefinder_domain_model_attribute (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumtext,

	icon varchar(255) DEFAULT '' NOT NULL,
	name varchar(255) DEFAULT '' NOT NULL,

	import_id int(11) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
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
	import_id int(11) DEFAULT '0' NOT NULL
);
