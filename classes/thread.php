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
 * @property string $ThreadHash
 * @property int $IdTenant
 * @property int $IdOwner
 * @property bool $ItsMe
 * @property bool $IsArchived
 * @property int $Type
 * @property string $Subject
 * @property int $Created
 * @property int $Updated
 * @property int $PostCount
 * @property int $LastPostId
 * @property int $LastPostOwnerId
 * @property bool $Notificated
 * @property bool $HasAttachments
 * @property bool $IsRead
 * @property array $Owner
 *
 * @package Helpdesk
 * @subpackage Classes
 */
class CThread extends \Aurora\System\EAV\Entity
{
	/**
	 * @var array
	 */
	public $Owner = null;

	public function __construct($sModule)
	{
		$this->aStaticMap = array(
			'ThreadHash'		=> array('string', trim(base_convert(md5(microtime(true).rand(1000, 9999)), 16, 32), '0')),
			'IdTenant'			=> array('int', 0),
			'IdOwner'			=> array('int', 0),
			'ItsMe'				=> array('bool', false),
			'IsArchived'		=> array('bool', false),
			'Type'				=> array('int', EHelpdeskThreadType::None),
			'Subject'			=> array('string', ''),
			'Created'			=> array('datetime', date('Y-m-d H:i:s')),
			'Updated'			=> array('datetime', date('Y-m-d H:i:s')),
			'PostCount'			=> array('int', 0),
			'LastPostId'		=> array('int', 0),
			'LastPostOwnerId'	=> array('int', 0),
			'Notificated'		=> array('bool', false),
			'HasAttachments'	=> array('bool', false),
			'IsRead'			=> array('bool', false)
		);
		parent::__construct($sModule);
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
		$aResponse['Owner'] = $this->Owner;
		$aResponse['IdThread'] = $this->EntityId;
		return $aResponse;
	}
}
