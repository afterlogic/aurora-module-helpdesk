<?php
/**
 * @copyright Copyright (c) 2017, Afterlogic Corp.
 * @license AfterLogic Software License
 *
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 */

namespace Aurora\Modules\HelpDesk\Classes;

/**
 * @property string $ThreadHash
 * @property int $IdTenant
 * @property int $IdOwner
 * @property bool $IsArchived
 * @property int $Type
 * @property string $Subject
 * @property int $Created
 * @property int $Updated
 * @property int $PostCount
 * @property bool $Notificated
 * @property bool $HasAttachments
 * @property string $UsersRead
 *
 * @package Helpdesk
 * @subpackage Classes
 */
class Thread extends \Aurora\System\EAV\Entity
{
	public function __construct($sModule)
	{
		$this->aStaticMap = array(
			'ThreadHash'		=> array('string', trim(base_convert(md5(microtime(true).rand(1000, 9999)), 16, 32), '0')),
			'IdTenant'			=> array('int', 0),
			'IdOwner'			=> array('int', 0),
			'IsArchived'		=> array('bool', false),
			'Type'				=> array('int', \Aurora\Modules\HelpDesk\Enums\ThreadType::None),
			'Subject'			=> array('string', ''),
			'Created'			=> array('datetime', date('Y-m-d H:i:s')),
			'Updated'			=> array('datetime', date('Y-m-d H:i:s'), true),
			'PostCount'			=> array('int', 0),
			'Notificated'		=> array('bool', false),
			'HasAttachments'	=> array('bool', false),
			'UsersRead'			=> array('string', false)
		);
		parent::__construct($sModule);
	}
	
	public function addUserRead($iUserId)
	{
		$aUsersRead = explode('|', $this->UsersRead);
		$aUsersRead[] = $iUserId;
		$this->UsersRead = implode('|', $aUsersRead);
	}
	
	public function resetUsersRead($iUserId)
	{
		$this->UsersRead = $iUserId;
	}
	
	public function hasUserRead($iUserId)
	{
		$aUsersRead = explode('|', $this->UsersRead);
		return in_array($iUserId, $aUsersRead);
	}

	/**
	 * @return string
	 */
	private function _helpdeskLink()
	{
		$sPath = rtrim(\MailSo\Base\Http::SingletonInstance()->GetFullUrl(), '\\/ ');
		if ('/crons' === substr($sPath, -6))
		{
			$sPath = substr($sPath, 0, -6);
		}

		$sPath .= '/?helpdesk';

		if (0 < $this->IdTenant)
		{
			$sPath .= '='.substr(md5($this->IdTenant.\Aurora\System\Api::$sSalt), 0, 8);
		}

		return $sPath;
	}

	/**
	 * @return string
	 */
	public function threadLink()
	{
		$sPath = $this->_helpdeskLink();
		$sPath .= '&thread='.$this->ThreadHash;
		return $sPath;
	}
	
	public function toResponseArray()
	{
		$aResponse = parent::toResponseArray();
		
		$oAuthenticatedUser = \Aurora\System\Api::getAuthenticatedUser();
		$aResponse['IdThread'] = $this->EntityId;
		$aResponse['IsRead'] = $this->hasUserRead($oAuthenticatedUser->EntityId);
		
		$oOwnerUser = \Aurora\System\Api::getUserById($this->IdOwner);
		if ($oOwnerUser !== false)
		{
			$aResponse['Owner'] = array($oOwnerUser->PublicId, '');
		}
		
		$aResponse['ItsMe'] = $oAuthenticatedUser->EntityId === $this->IdOwner;
		
		return $aResponse;
	}
}
