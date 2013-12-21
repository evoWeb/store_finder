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
	use_as_center int(4) DEFAULT '0' NOT NULL,

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

	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	tx_storefinder_latitude varchar(255) DEFAULT '' NOT NULL,
	tx_storefinder_longitude varchar(255) DEFAULT '' NOT NULL,
	tx_storefinder_geocode int(4) DEFAULT '1' NOT NULL
);


#
# Table structure for table 'tt_address'
#
CREATE TABLE tt_address (
	tx_storefinder_latitude varchar(255) DEFAULT '' NOT NULL,
	tx_storefinder_longitude varchar(255) DEFAULT '' NOT NULL,
	tx_storefinder_geocode int(4) DEFAULT '1' NOT NULL
);


#
# Table structure for table 'tt_address_group'
#
CREATE TABLE tt_address_group (
	tx_storefinder_icon varchar(255) DEFAULT '' NOT NULL
);