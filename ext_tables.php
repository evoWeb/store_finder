<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

/** @noinspection PhpUndefinedVariableInspection */
define('STOREFINDERLLFILE', 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_db.xml:');
define('STOREFINDEREXTPATH', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY));
define('STOREFINDEREXTRELPATH', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY));


$GLOBALS['TCA']['tx_storefinder_domain_model_location'] = array(
	'ctrl' => array(
		'title' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location',
		'label' => 'name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',
		'delete' => 'deleted',
		'requestUpdate' => 'country',
		'searchFields' => 'name, zipcode, city, address, country, notes',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
			'fe_group' => 'fe_group',
		),
		'dividers2tabs' => 1,
		'dynamicConfigFile' => STOREFINDEREXTPATH . 'Configuration/Tca/tx_storefinder_domain_model_location.php',
		'iconfile' => STOREFINDEREXTRELPATH . 'Resources/Public/Icons/tx_storefinder_domain_model_location.gif',
	),
	'feInterface' => array(
		'fe_admin_fieldList' => 'hidden, endtime, fe_group, name, storeid, address, city, state, zip, country, phone, hours, url, notes, image, icon, use_coordinate, categories, latitude, longitude',
	)
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_storefinder_domain_model_location');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_storefinder_domain_model_location');


$GLOBALS['TCA']['tx_storefinder_domain_model_attribute'] = array(
	'ctrl' => array(
		'title' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_attribute',
		'label' => 'name',
		'label_alt' => 'icon',
		'label_alt_force' => '1',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',
		'delete' => 'deleted',

		'type' => 'type',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',

		'selicon_field' => 'icon',
		'selicon_field_path' => 'uploads/tx_storefinder',

		'enablecolumns' => array(
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => STOREFINDEREXTPATH . 'Configuration/Tca/tx_storefinder_domain_model_attribute.php',
		'iconfile' => STOREFINDEREXTRELPATH . 'Resources/Public/Icons/tx_storefinder_domain_model_attribute.gif',
	),
	'feInterface' => array(
		'fe_admin_fieldList' => 'name',
	)
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_storefinder_domain_model_attribute');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_storefinder_domain_model_attribute');


$tempColumns = array(
	'tx_storefinder_latitude' => array(
		'exclude' => 1,
		'label' => STOREFINDERLLFILE . 'fe_users.tx_storefinder_latitude',
		'config' => array(
			'type' => 'input',
			'size' => '30',
			'eval' => 'trim',
		)
	),
	'tx_storefinder_longitude' => array(
		'exclude' => 1,
		'label' => STOREFINDERLLFILE . 'fe_users.tx_storefinder_longitude',
		'config' => array(
			'type' => 'input',
			'size' => '30',
			'eval' => 'trim',
		)
	),
	'tx_storefinder_geocode' => array(
		'exclude' => 1,
		'label' => STOREFINDERLLFILE . 'fe_users.tx_storefinder_geocode',
		'config' => array(
			'type' => 'check',
			'default' => '1',
		)
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'fe_users',
	'tx_storefinder_latitude;;;;1-1-1,tx_storefinder_longitude, tx_storefinder_geocode'
);


	// tt_address
$tempColumns = array(
	'tx_storefinder_latitude' => array(
		'exclude' => 1,
		'label' => STOREFINDERLLFILE . 'tt_address.tx_storefinder_latitude',
		'config' => array(
			'type' => 'input',
			'size' => '15',
			'eval' => 'trim',
		)
	),
	'tx_storefinder_longitude' => array(
		'exclude' => 1,
		'label' => STOREFINDERLLFILE . 'tt_address.tx_storefinder_longitude',
		'config' => array(
			'type' => 'input',
			'size' => '15',
			'eval' => 'trim',
		)
	),
	'tx_storefinder_geocode' => array(
		'exclude' => 1,
		'label' => STOREFINDERLLFILE . 'tt_address.tx_storefinder_geocode',
		'config' => array(
			'type' => 'check',
			'default' => 1,
		)
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_address', $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'tt_address',
	'tx_storefinder_latitude;;;;1-1-1, tx_storefinder_longitude, tx_storefinder_geocode'
);


	// tt_address_group
$tempColumns = array(
	'tx_storefinder_icon' => array(
		'exclude' => 1,
		'label' => STOREFINDERLLFILE . 'tt_address_group.tx_storefinder_icon',
		'config' => array(
			'type' => 'select',
			'items' => array(),
			'itemsProcFunc' => 'EXT:' . $_EXTKEY . '/Classes/Utility/Locations_Icon.php:Tx_Storefinder_Utility_Locations_Icon->main',
			'size' => 1,
			'maxitems' => 1,
		)
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_address_group', $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_address_group', 'tx_storefinder_icon;;;;1-1-1');


$pluginSignature = 'storefinder_map';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout, select_key';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
	$pluginSignature,
	'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_ds.xml'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Map',
	'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_be.xml:tt_content.list_type_map'
);


/**
 * Default TypoScript
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Store Finder');

?>