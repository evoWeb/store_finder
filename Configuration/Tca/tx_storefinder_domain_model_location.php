<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$GLOBALS['TCA']['tx_storefinder_domain_model_location'] = array(
	'ctrl' => $GLOBALS['TCA']['tx_storefinder_domain_model_location']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'hidden,endtime,fe_group,name,storeid,address,additionaladdress,person,city,state,zipcode,country,attributes,products,phone,mobile,hours,url,notes,image,icon,content,use_coordinate,categories,latitude,longitude,geocode'
	),
	'feInterface' => $GLOBALS['TCA']['tx_storefinder_domain_model_location']['feInterface'],
	'columns' => array(
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => array(
				'type' => 'check',
				'items' => array ('1' => array('0' => 'LLL:EXT:cms/locallang_ttc.xml:hidden.I.0', ), ),
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => '13',
				'max' => '20',
				'eval' => 'date',
				'default' => '0',
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => '13',
				'max' => '20',
				'eval' => 'date',
				'default' => '0',
				'range' => array(
					'upper' => mktime(0, 0, 0, 12, 31, 2020),
				),
			),
		),
		'fe_group' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config' => array(
				'type' => 'select',
				'size' => 5,
				'maxitems' => 20,
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1,),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2,),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--',),
				),
				'exclusiveKeys' => '-1,-2',
				'foreign_table' => 'fe_groups',
				'foreign_table_where' => 'ORDER BY fe_groups.title',
			),
		),

		'name' => array(
			'exclude' => 0,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.name',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),

		'related' => array(
			'exclude' => 0,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.related',
			'config' => array(
				'type' => 'select',
				'items' => array(
					Array('', 0)
				),
				'foreign_table' => 'tx_storefinder_domain_model_location',
				'foreign_table_where' => 'AND tx_storefinder_domain_model_location.uid != ###THIS_UID###',
				'minitems' => '0',
				'maxitems' => '1',
				'default' => '0',
			)
		),

		'storeid' => array(
			'exclude' => 0,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.storeid',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),

		'attributes' => array(
			'exclude' => 1,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.attributes',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'tx_storefinder_domain_model_attribute',
				'foreign_table_loadIcon' => 1,
				'iconsInOptionTags' => 1,
				'size' => 10,
				'maxitems' => 30,
				'show_thumbs' => 1,
			)
		),

		'address' => array(
			'exclude' => 0,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.address',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),

		'additionaladdress' => array(
			'exclude' => 0,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.additionaladdress',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),

		'person' => array(
			'exclude' => 0,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.person',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),

		'city' => array(
			'exclude' => 0,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.city',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),

		'zipcode' => array(
			'exclude' => 0,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.zipcode',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),

		'state' => array(
			'exclude' => 0,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.state',
			'displayCond' => 'FIELD:country:>:0',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('', 0),
				),
				'foreign_table' => 'static_country_zones',
				'foreign_table_where' => 'AND zn_country_uid = ###REC_FIELD_country### ORDER BY static_country_zones.zn_name_local',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1
			)
		),

		'country' => array(
			'exclude' => 0,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.country',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('', 0),
				),
				'foreign_table' => 'static_countries',
				'itemsProcFunc' => 'SJBR\\StaticInfoTables\\Hook\\Backend\\Form\\ElementRenderingHelper->translateCountriesSelector',
				'size' => 1,
				'minitems' => 1,
				'maxitems' => 1
			)
		),

		'products' => array(
			'exclude' => 0,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.products',
			'config' => array(
				'type' => 'input',
				'size' => '50',
				'eval' => 'trim',
				'max' => '255',
			)
		),

		'phone' => array(
			'exclude' => 0,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.phone',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),

		'mobile' => array(
			'exclude' => 0,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.mobile',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),

		'fax' => array(
			'exclude' => 0,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.fax',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),

		'email' => array(
			'exclude' => 0,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.email',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),

		'hours' => array(
			'exclude' => 0,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.hours',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),

		'url' => array(
			'exclude' => 0,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.url',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'max' => '255',
				'eval' => 'trim',
				'wizards' => Array(
					'_PADDING' => 2,
					'link' => Array(
						'type' => 'popup',
						'title' => 'Link',
						'icon' => 'link_popup.gif',
						'script' => 'browse_links.php?mode=wizard',
						'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
					)
				)
			)
		),

		'notes' => array(
			'exclude' => 0,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.notes',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),

		'media' => array(
			'exclude' => 0,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.media',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => 'txt,html,htm,class,swf,swa,dcr,wav,avi,au,mov,asf,mpg,wmv,mp3,mp4,m4v',
				'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],
				'uploadfolder' => 'uploads/media',
				'size' => '2',
				'maxitems' => '1',
				'minitems' => '0',
			),
		),

		'image' => array(
			'exclude' => 0,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.image',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
				'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],
				'uploadfolder' => 'uploads/pics',
				'show_thumbs' => '1',
				'size' => '3',
				'maxitems' => '200',
				'minitems' => '0',
				'autoSizeMax' => 40,
			),
		),

		'icon' => array(
			'exclude' => 0,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.icon',
			'config' => array(
				'type' => 'select',
				'items' => array(),
				'size' => 1,
				'maxitems' => 1,
			)
		),

		'categories' => array(
			'label' => 'LLL:EXT:lang/locallang_tca.xlf:category_perms',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'sys_category',
				'foreign_table_where' => ' AND (sys_category.sys_language_uid = 0 OR sys_category.l10n_parent = 0) ORDER BY sys_category.sorting',
				'MM' => 'sys_category_record_mm',
				'MM_opposite_field' => 'items',
				'MM_match_fields' => array(
					'tablenames' => 'tx_storefinder_domain_model_location',
					'fieldname' => 'categories',
				),
				'renderMode' => 'tree',
				'treeConfig' => array(
					'parentField' => 'parent',
					'appearance' => array(
						'expandAll' => FALSE,
						'showHeader' => FALSE,
						'maxLevels' => 99,
					),
				),
				'size' => 10,
				'autoSizeMax' => 20,
				'minitems' => 0,
				'maxitems' => 9999,
				'wizards' => array(
					'add' => array(
						'type' => 'script',
						'title' => STOREFINDERLLFILE . 'sys_category.add',
						'icon' => 'add.gif',
						'params' => array(
							'table' => 'sys_category',
							'pid' => '###CURRENT_PID###',
							'setValue' => 'prepend'
						),
						'script' => 'wizard_add.php',
					)
				),
			)
		),

		'latitude' => array(
			'exclude' => 0,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.latitude',
			'config' => array(
				'type' => 'input',
				'readOnly' => 1,
				'size' => 10,
			)
		),

		'longitude' => array(
			'exclude' => 0,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.longitude',
			'config' => array(
				'type' => 'input',
				'readOnly' => 1,
				'size' => 10,
			)
		),

		'use_as_center' => array(
			'exclude' => 1,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.use_as_center',
			'config' => array(
				'type' => 'check',
			)
		),

		'content' => array(
			'exclude' => 1,
			'label' => STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.content',
			'config' => array(
				'type' => 'inline',
				'allowed' => 'tt_content',
				'foreign_table' => 'tt_content',
				'minitems' => 0,
				'maxitems' => 10,
				'appearance' => array(
					'collapseAll' => 1,
					'expandSingle' => 1,
					'levelLinksPosition' => 'bottom',
					'useSortable' => 1,
					'showPossibleLocalizationRecords' => 1,
					'showRemovedLocalizationRecords' => 1,
					'showAllLocalizationLink' => 1,
					'showSynchronizationLink' => 1,
					'enabledControls' => array(
						'info' => FALSE,
					)
				)
			)
		),
	),

	'types' => array(
		'0' => array('showitem' => '
			--div--;' . STOREFINDERLLFILE . 'div-address,
				name, storeid, address, additionaladdress, zipcode, city, state, country,
			--div--;' . STOREFINDERLLFILE . 'div-contact,
				person, phone, mobile, fax, email, hours,
			--div--;' . STOREFINDERLLFILE . 'div-informations,
				products, notes, url, image, media, content,
			--div--;' . STOREFINDERLLFILE . 'div-relations,
				--palette--;LLL:EXT:cms/locallang_ttc.xml:palette.access;access,
				related, categories, attributes, icon, latitude, longitude, use_as_center')
	),

	'palettes' => array(
		'access' => array(
			'showitem' => '
				hidden;' . STOREFINDERLLFILE . 'tx_storefinder_domain_model_location.hidden, --linebreak--,
				starttime;LLL:EXT:cms/locallang_ttc.xml:starttime_formlabel,
				endtime;LLL:EXT:cms/locallang_ttc.xml:endtime_formlabel, --linebreak--,
				fe_group;LLL:EXT:cms/locallang_ttc.xml:fe_group_formlabel',
			'canNotCollapse' => 1,
		),
	)
);

?>