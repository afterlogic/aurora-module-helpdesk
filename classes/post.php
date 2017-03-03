<?php
/**
 * @copyright Copyright (c) 2017, Afterlogic Corp.
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

/**
 * @property int $IdHelpdeskPost
 * @property int $IdHelpdeskThread
 * @property int $IdTenant
 * @property int $IdOwner
 * @property array $Owner
 * @property array $Attachments
 * @property int $Type
 * @property int $SystemType
 * @property int $Created
 * @property bool $IsThreadOwner
 * @property bool $ItsMe
 * @property string $Text
 *
 * @package Helpdesk
 * @subpackage Classes
 */
class CHelpdeskPost extends \Aurora\System\AbstractContainer
{
	/**
	 * @var array
	 */
	public $Owner;
	
	/**
	 * @var array
	 */
	public $Attachments;

	public function __construct()
	{
		parent::__construct(get_class($this));

		$this->SetTrimer(array('Text'));

		$this->Owner = null;
		$this->Attachments = null;

		$this->SetDefaults(array(
			'IdHelpdeskPost'		=> 0,
			'IdHelpdeskThread'		=> 0,
			'IdTenant'				=> 0,
			'IdOwner'				=> 0,
			'Type'					=> EHelpdeskPostType::Normal,
			'SystemType'			=> EHelpdeskPostSystemType::None,
			'Created'				=> time(),
			'IsThreadOwner'			=> true,
			'ItsMe'					=> false,
			'Text'					=> ''
		));
	}

	/**
	 * @throws \Aurora\System\Exceptions\ValidationException 1106 Errs::Validation_ObjectNotComplete
	 *
	 * @return bool
	 */
	public function validate()
	{
		switch (true)
		{
			case 0 >= $this->IdOwner:
				throw new \Aurora\System\Exceptions\ValidationException(Errs::Validation_ObjectNotComplete, null, array(
					'{{ClassName}}' => 'CHelpdeskPost', '{{ClassField}}' => 'IdOwner'));
		}

		return true;
	}
	
	/**
	 * @return array
	 */
	public function getMap()
	{
		return self::getStaticMap();
	}

	/**
	 * @return array
	 */
	public static function getStaticMap()
	{
		return array(
			'IdHelpdeskPost'	=> array('int', 'id_helpdesk_post', false, false),
			'IdHelpdeskThread'	=> array('int', 'id_helpdesk_thread', true, false),
			'IdTenant'			=> array('int', 'id_tenant', true, false),
			'IdOwner'			=> array('int', 'id_owner', true, false),
			'Type'				=> array('int', 'type'),
			'SystemType'		=> array('int', 'system_type'),
			'IsThreadOwner'		=> array('bool'),
			'ItsMe'				=> array('bool'),
			'Text'				=> array('string', 'text'),
			'Created'			=> array('datetime', 'created', true, false)
		);
	}
	
	public function toResponseArray()
	{
		return	array(
			'IdHelpdeskPost' => $this->IdHelpdeskPost,
			'IdHelpdeskThread' => $this->IdHelpdeskThread,
			'IdOwner' => $this->IdOwner,
			'Owner' => $this->Owner,
			'Attachments' => \Aurora\System\ResponseManager::GetResponseObject($this->Attachments),
			'IsThreadOwner' => $this->IsThreadOwner,
			'ItsMe' => $this->ItsMe,
			'Type' => $this->Type,
			'SystemType' => $this->SystemType,
			'Text' => \MailSo\Base\HtmlUtils::ConvertPlainToHtml($this->Text),
			'Created' => $this->Created
		);	
	}
}
