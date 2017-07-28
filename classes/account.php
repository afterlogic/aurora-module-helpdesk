<?php
/**
 * @copyright Copyright (c) 2017, Afterlogic Corp.
 * @license AfterLogic Software License
 *
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 */

namespace Aurora\Modules\HelpDesk;

/**
 *
 * @package Users
 * @subpackage Classes
 */
class CAccount extends \Aurora\System\EAV\Entity
{
	/**
	 * Creates a new instance of the object.
	 * 
	 * @return void
	 */
	public function __construct($sModule)
	{
		$this->setStaticMap(array(
			'IsDisabled'	=> array('bool', false),
			'IdUser'		=> array('int', 0),
			'Login'			=> array('string', ''),
			'Password'		=> array('encrypted', ''),
			'NotificationEmail' => array('string', '')
			/* moved from user */
//			'IsAgent' => array('string', '')
		));
		parent::__construct($sModule);
	}
	
	/**
	 * @return string
	 */
	public function getNotificationEmail()
	{
		$sEmail = $this->NotificationEmail;
		if (empty($sEmail))
		{
//			$sEmail = $this->Email;
			$sEmail = $this->Login;
		}

		return $sEmail;
	}
}
