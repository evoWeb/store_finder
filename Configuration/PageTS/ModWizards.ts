mod.wizards {
	newContentElement {
		wizardItems {
			plugins {
				elements {
					storefinder_map {
						icon = ../typo3conf/ext/store_finder/Resources/Public/Icons/google-maps-icon.png
						title = LLL:EXT:store_finder/Resources/Private/Language/locallang_be.xml:pi1_title
						description = LLL:EXT:store_finder/Resources/Private/Language/locallang_be.xml:pi1_plus_wiz_description
						tt_content_defValues {
							CType = list
							list_type = storefinder_map
						}
					}
				}
				show = *
			}
		}
	}
}