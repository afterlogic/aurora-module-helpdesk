<?php
/**
 * @copyright Copyright (c) 2017, Afterlogic Corp.
 * @license AfterLogic Software License
 *
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 */

/**
 * @internal
 * 
 * @package Helpdesk
 * @subpackage Storages
 */
class CApiHelpdeskMainStorage extends \Aurora\System\Managers\AbstractManagerStorage
{
	/**
	 * @param \Aurora\System\Managers\GlobalManager &$oManager
	 */
	public function __construct($sStorageName, \Aurora\System\Managers\AbstractManager &$oManager)
	{
		parent::__construct('helpdesk', $sStorageName, $oManager);
	}
}