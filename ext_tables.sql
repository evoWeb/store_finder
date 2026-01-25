#
# Table structure for table 'tx_storefinder_domain_model_location'
#
CREATE TABLE tx_storefinder_domain_model_location (
	country varchar(3) DEFAULT '' NOT NULL,

	latitude double(11,7) DEFAULT '0' NOT NULL,
	longitude double(11,7) DEFAULT '0' NOT NULL
);
