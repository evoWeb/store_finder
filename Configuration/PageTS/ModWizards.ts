mod.wizards {
	newContentElement {
		wizardItems {
			plugins {
				elements {
					storefinder_map {
						icon = ../typo3conf/ext/store_finder/Resources/Public/Icons/google-maps-icon.png
						title = LLL:EXT:store_finder/Resources/Private/Language/locallang_be.xml:pi1_title_map
						description = LLL:EXT:store_finder/Resources/Private/Language/locallang_be.xml:pi1_description_map
						tt_content_defValues {
							CType = list
							list_type = storefinder_map
						}
					}
				}
				elements {
					storefinder_show {
						icon = ../typo3conf/ext/store_finder/Resources/Public/Icons/google-maps-icon.png
						title = LLL:EXT:store_finder/Resources/Private/Language/locallang_be.xml:pi1_title_show
						description = LLL:EXT:store_finder/Resources/Private/Language/locallang_be.xml:pi1_description_show
						tt_content_defValues {
							CType = list
							list_type = storefinder_show
						}
					}
				}
				show = *
			}
		}
	}
}