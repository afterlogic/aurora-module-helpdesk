<?php
/**
 * This code is licensed under Afterlogic Software License.
 * For full statements of the license see LICENSE file.
 */

namespace Aurora\Modules\HelpDesk\Classes;

/**
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2019, Afterlogic Corp.
 *
 * @property int $IdThread
 * @property int $IdViewer
 * @property string $Email
 * @property datetime $PingTime
 */
class Online extends \Aurora\System\EAV\Entity
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
