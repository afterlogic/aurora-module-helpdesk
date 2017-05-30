<?php
/**
 * @copyright Copyright (c) 2017, Afterlogic Corp.
 * @license AGPL-3.0 or AfterLogic Software License
 *
 * This code is licensed under AGPLv3 license or AfterLogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
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
