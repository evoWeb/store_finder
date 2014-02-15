<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}


$GLOBALS['TYPO3_CONF_VARS']['SYS']['livesearch']['location'] = 'tx_storefinder_domain_model_location';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_storefinder_domain_model_location');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_storefinder_domain_model_location');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_storefinder_domain_model_attribute');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_storefinder_domain_model_attribute');


/** @noinspection PhpUndefinedVariableInspection */
$tempColumns = array(
	'children' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_db.xml:sys_category.children',
		'config' => array(
			'type' => 'inline',
			'foreign_table' => 'sys_category',
			'foreign_field' => 'parent',
		)
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_category', $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('sys_category', 'children', '', 'after:parent');


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
