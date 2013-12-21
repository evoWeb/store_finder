<?php
namespace Evoweb\StoreFinder\View;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Sebastian Fischer <typo3@evoweb.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * extend class t3lib_treeview to change function wrapTitle().
 */
class SelectTreeview extends \TYPO3\CMS\Backend\Tree\View\AbstractTreeView {
	/**
	 * @var string
	 */
	public $TCEforms_itemFormElName = '';

	/**
	 * @var array
	 */
	public $TCEforms_nonSelectableItemsArray = array();

	/**
	 * @var integer
	 */
	public $maxDepth;

	/**
	 * @var string
	 */
	public $hiddenField;

	/**
	 * Needs to be initialized with e.g. $GLOBALS['WEBMOUNTS']
	 * Default setting in init() is 0 => 0
	 * The keys are mount-ids (can be anything basically) and the values are the
	 * ID of the root element (COULD be zero or anything else. For pages that
	 * would be the uid of the page, zero for the pagetree root.)
	 *
	 * @var array
	 */
	public $MOUNTS = array(0 => 0);

	/**
	 * wraps the record titles in the tree with links or not depending on if they are in the TCEforms_nonSelectableItemsArray.
	 *
	 * @param	string		$title: the title
	 * @param	array		$v: an array with uid and title of the current item.
	 * @return	string		the wrapped title
	 */
	public function wrapTitle($title, $v) {
		if ($v['title'] == '') {
			$title = $v['name'];
		}

		$aOnClick = 'setFormValueFromBrowseWin(\'' . $this->TCEforms_itemFormElName . '\',' . $v['uid'] . ',\'' . $title . '\'); return false;';
		return '<a href="#" onclick="' . htmlspecialchars($aOnClick) . '" title="' . htmlentities($v['description']) . '">' . $title . '</a>';
	}

	/**
	 * Wrap the plus/minus icon in a link
	 *
	 * @param string $icon HTML string to wrap, probably an image tag.
	 * @param string $cmd Command for 'PM' get var
	 * @param string $bMark If set, the link will have a anchor point (=$bMark) and a name attribute (=$bMark)
	 * @return string Link-wrapped input string
	 * @access private
	 */
	public function PM_ATagWrap($icon, $cmd, $bMark = '') {
		if ($this->thisScript) {
			$name = '';
			if ($bMark) {
				$name = ' name="' . $bMark . '"';
			}
			return '<a href="#" onClick="set' . $this->treeName . 'PM(\'' . $cmd . '\');TBE_EDITOR_submitForm();"' . $name . '>' . $icon . '</a>';
		} else {
			return $icon;
		}
	}

	public function initializePositionSaving() {
			// Get stored tree structure:
		$this->stored = unserialize($this->BE_USER->uc['browseTrees'][$this->treeName]);

			// PM action
			// (If an plus/minus icon has been clicked, the PM GET var is sent and we must update the stored positions in the tree):
			// 0: mount key, 1: set/clear boolean, 2: item ID (cannot contain "_"), 3: treeName
		$PM = explode('_', \TYPO3\CMS\Core\Utility\GeneralUtility::_POST($this->treeName . '_pm'));

		if (count($PM) == 4 && $PM[3] == $this->treeName) {
			if (isset($this->MOUNTS[$PM[0]])) {
					// set
				if ($PM[1]) {
						$this->stored[$PM[0]][$PM[2]] = 1;
						$this->savePosition();
					// clear
				} else {
						unset($this->stored[$PM[0]][$PM[2]]);
						$this->savePosition();
				}
			}
		}
	}

	/**
	 * Will create and return the HTML code for a browsable tree
	 * Is based on the mounts found in the internal array ->MOUNTS (set in the constructor)
	 *
	 * @param integer $maxDepth
	 * @return string HTML code for the browsable tree
	 */
	public function getBrowsableTree($maxDepth = 999) {
			// Get stored tree structure AND updating it if needed according to incoming PM GET var.
		$this->initializePositionSaving();

			// Init done:
		$treeArr = array();

			// Traverse mounts:
		foreach ($this->MOUNTS as $idx => $uid) {

				// Set first:
			$this->bank = $idx;
			$isOpen = $this->stored[$idx][$uid] || $this->expandFirst;

				// Save ids while resetting everything else.
			$curIds = $this->ids;
			$this->reset();
			$this->ids = $curIds;

				// Set PM icon for root of mount:
			$cmd = $this->bank . '_' . ($isOpen ? '0_' : '1_') . $uid . '_' . $this->treeName;
			$icon = '<img' . \TYPO3\CMS\Backend\Utility\IconUtility::skinImg($this->backPath, 'gfx/ol/' . ($isOpen ? 'minus' : 'plus') . 'only.gif', 'width="18" height="16"') . ' alt="" />';
			$firstHtml = $this->PM_ATagWrap($icon, $cmd);

				// Preparing rootRec for the mount
			if ($uid) {
				$rootRec = $this->getRecord($uid);
				$firstHtml .= $this->getIcon($rootRec);
			} else {
					// Artificial record for the tree root, id=0
				$rootRec = $this->getRootRecord($uid);
				$firstHtml .= $this->getRootIcon($rootRec);
			}

			if (is_array($rootRec)) {
					// Add the root of the mount to ->tree
				$this->tree[] = array('HTML' => $firstHtml, 'row' => $rootRec, 'bank' => $this->bank);

					// If the mount is expanded, go down:
				if ($isOpen) {
						// Set depth:
					$depthD = '<img' . \TYPO3\CMS\Backend\Utility\IconUtility::skinImg($this->backPath, 'gfx/ol/blank.gif', 'width="18" height="16"') . ' alt="" />';
					if ($this->addSelfId) {
						$this->ids[] = $uid;
					}
					$this->getTree($uid, $maxDepth, $depthD);
				}

					// Add tree:
				$treeArr = array_merge($treeArr, $this->tree);
			}
		}
		return $this->printTree($treeArr);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/store_finder/Classes/View/SelectTreeview.php']) {
	/** @noinspection PhpIncludeInspection */
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/store_finder/Classes/View/SelectTreeview.php']);
}

?>