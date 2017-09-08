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
 * @property int $IdAttachment
 * @property int $IdPost
 * @property int $IdThread
 * @property int $IdTenant
 * @property int $IdOwner
 * @property int $Created
 * @property int $SizeInBytes
 * @property string $FileName
 * @property string $Content
 * @property string $Hash
 *
 * @package Helpdesk
 * @subpackage Classes
 */
class Attachment extends \Aurora\System\EAV\Entity
{
	public function __construct($sModule)
	{
		$this->aStaticMap = array(
			'IdAttachment'	=> array('int', 0),
			'IdPost'		=> array('int', 0),
			'IdThread'		=> array('int', 0),
			'IdTenant'		=> array('int', 0),
			'IdOwner'		=> array('int', 0),
			'Created'		=> array('datetime', date('Y-m-d H:i:s')),
			'SizeInBytes'	=> array('int', 0),
			'FileName'		=> array('string', ''),
			'Content'		=> array('string', ''),
			'Hash'			=> array('string', '')
		);
		parent::__construct($sModule);
	}

	/**
	 * @param \Aurora\Modules\Core\Classes\User $oUser Core user object
	 * @param string $sThreadFolderName
	 */
	public function encodeHash(\Aurora\Modules\Core\Classes\User $oUser, $sThreadFolderName)
	{
		$this->Hash = \Aurora\System\Api::EncodeKeyValues(array(
			'FilestorageFile' => true,
			'HelpdeskTenantID' => $oUser->IdTenant,
			'HelpdeskUserID' => $oUser->EntityId,
			'StorageType' => \Aurora\System\Enums\FileStorageType::Corporate,
			'Name' => $this->FileName,
			'Path' => $sThreadFolderName
		));
	}

	/**
	 * @param \Aurora\Modules\Core\Classes\User $oUser
	 * @param \CApiHelpdeskManager $oApiHelpdesk
	 * @param \CApiFilestorageManager $oApiFilestorage
	 */
	public function populateContent($oUser, $oApiHelpdesk, $oApiFilestorage)
	{
		$aHash = \Aurora\System\Api::DecodeKeyValues($this->Hash);
		if (isset($aHash['StorageType'], $aHash['Path'], $aHash['Name']) && $oApiHelpdesk && $oApiFilestorage)
		{
			$oHelpdeskUserFromAttachment = null;
			if (isset($aHash['HelpdeskUserID'], $aHash['HelpdeskTenantID']))
			{
				if ($oUser && $aHash['HelpdeskUserID'] === $oUser->EntityId)
				{
					$oHelpdeskUserFromAttachment = $oUser;
				}
				else
				{
					$oHelpdeskUserFromAttachment = $oApiHelpdesk->getUserById(
						$aHash['HelpdeskTenantID'], $aHash['HelpdeskUserID']);
				}
			}

			if ($oHelpdeskUserFromAttachment && $oApiFilestorage->isFileExists(
					$oHelpdeskUserFromAttachment, $aHash['StorageType'], $aHash['Path'], $aHash['Name']
			))
			{
				$mResult = $oApiFilestorage->getFile(
					$oHelpdeskUserFromAttachment, $aHash['StorageType'], $aHash['Path'], $aHash['Name']
				);

				if (is_resource($mResult))
				{
					$this->Content = stream_get_contents($mResult);
				}
			}
		}
	}
	
	public function toResponseArray()
	{
		$oSettings =& \Aurora\System\Api::GetSettings();
		$iThumbnailLimit = ((int) $oSettings->GetConf('ThumbnailMaxFileSizeMb', 5)) * 1024 * 1024;
		return array(
			'IdAttachment' => $this->IdAttachment,
			'IdPost' => $this->IdPost,
			'IdThread' => $this->IdThread,
			'SizeInBytes' => $this->SizeInBytes,
			'FileName' => $this->FileName,
			'MimeType' => \MailSo\Base\Utils::MimeContentType($this->FileName),
			'Thumb' => $oSettings->GetConf('AllowThumbnail', true) &&
				$this->SizeInBytes < $iThumbnailLimit &&
				\Aurora\System\Utils::IsGDImageMimeTypeSuppoted(
					\MailSo\Base\Utils::MimeContentType($this->FileName), $this->FileName),
			'Hash' => $this->Hash,
			'Content' => $this->Content,
			'Created' => $this->Created
		);
	}	
}
