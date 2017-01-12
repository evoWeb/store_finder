<?php
defined('TYPO3_MODE') || die('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['SYS']['livesearch']['location'] = 'tx_storefinder_domain_model_location';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_storefinder_domain_model_location');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_storefinder_domain_model_location');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_storefinder_domain_model_attribute');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_storefinder_domain_model_attribute');
