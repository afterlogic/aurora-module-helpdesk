<?php
/**
 * @copyright Copyright (c) 2016, Afterlogic Corp.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 * 
 * @package Modules
 */

namespace Modules\HelpDesk;

/**
 *
 * @package Users
 * @subpackage Classes
 */
class CAccount extends \CEntity
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
				throw new \CApiValidationException(Errs::Validation_FieldIsEmpty, null, array(
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
