<?php
return array(
	'ctrl' => array(
		'title' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location',
		'label' => 'name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',
		'delete' => 'deleted',

		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',

		'requestUpdate' => 'country',
		'searchFields' => 'name, zipcode, city, address, country, notes',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
			'fe_group' => 'fe_group',
		),
		'dividers2tabs' => 1,
		'iconfile' => '../typo3conf/ext/store_finder/Resources/Public/Icons/tx_storefinder_domain_model_location.gif',
	),

	'interface' => array(
		'showRecordFieldList' => 'hidden, endtime, fe_group, name, storeid, address, additionaladdress, person, city, state,
			zipcode, country, attributes, products, phone, mobile, hours, url, notes, image, icon, content, use_coordinate,
			categories, latitude, longitude, geocode'
	),

	'columns' => array(
		'hidden' => $GLOBALS['TCA']['tt_content']['columns']['hidden'],
		'starttime' => $GLOBALS['TCA']['tt_content']['columns']['starttime'],
		'endtime' => $GLOBALS['TCA']['tt_content']['columns']['endtime'],
		'fe_group' => $GLOBALS['TCA']['tt_content']['columns']['fe_group'],
		'sys_language_uid' => $GLOBALS['TCA']['tt_content']['columns']['sys_language_uid'],

		'l18n_parent' => array(
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array(
						'',
						0
					)
				),
				'foreign_table' => 'tx_storefinder_domain_model_location',
				'foreign_table_where' => 'AND tx_storefinder_domain_model_location.pid=###CURRENT_PID###
					AND tx_storefinder_domain_model_location.sys_language_uid IN (-1,0)'
			)
		),

			// address
		'name' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.name',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'max' => '256',
				'eval' => 'required,trim',
			)
		),

		'storeid' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.storeid',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),

		'address' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.address',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '3',
				'eval' => 'trim',
			)
		),

		'additionaladdress' => array(
			'exclude' => 0,
			'label' =>
				'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.additionaladdress',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),

		'zipcode' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.zipcode',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),

		'city' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.city',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),

		'state' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.state',
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
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.country',
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

			// contact
		'person' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.person',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),

		'phone' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.phone',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),

		'mobile' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.mobile',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),

		'fax' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.fax',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),

		'email' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.email',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'eval' => 'trim',
			)
		),

		'hours' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.hours',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),


			// relations
		'related' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.related',
			'config' => array(
				'type' => 'select',
				'items' => array(
					Array('', 0)
				),
				'foreign_table' => 'tx_storefinder_domain_model_location',
				'foreign_table_where' => 'AND tx_storefinder_domain_model_location.uid != ###THIS_UID###
					ORDER BY tx_storefinder_domain_model_location.name',
				'MM' => 'sys_category_record_mm',
				'MM_match_fields' => array(
					'tablenames' => 'tx_storefinder_domain_model_location',
					'fieldname' => 'related',
				),
				'minitems' => '0',
				'maxitems' => '1',
				'default' => '0',
			)
		),

		'categories' => array(
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.categories',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'sys_category',
				'foreign_table_where' => 'AND sys_category.sys_language_uid IN (0,-1) ORDER BY sys_category.title ASC',
				'MM' => 'sys_category_record_mm',
				'MM_opposite_field' => 'items',
				'MM_match_fields' => array(
					'tablenames' => 'tx_storefinder_domain_model_location',
					'fieldname' => 'categories',
				),
				'size' => 10,
				'autoSizeMax' => 50,
				'maxitems' => 9999,
				'renderMode' => 'tree',
				'treeConfig' => array(
					'parentField' => 'parent',
					'appearance' => array(
						'expandAll' => TRUE,
						'showHeader' => TRUE,
					),
				),
				'wizards' => array(
					'add' => array(
						'type' => 'script',
						'title' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:sys_category.add',
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

		'attributes' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.attributes',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'tx_storefinder_domain_model_attribute',
				'foreign_table_where' => ' AND tx_storefinder_domain_model_attribute.sys_language_uid = 0
					AND tx_storefinder_domain_model_attribute.pid = ###CURRENT_PID###',
				'MM' => 'tx_storefinder_location_attribute_mm',
				'MM_match_fields' => array(
					'tablenames' => 'tx_storefinder_domain_model_attribute',
					'fieldname' => 'attributes',
				),
				'size' => 10,
				'maxitems' => 30,
			)
		),

		'icon' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.icon',
			'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig('icon', array(
				'appearance' => array(
					'createNewRelationLinkTitle' => 'LLL:EXT:cms/locallang_ttc.xlf:images.addFileReference'
				),
				'minitems' => 0,
				'maxitems' => 1,
				// custom configuration for displaying fields in the overlay/reference table
				// to use the imageoverlayPalette instead of the basicoverlayPalette
				'foreign_types' => array(
					'0' => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					)
				)
			), $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']),
		),

		'latitude' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.latitude',
			'config' => array(
				'type' => 'input',
				'readOnly' => 1,
				'size' => 10,
			)
		),

		'longitude' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.longitude',
			'config' => array(
				'type' => 'input',
				'readOnly' => 1,
				'size' => 10,
			)
		),

		'center' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.center',
			'config' => array(
				'type' => 'check',
			)
		),

		'geocode' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.geocode',
			'config' => array(
				'type' => 'check',
			)
		),

		'distance' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.distance',
			'config' => array(
				'type' => 'input',
				'readOnly' => 1,
				'size' => 10,
			)
		),


			// informations
		'products' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.products',
			'config' => array(
				'type' => 'input',
				'size' => '50',
				'eval' => 'trim',
				'max' => '255',
			)
		),

		'notes' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.notes',
			'config' => array(
				'type' => 'text',
				'cols' => '80',
				'rows' => '15',
				'softref' => 'rtehtmlarea_images,typolink_tag,images,email[subst],url',
			)
		),

		'url' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.url',
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

		'image' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.image',
			'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig('image', array(
				'appearance' => array(
					'headerThumbnail' => array(
						'width' => '100',
						'height' => '100c',
					),
					'createNewRelationLinkTitle' => 'LLL:EXT:cms/locallang_ttc.xlf:images.addFileReference'
				),
				// custom configuration for displaying fields in the overlay/reference table
				// to use the imageoverlayPalette instead of the basicoverlayPalette
				'foreign_types' => array(
					'0' => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					)
				)
			), $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']),
		),

		'media' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.media',
			'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig('media', array(
				'appearance' => array(
					'headerThumbnail' => array(
						'width' => '100',
						'height' => '100c',
					),
					'createNewRelationLinkTitle' => 'LLL:EXT:cms/locallang_ttc.xlf:images.addFileReference'
				),
				// custom configuration for displaying fields in the overlay/reference table
				// to use the imageoverlayPalette instead of the basicoverlayPalette
				'foreign_types' => array(
					'0' => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					)
				)
			), $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']),
		),

		'content' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.content',
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
			--div--;LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:div-address,
				name, storeid, address, additionaladdress, zipcode, city, state, country,
			--div--;LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:div-contact,
				person, phone, mobile, fax, email, hours,
			--div--;LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:div-relations,
				related, categories, attributes, icon,
				--palette--;LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:palette-coordinates;coordinates,
			--div--;LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:div-informations,
				products, notes;;;richtext:rte_transform[flag=rte_enabled|mode=ts_css], url,
				image;LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.image,
				media;LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.media, content,
			--div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access,
				--palette--;LLL:EXT:cms/locallang_ttc.xlf:palette.visibility;visibility,
				--palette--;LLL:EXT:cms/locallang_ttc.xlf:palette.access;access,')
	),

	'palettes' => array(
		'coordinates' => array(
			'showitem' => 'latitude, longitude, center, geocode',
			'canNotCollapse' => 1
		),
		'visibility' => array(
			'showitem' => '
				hidden;LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:tx_storefinder_domain_model_location.hidden',
			'canNotCollapse' => 1
		),
		'access' => array(
			'showitem' => '
				starttime;LLL:EXT:cms/locallang_ttc.xlf:starttime_formlabel,
				endtime;LLL:EXT:cms/locallang_ttc.xlf:endtime_formlabel,
				--linebreak--, fe_group;LLL:EXT:cms/locallang_ttc.xlf:fe_group_formlabel',
			'canNotCollapse' => 1
		),
	)
);
