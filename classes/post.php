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
 * @property int $IdTenant
 * @property int $IdOwner
 * @property int $IdPost
 * @property array $Attachments
 * @property int $Type
 * @property int $SystemType
 * @property int $Created
 * @property string $Text
 *
 * @package Helpdesk
 * @subpackage Classes
 */
class CPost extends \Aurora\System\EAV\Entity
{
	/**
	 * @var array
	 */
	public $IsThreadOwner = false;
	
	/**
	 * @var array
	 */
	public $Attachments = null;

	public function __construct($sModule)
	{
		$this->aStaticMap = array(
			'IdThread'		=> array('int', 0),
			'IdTenant'		=> array('int', 0),
			'IdOwner'		=> array('int', 0),
			'IdPost'		=> array('int', 0),
			'Type'			=> array('int', \Aurora\Modules\HelpDesk\Enums\PostType::Normal),
			'SystemType'	=> array('int', \Aurora\Modules\HelpDesk\Enums\PostSystemType::None),
			'Created'		=> array('datetime', date('Y-m-d H:i:s')),
			'Text'			=> array('string', '')
		);
		parent::__construct($sModule);
	}
	
	public function toResponseArray()
	{
		$aResponse = parent::toResponseArray();
		$aResponse['Attachments'] = \Aurora\System\Managers\Response::GetResponseObject($this->Attachments);
		$aResponse['Text'] = \MailSo\Base\HtmlUtils::ConvertPlainToHtml($this->Text);
		$aResponse['IdPost'] = $this->EntityId;
		
		$oOwnerUser = \Aurora\System\Api::getUserById($this->IdOwner);
		if ($oOwnerUser !== false)
		{
			$aResponse['Owner'] = array($oOwnerUser->PublicId, '');
		}
		
		$oAuthenticatedUser = \Aurora\System\Api::getAuthenticatedUser();
		$aResponse['ItsMe'] = $oAuthenticatedUser->EntityId === $this->IdOwner;
		$aResponse['IsThreadOwner'] = $this->IsThreadOwner;
		
		return $aResponse;
	}
}
