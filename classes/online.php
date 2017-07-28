<?php
/**
 * @copyright Copyright (c) 2017, Afterlogic Corp.
 * @license AfterLogic Software License
 *
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 */

/**
 * @property int $IdThread
 * @property int $IdViewer
 * @property string $Email
 * @property datetime $PingTime
 */
class COnline extends \Aurora\System\EAV\Entity
{
	public function __construct($sModule)
	{
		$this->aStaticMap = array(
			'IdThread'		=> array('int', 0),
			'IdViewer'		=> array('int', 0),
			'Email'			=> array('string', ''),
			'PingTime'		=> array('datetime', date('Y-m-d H:i:s')),
		);
		parent::__construct($sModule);
	}
}
