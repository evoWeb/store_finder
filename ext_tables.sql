#
# Table structure for table 'tx_storefinder_domain_model_location'
#
CREATE TABLE tx_storefinder_domain_model_location (
	country varchar(3) DEFAULT '' NOT NULL,

	latitude double(11,7) DEFAULT '0' NOT NULL,
	longitude double(11,7) DEFAULT '0' NOT NULL
);



#
# Table structure for table 'tx_storefinder_location_attribute_mm'
#
CREATE TABLE tx_storefinder_location_attribute_mm (
	uid_local int(11) DEFAULT '0' NOT NULL,
	uid_foreign int(11) DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

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
