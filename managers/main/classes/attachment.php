<?php

/* -AFTERLOGIC LICENSE HEADER- */

/**
 * @property int $IdHelpdeskAttachment
 * @property int $IdHelpdeskPost
 * @property int $IdHelpdeskThread
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
class CHelpdeskAttachment extends api_AContainer
{
	public function __construct()
	{
		parent::__construct(get_class($this));

		$this->SetDefaults(array(
			'IdHelpdeskAttachment'	=> 0,
			'IdHelpdeskPost'		=> 0,
			'IdHelpdeskThread'		=> 0,
			'IdTenant'				=> 0,
			'IdOwner'				=> 0,
			'Created'				=> time(),
			'SizeInBytes'			=> 0,
			'FileName'				=> '',
			'Content'				=> '',
			'Hash'					=> ''
		));
	}

	/**
	 * @param \CUser $oUser Core user object
	 * @param string $sThreadFolderName
	 */
	public function encodeHash(\CUser $oUser, $sThreadFolderName)
	{
		$this->Hash = \CApi::EncodeKeyValues(array(
			'FilestorageFile' => true,
			'HelpdeskTenantID' => $oUser->IdTenant,
			'HelpdeskUserID' => $oUser->iObjectId,
			'StorageType' => \EFileStorageTypeStr::Corporate,
			'Name' => $this->FileName,
			'Path' => $sThreadFolderName
		));
	}

	/**
	 * @throws CApiValidationException 1106 Errs::Validation_ObjectNotComplete
	 *
	 * @return bool
	 */
	public function validate()
	{
		switch (true)
		{
			case 0 >= $this->IdOwner:
				throw new CApiValidationException(Errs::Validation_ObjectNotComplete, null, array(
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
	 * @param \CUser $oUser
	 * @param \CApiHelpdeskManager $oApiHelpdesk
	 * @param \CApiFilestorageManager $oApiFilestorage
	 */
	public function populateContent($oUser, $oApiHelpdesk, $oApiFilestorage)
	{
		$aHash = \CApi::DecodeKeyValues($this->Hash);
		if (isset($aHash['StorageType'], $aHash['Path'], $aHash['Name']) && $oApiHelpdesk && $oApiFilestorage)
		{
			$oHelpdeskUserFromAttachment = null;
			if (isset($aHash['HelpdeskUserID'], $aHash['HelpdeskTenantID']))
			{
				if ($oUser && $aHash['HelpdeskUserID'] === $oUser->iObjectId)
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

	/**
	 * @return array
	 */
	public static function getStaticMap()
	{
		return array(
			'IdHelpdeskAttachment'	=> array('int', 'id_helpdesk_attachment', false, false),
			'IdHelpdeskPost'		=> array('int', 'id_helpdesk_post', true, false),
			'IdHelpdeskThread'		=> array('int', 'id_helpdesk_thread', true, false),
			'IdTenant'				=> array('int', 'id_tenant', true, false),
			'IdOwner'				=> array('int', 'id_owner', true, false),
			'Created'				=> array('datetime', 'created', true, false),
			'SizeInBytes'			=> array('int', 'size_in_bytes'),
			'FileName'				=> array('string', 'file_name'),
			'Content'				=> array('string'),
			'Hash'					=> array('string', 'hash')
		);
	}
	
	public function toResponseArray()
	{
		$iThumbnailLimit = 1024 * 1024 * 2; // 2MB TODO:
		return array(
			'IdHelpdeskAttachment' => $this->IdHelpdeskAttachment,
			'IdHelpdeskPost' => $this->IdHelpdeskPost,
			'IdHelpdeskThread' => $this->IdHelpdeskThread,
			'SizeInBytes' => $this->SizeInBytes,
			'FileName' => $this->FileName,
			'MimeType' => \MailSo\Base\Utils::MimeContentType($this->FileName),
			'Thumb' => \CApi::GetConf('labs.allow-thumbnail', true) &&
				$this->SizeInBytes < $iThumbnailLimit &&
				\api_Utils::IsGDImageMimeTypeSuppoted(
					\MailSo\Base\Utils::MimeContentType($this->FileName), $this->FileName),
			'Hash' => $this->Hash,
			'Content' => $this->Content,
			'Created' => $this->Created
		);
	}	
}
