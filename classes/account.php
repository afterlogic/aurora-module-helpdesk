<?php
/**
 * @copyright Copyright (c) 2017, Afterlogic Corp.
 * @license AGPL-3.0 or AfterLogic Software License
 *
 * This code is licensed under AGPLv3 license or AfterLogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Modules\HelpDesk;

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
		parent::__construct(get_class($this), $sModule);
		
		$this->setStaticMap(array(
			'IsDisabled'	=> array('bool', false),
			'IdUser'		=> array('int', 0),
//			'Login'			=> array('string', ''),
//			'Password'		=> array('string', ''),
			'NotificationEmail' => array('string', '')
			/* moved from user */
//			'IsAgent' => array('string', '')
		));
	}
	
	/**
	 * Checks if the user has only valid data.
	 * 
	 * @return bool
	 */
	public function validate()
	{
		switch (true)
		{
			case false:
				throw new \Aurora\System\Exceptions\ValidationException(Errs::Validation_FieldIsEmpty, null, array(
					'{{ClassName}}' => 'CUser', '{{ClassField}}' => 'Error'));
		}

		return true;
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
