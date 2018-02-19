<?php
/**
 * @copyright Copyright (c) 2017, Afterlogic Corp.
 * @license AfterLogic Software License
 *
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 */

namespace Aurora\Modules\HelpDesk\Managers\Main;

/**
 * CApiHelpdeskManager class summary
 *
 * @package Helpdesk
 */
class Manager extends \Aurora\System\Managers\AbstractManager
{
	/**
	 * @var $oApiMail CApiMailManager
	 */
	private $oApiMail;

	/**
	 * @var $oApiUsers CApiUsersManager
	 */
	private $oApiUsers;

	/**
	 * @var $oApiTenants CApiTenantsManager
	 */
	private $oApiTenants;
	
	/**
	 * @var \Aurora\System\Managers\Eav
	 */
	public $oEavManager = null;
	
	/**
	 * @param string $sForcedStorage Default value is empty string.
	 * @param \Aurora\System\Module\AbstractModule &$oManager
	 */
	public function __construct($sForcedStorage = '', \Aurora\System\Module\AbstractModule $oModule = null)
	{
		parent::__construct($oModule);

		$this->oApiMail = null;
		$this->oApiUsers = null;
		$this->oApiTenants = null;
		if ($oModule instanceof \Aurora\System\Module\AbstractModule)
		{
			$this->oEavManager = new \Aurora\System\Managers\Eav();
		}
	}

	/**
	 * Creates a new instance of Users object.
	 *
	 * @return CApiUsersManager
	 */
	private function _getApiUsers()
	{
		if (null === $this->oApiUsers)
		{
//			$this->oApiUsers =\Aurora\System\Api::GetSystemManager('users');
		}

		return $this->oApiUsers;
	}

	/**
	 * Creates a new instance of Mail object.
	 *
	 * @return CApiMailManager
	 */
	public function _getApiMail()
	{
		if (null === $this->oApiMail)
		{
			$this->oApiMail = \Aurora\System\Api::Manager('mail');
		}
		
		return $this->oApiMail;
	}

	/**
	 * @param string $sPath
	 * @param string $sSubject
	 *
	 * @return string
	 */
	private function _getMessageTemplate($sPath, &$sSubject, $fCallback)
	{
		$sData = @file_get_contents($sPath);
		if (is_string($sData) && 0 < strlen($sData))
		{
			$aMatch = array();
			$sData = trim($sData);

			if ($fCallback)
			{
				$sData = call_user_func($fCallback, $sData);
			}
			
			if (preg_match('/^:SUBJECT:([^\n]+)/', $sData, $aMatch) && !empty($aMatch[1]))
			{
				$sSubject = trim($aMatch[1]);
				$sData = trim(preg_replace('/^:SUBJECT:[^\n]+/', '', $sData));
			}

			return $sData;
		}

		return '';
	}

	/**
	 * @param string $sPath
	 * @param \MailSo\Mime\Message $oMessage Message object
	 * @param \Aurora\Modules\Core\Classes\User $oUser Helpdesk user object
	 * @param string $sSiteName
	 * @param string $sFrom
	 */
	private function _addHtmlBodyAndSubjectForUserMessage($sPath, &$oMessage, $oUser, $sSiteName, $sFrom)
	{
		$sSubject = '';
		$sData = $this->_getMessageTemplate($sPath, $sSubject, function ($sData) use ($oUser, $sSiteName, $sFrom) {
			
			$sHelpdeskSiteName = strlen($sSiteName) === 0 ? 'Helpdesk' : $sSiteName;

			return strtr($sData, array(
				'{{HELPDESK/FORGOT_SUBJECT}}' => ':SUBJECT: ' . \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_FORGOT_SUBJECT', null, array("SITE" => $sHelpdeskSiteName)),
				'{{HELPDESK/FORGOT_CONFIRM}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_FORGOT_CONFIRM', null, array("EMAIL" => $oUser->resultEmail(), "SITE" => $sHelpdeskSiteName)),
				'{{HELPDESK/FORGOT_PROCEED_LINK}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_FORGOT_PROCEED_LINK'),
				'{{HELPDESK/FORGOT_LINK}}' => $oUser->forgotLink(),
				'{{HELPDESK/FORGOT_DISREGARD}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_FORGOT_DISREGARD', null, array("SITE" => $sHelpdeskSiteName)),
				'{{HELPDESK/FORGOT_NOT_REPLY}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_FORGOT_NOT_REPLY'),
				'{{HELPDESK/FORGOT_REGARDS}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_FORGOT_REGARDS'),
				'{{HELPDESK/FORGOT_SITE}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_FORGOT_SITE', null, array("SITE" => $sHelpdeskSiteName)),
				'{{HELPDESK/REG_SUBJECT}}' => ':SUBJECT: ' . \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_REG_SUBJECT', null, array("SITE" => $sHelpdeskSiteName)),
				'{{HELPDESK/REG_CONFIRM}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_REG_CONFIRM', null, array("SITE" => $sHelpdeskSiteName)),
				'{{HELPDESK/REG_PROCEED_LINK}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_REG_PROCEED_LINK'),
				'{{HELPDESK/REG_ACTIVATION_LINK}}' => $oUser->activationLink(),
				'{{HELPDESK/REG_DISREGARD}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_REG_DISREGARD'),
				'{{HELPDESK/REG_NOT_REPLY}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_REG_NOT_REPLY'),
				'{{HELPDESK/REG_REGARDS}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_REG_REGARDS'),
				'{{HELPDESK/REG_SITE}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_REG_SITE', null, array("SITE" => $sHelpdeskSiteName)),
				'{{HELPDESK/FETCHER_SUBJECT}}' => ':SUBJECT: ' . \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_FETCHER_SUBJECT', null, array("SITE" => $sHelpdeskSiteName)),
				'{{HELPDESK/FETCHER_CONFIRM}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_FETCHER_CONFIRM', null, array("EMAIL" => $oUser->resultEmail(), "FROM" => $sFrom, "SITE" => $sHelpdeskSiteName)),
				'{{HELPDESK/FETCHER_NAME}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_FETCHER_NAME', null, array("EMAIL" => $oUser->resultEmail())),
				'{{HELPDESK/FETCHER_PASSWORD}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_FETCHER_PASSWORD', null, array("PASSWORD" => $oUser->NotificationPassword)),
				'{{HELPDESK/FETCHER_PROCEED_LINK}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_FETCHER_PROCEED_LINK'),
				'{{HELPDESK/FETCHER_ACTIVATION_LINK}}' => $oUser->activationLink(),
				'{{HELPDESK/FETCHER_HELPDESK_LINK}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_FETCHER_HELPDESK_LINK', null, array("LINK" => $oUser->helpdeskLink())),
				'{{HELPDESK/FETCHER_DISREGARD}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_FETCHER_DISREGARD', null, array("FROM" => $sFrom)),
				'{{HELPDESK/FETCHER_REGARDS}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_FETCHER_REGARDS'),
				'{{HELPDESK/FETCHER_SITE}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_FETCHER_SITE', null, array("SITE" => $sHelpdeskSiteName)),
			));
		});
		
		if (0 < strlen($sSubject))
		{
			$oMessage->SetSubject($sSubject);
		}

		if (is_string($sData) && 0 < strlen($sData))
		{
			$oMessage->AddText(\MailSo\Base\HtmlUtils::ConvertHtmlToPlain($sData), false);
			$oMessage->AddHtml($sData, true);
		}
	}

	/**
	 * @param string $sPath
	 * @param \MailSo\Mime\Message $oMessage Message object
	 * @param \Aurora\Modules\Core\Classes\User $oHelpdeskThreadOwnerUser Helpdesk user object
	 * @param \Aurora\Modules\Core\Classes\User $oHelpdeskPostOwnerUser Helpdesk user object
	 * @param \CThread $oThread Helpdesk thread object
	 * @param CHelpdeskPost $oPost Helpdesk post object
	 * @param string $sSiteName
	 */
	private function _addHtmlBodyAndSubjectForPostMessage($sPath, &$oMessage, $oHelpdeskThreadOwnerUser, $oHelpdeskPostOwnerUser, $oThread, $oPost, $sSiteName)
	{
		$sSubject = '';
			
		$sData = $this->_getMessageTemplate($sPath, $sSubject, function ($sData) use ($oHelpdeskThreadOwnerUser, $oHelpdeskPostOwnerUser, $oThread, $oPost, $sSiteName)
		{
			$sPostOwner = \MailSo\Mime\Email::NewInstance($oHelpdeskPostOwnerUser->resultEmail(), $oHelpdeskPostOwnerUser->Name)->ToString();

			$sSubjectPrefix = '';
			if ($oThread && 0 < $oThread->PostCount - 1)
			{
				$sSubjectPrefix = 'Re'.(2 < $oThread->PostCount ? '['.($oThread->PostCount - 1).']' : '').': ';
			}

			$sAttachments = '';
			if ($oPost && is_array($oPost->Attachments) && 0 < count($oPost->Attachments))
			{
				$sAttachmentsNames = array();
				foreach ($oPost->Attachments as $oAttachment)
				{
					if ($oAttachment)
					{
						$sAttachmentsNames[] = $oAttachment->FileName;
					}
				}

				$sAttachments = '<br /><br />Attachments: '.implode(', ', $sAttachmentsNames).'<br />';
			}

			$sHelpdeskSiteName = strlen($sSiteName) === 0 ? 'Helpdesk' : $sSiteName;
			$sThreadOwner = $oHelpdeskThreadOwnerUser && \strlen($oHelpdeskThreadOwnerUser->Name) > 0 ? ' '.$oHelpdeskThreadOwnerUser->Name : '';

			return strtr($sData, array(
				'{{HELPDESK/POST_AGENT_SUBJECT}}' => ':SUBJECT: ' . \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_POST_AGENT_SUBJECT', null, array("OWNER" => $sPostOwner)),
				'{{HELPDESK/POST_AGENT_HTML}}' => $oPost ? \MailSo\Base\HtmlUtils::ConvertPlainToHtml($oPost->Text) : '',
				'{{HELPDESK/POST_AGENT_ATTACHMENTS}}' => $sAttachments,
				'{{HELPDESK/POST_AGENT_THREAD_LINK}}' => $oThread->threadLink(),
				'{{HELPDESK/POST_USER_SUBJECT}}' => $sSubjectPrefix.$oThread->Subject,
				'{{HELPDESK/POST_USER_GREET}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_POST_USER_GREET', null, array("OWNER" => $sPostOwner)),
				'{{HELPDESK/POST_USER_REMIND}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_POST_USER_REMIND', null, array("SITE" => $sHelpdeskSiteName)),
				'{{HELPDESK/POST_USER_THREAD_SUBJECT_LABEL}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_POST_USER_THREAD_SUBJECT_LABEL'),
				'{{HELPDESK/POST_USER_THREAD_SUBJECT}}' => $oThread->Subject,
				'{{HELPDESK/POST_USER_HTML}}' => $oPost ? \MailSo\Base\HtmlUtils::ConvertPlainToHtml($oPost->Text) : '',
				'{{HELPDESK/POST_USER_NOT_REPLY}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_POST_USER_NOT_REPLY'),
				'{{HELPDESK/POST_USER_CLICK_LINK}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_POST_USER_CLICK_LINK'),
				'{{HELPDESK/POST_USER_THREAD_LINK}}' => $oThread->threadLink(),
				'{{HELPDESK/POST_NEW_SUBJECT}}' => ':SUBJECT: ' . \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_POST_NEW_SUBJECT'),
				'{{HELPDESK/POST_NEW_GREET}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_POST_NEW_GREET', null, array("OWNER" => $sThreadOwner)),
				'{{HELPDESK/POST_NEW_REMIND}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_POST_NEW_REMIND', null, array("SITE" => $sHelpdeskSiteName)),
				'{{HELPDESK/POST_NEW_HTML}}' => $oPost ? \MailSo\Base\HtmlUtils::ConvertPlainToHtml($oPost->Text) : '',
				'{{HELPDESK/POST_NEW_NOT_REPLY}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_POST_NEW_NOT_REPLY'),
				'{{HELPDESK/POST_NEW_CLICK_LINK}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_POST_NEW_CLICK_LINK'),
				'{{HELPDESK/POST_NEW_THREAD_LINK}}' => $oThread->threadLink(),
				'{{HELPDESK/POST_NOTIFICATION_SUBJECT}}' => ':SUBJECT: ' . \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_POST_NOTIFICATION_SUBJECT'),
				'{{HELPDESK/POST_NOTIFICATION_GREET}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_POST_NOTIFICATION_GREET', null, array("OWNER" => $sThreadOwner)),
				'{{HELPDESK/POST_NOTIFICATION_REMIND}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_POST_NOTIFICATION_REMIND', null, array("SITE" => $sHelpdeskSiteName)),
				'{{HELPDESK/POST_NOTIFICATION_QUESTIONS}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_POST_NOTIFICATION_QUESTIONS'),
				'{{HELPDESK/POST_NOTIFICATION_CLOSE}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_POST_NOTIFICATION_CLOSE'),
				'{{HELPDESK/POST_NOTIFICATION_NOT_REPLY}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_POST_NOTIFICATION_NOT_REPLY'),
				'{{HELPDESK/POST_NOTIFICATION_CLICK_LINK}}' => \Aurora\System\Api::ClientI18N('HELPDESK/MAIL_POST_NOTIFICATION_CLICK_LINK'),
				'{{HELPDESK/POST_NOTIFICATION_THREAD_LINK}}' => $oThread->threadLink(),
			));
		});

		if (0 < strlen($sSubject))
		{
			$oMessage->SetSubject($sSubject.' [#'.$oThread->ThreadHash.'#]');
		}

		if (is_string($sData) && 0 < strlen($sData))
		{
			$oMessage->AddText(\MailSo\Base\HtmlUtils::ConvertHtmlToPlain($sData), false);
			$oMessage->AddHtml($sData, true);
		}
	}

	/**
	 * @param string $sFrom
	 * @param string $sTo
	 * @param string $sSubject
	 * @param string $sCc Default value is empty string.
	 * @param string $sBcc Default value is empty string.
	 * @param string $sMessageID Default value is empty string.
	 * @param string $sReferences Default value is empty string.
	 *
	 * @return \MailSo\Mime\Message
	 */
	private function _buildMail($sFrom, $sTo, $sSubject, $sCc = '', $sBcc = '', $sMessageID = '', $sReferences = '')
	{
		$oMessage = \MailSo\Mime\Message::NewInstance();

		if (empty($sMessageID))
		{
			$oMessage->RegenerateMessageId();
		}
		else
		{
			$oMessage->SetMessageId($sMessageID);
		}

		if (!empty($sReferences))
		{
			$oMessage->SetReferences($sReferences);
		}

		$oMailModule = \Aurora\System\Api::GetModule('Mail'); 
		$sXMailer = $oMailModule ? $oMailModule->getConfig('XMailerValue', '') : '';
		if (0 < strlen($sXMailer))
		{
			$oMessage->SetXMailer($sXMailer);
		}

		$oMessage
			->SetFrom(\MailSo\Mime\Email::NewInstance($sFrom))
			->SetSubject($sSubject)
		;

		$oToEmails = \MailSo\Mime\EmailCollection::NewInstance($sTo);
		if ($oToEmails && $oToEmails->Count())
		{
			$oMessage->SetTo($oToEmails);
		}

		$oCcEmails = \MailSo\Mime\EmailCollection::NewInstance($sCc);
		if ($oCcEmails && $oCcEmails->Count())
		{
			$oMessage->SetCc($oCcEmails);
		}

		$oBccEmails = \MailSo\Mime\EmailCollection::NewInstance($sBcc);
		if ($oBccEmails && $oBccEmails->Count())
		{
			$oMessage->SetBcc($oBccEmails);
		}

		return $oMessage;
	}

	/**
	 * @param \CThread $oThread Helpdesk thread object
	 * @param string $sMessageID
	 * @param string $sReferences
	 */
	private function _initMessageIdAndReferences($oThread, &$sMessageID, &$sReferences)
	{
		if ($oThread && 0 < $oThread->PostCount)
		{
			$sReferences = '';
			if (1 < $oThread->PostCount)
			{
				for ($iIndex = 1, $iLen = $oThread->PostCount; $iIndex < $iLen; $iIndex++)
				{
					$sReferences .= ' <'.md5($oThread->IdThread.$oThread->IdTenant.$iIndex).'@hdsystem>';
				}
			}

			$sReferences = trim($sReferences);
			$sMessageID = '<'.md5($oThread->IdThread.$oThread->IdTenant.$oThread->PostCount).'@hdsystem>';
		}
	}

	/**
	 * @param string $sPath
	 * @param string $sFrom
	 * @param string $sTo
	 * @param string $sSubject
	 * @param string $sCc
	 * @param string $sBcc
	 * @param \Aurora\Modules\Core\Classes\User $oHelpdeskThreadOwnerUser Helpdesk user object
	 * @param \Aurora\Modules\Core\Classes\User $oHelpdeskPostOwnerUser Helpdesk user object
	 * @param \CThread $oThread Helpdesk thread object
	 * @param CHelpdeskPost $oPost Helpdesk post object
	 * @param string $sSiteName
	 *
	 * @return \MailSo\Mime\Message
	 */
	private function _buildPostMail($sPath, $sFrom, $sTo, $sSubject, $sCc, $sBcc, $oHelpdeskThreadOwnerUser, $oHelpdeskPostOwnerUser, $oThread, $oPost, $sSiteName)
	{
		$sMessageID = '';
		$sReferences = '';

		$this->_initMessageIdAndReferences($oThread, $sMessageID, $sReferences);

		$oMessage = $this->_buildMail($sFrom, $sTo, $sSubject, $sCc, $sBcc, $sMessageID, $sReferences);

		$this->_addHtmlBodyAndSubjectForPostMessage($sPath, $oMessage, $oHelpdeskThreadOwnerUser, $oHelpdeskPostOwnerUser, $oThread, $oPost, $sSiteName);

		return $oMessage;
	}

	/**
	 * @param string $sPath
	 * @param string $sFrom
	 * @param string $sTo
	 * @param string $sSubject
	 * @param string $sCc
	 * @param string $sBcc
	 * @param \Aurora\Modules\Core\Classes\User $oUser Helpdesk user object
	 * @param string $sSiteName
	 *
	 * @return \MailSo\Mime\Message
	 */
	private function _buildUserMailMail($sPath, $sFrom, $sTo, $sSubject, $sCc, $sBcc, $oUser, $sSiteName)
	{
		$oMessage = $this->_buildMail($sFrom, $sTo, $sSubject, $sCc, $sBcc);
		
		$this->_addHtmlBodyAndSubjectForUserMessage($sPath, $oMessage, $oUser, $sSiteName, $sFrom);

		return $oMessage;
	}

	/**
	 * @param int $iIdTenant
	 * @param string $sSearch
	 *
	 * @return int
	 */
	private function _getOwnerFromSearch($iIdTenant, &$sSearch)
	{
//		$aMatch = array();
//		$sSearch = trim($sSearch);
//		if (0 < strlen($sSearch) && preg_match('/owner:[\s]?([^\s]+@[^\s]+)/', $sSearch, $aMatch) && !empty($aMatch[0]) && !empty($aMatch[1]))
//		{
//			$sSearch = trim(str_replace($aMatch[0], '', $sSearch));
//			$sEmail = trim($aMatch[1]);
//			$oUser = $this->getUserByEmail($iIdTenant, $sEmail);
//			if (!$oUser)
//			{
//				$oUser = $this->getUserByNotificationEmail($iIdTenant, $sEmail);
//			}
//
//			return $oUser ? $oUser->iObjectId : 0;
//		}

		return 0;
	}
	
	public function isAgent(\Aurora\Modules\Core\Classes\User $oUser)
	{
		return !empty($oUser) && $oUser->Role === \Aurora\System\Enums\UserRole::NormalUser;
	}
	
	/**
	 * @param \Aurora\Modules\Core\Classes\User $oUser Helpdesk user object
	 * @param bool $bCreateFromFetcher Default value is **false**.
	 * 
	 * @return bool
	 */
	public function createUser(\Aurora\Modules\Core\Classes\User &$oUser, $bCreateFromFetcher = false)
	{
		$bResult = false;
		try
		{
			if ($oUser->validate())
			{
				if (!$this->isUserExists($oUser))
				{
					if (true)//!$this->oStorage->createUser($oUser))
					{
						throw new \Aurora\System\Exceptions\ManagerException(\Aurora\System\Exceptions\Errs::HelpdeskManager_UserCreateFailed);
					}
					else if (!$oUser->Activated)
					{
						$this->NotifyRegistration($oUser, $bCreateFromFetcher);
					}
				}
				else
				{
					throw new \Aurora\System\Exceptions\ManagerException(\Aurora\System\Exceptions\Errs::HelpdeskManager_UserAlreadyExists);
				}
			}
			
			$bResult = true;
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$bResult = false;
			$this->setLastException($oException);
		}

		return $bResult;
	}

	/**
	 * @param int $iIdTenant
	 * @param int $iHelpdeskUserId
	 * 
	 * @return \Aurora\Modules\Core\Classes\User|false
	 */
	public function getUserById($iIdTenant, $iHelpdeskUserId)
	{
		$oUser = null;
		try
		{
//			$oUser = $this->oStorage->getUserById($iIdTenant, $iHelpdeskUserId);
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$oUser = false;
			$this->setLastException($oException);
		}
		return $oUser;
	}

	/**
	 * @param int $iHelpdeskUserId
	 *
	 * @return \Aurora\Modules\Core\Classes\User|false
	 */
	public function getUserByIdWithoutTenantID($iHelpdeskUserId)
	{
		$oUser = null;
		try
		{
//			$oUser = $this->oStorage->getUserByIdWithoutTenantID($iHelpdeskUserId);
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$oUser = false;
			$this->setLastException($oException);
		}
		return $oUser;
	}

	/**
	 * @param int $iIdTenant
	 * @param string $sActivateHash
	 *
	 * @return \Aurora\Modules\Core\Classes\User|false
	 */
	public function getUserByActivateHash($iIdTenant, $sActivateHash)
	{
		$oUser = null;
		try
		{
//			$oUser = $this->oStorage->getUserByActivateHash($iIdTenant, $sActivateHash);
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$oUser = false;
			$this->setLastException($oException);
		}
		return $oUser;
	}

	/**
	 * @param int $iIdTenant
	 *
	 * @return \Aurora\Modules\Core\Classes\User|false
	 */
	public function getHelpdeskMainSettings($iIdTenant)
	{
		$oApiTenant = $this->GetModule()->oCoreDecorator;
		
		$oTenant = /* @var $oTenant CTenant */ $oApiTenant ? 
			(0 < $iIdTenant ? $oApiTenant->GetTenantById($iIdTenant) : $oApiTenant->GetDefaultGlobalTenant()) : null;

		$sClientIframeUrl = '';
		$sAdminEmail = '';
		$sAdminEmailAccount = '';
		$sAgentIframeUrl = '';
		$sSiteName = '';
		$bStyleAllow = false;
		$sStyleImage = '';
		$sStyleText = '';

		$bFacebookAllow = false;
		$sFacebookId = '';
		$sFacebookSecret = '';
		$bGoogleAllow = false;
		$sGoogleId = '';
		$sGoogleSecret = '';
		$bTwitterAllow = false;
		$sTwitterId = '';
		$sTwitterSecret = '';

		$iHelpdeskFetcherType = 0;

		if ($oTenant)
		{
			$sAdminEmail = $oTenant->{'HelpDesk::AdminEmail'};
			$sAdminEmailAccount = $oTenant->{'HelpDesk::AdminEmailAccount'};
			$sClientIframeUrl = $oTenant->{'HelpDesk::ClientIframeUrl'};
			$sAgentIframeUrl = $oTenant->{'HelpDesk::AgentIframeUrl'};
			$sSiteName = $oTenant->{'HelpDesk::SiteName'};
			$bStyleAllow = $oTenant->{'HelpDesk::StyleAllow'};
			$sStyleImage = $oTenant->{'HelpDesk::StyleImage'};
			$iHelpdeskFetcherType = $oTenant->{'HelpDesk::FetcherType'};
			$sStyleText = $this->getHelpdeskStyleText($oTenant->{'HelpDesk::StyleText'});
		}

		return array(
			'AdminEmail' => $sAdminEmail,
			'AdminEmailAccount' => $sAdminEmailAccount,
			'ClientIframeUrl' => $sClientIframeUrl,
			'AgentIframeUrl' => $sAgentIframeUrl,
			'SiteName' => $sSiteName,
			'StyleAllow' => $bStyleAllow,
			'StyleImage' => $sStyleImage,
			'StyleText' => $sStyleText,

			'HelpdeskFetcherType' => $iHelpdeskFetcherType,

			'FacebookAllow' => $bFacebookAllow,
			'FacebookId' => $sFacebookId,
			'FacebookSecret' => $sFacebookSecret,
			'GoogleAllow' => $bGoogleAllow,
			'GoogleId' => $sGoogleSecret,
			'GoogleSecret' => $sGoogleId,
			'TwitterAllow' => $bTwitterAllow,
			'TwitterId' => $sTwitterId,
			'TwitterSecret' => $sTwitterSecret,
		);
	}

	/**
	 * @param int $iIdTenant
	 * @param array $aExcludeEmails = array()
	 * 
	 * @return array
	 */
	public function getAgentsEmailsForNotification($iIdTenant, $aExcludeEmails = array())
	{
		$aResult = array();
		try
		{
//			$aResult = $this->oStorage->getAgentsEmailsForNotification($iIdTenant, $aExcludeEmails);
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$this->setLastException($oException);
		}
		return $aResult;
	}

	/**
	 * @param int $iIdTenant
	 * @param string $sEmail
	 * 
	 * @return \Aurora\Modules\Core\Classes\User|null|false
	 */
	public function getUserByEmail($iIdTenant, $sEmail)
	{
		$oUser = null;
		try
		{
//			$oUser = $this->oStorage->getUserByEmail($iIdTenant, $sEmail);
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$oUser = false;
			$this->setLastException($oException);
		}
		return $oUser;
	}

	/**
	 * @param int $iIdTenant
	 * @param string $sEmail
	 *
	 * @return \Aurora\Modules\Core\Classes\User|null|false
	 */
	public function getUserByNotificationEmail($iIdTenant, $sEmail)
	{
		$oUser = null;
		try
		{
//			$oUser = $this->oStorage->getUserByNotificationEmail($iIdTenant, $sEmail);
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$oUser = false;
			$this->setLastException($oException);
		}
		return $oUser;
	}

	/**
	 * @param int $iIdTenant
	 * @param string $sSocialId
	 *
	 * @return \Aurora\Modules\Core\Classes\User|null|false
	 */
	public function getUserBySocialId($iIdTenant, $sSocialId)
	{
		$oUser = null;
		try
		{
//			$oUser = $this->oStorage->getUserBySocialId($iIdTenant, $sSocialId);
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$oUser = false;
			$this->setLastException($oException);
		}
		return $oUser;
	}

	/**
	 * @param \Aurora\Modules\Core\Classes\User $oUser
	 *
	 * @return bool
	 */
	//MOVED TO THE MAIN MODULE CLASS
//	public function forgotUser($oHelpdeskAccount)
//	{
//		$this->NotifyForgot($oHelpdeskAccount);
//		return true;
//	}
	
	/**
	 * @param \Aurora\Modules\Core\Classes\User $oUser Helpdesk user object
	 *
	 * @return bool
	 */
	public function isUserExists(\Aurora\Modules\Core\Classes\User $oUser)
	{
		$bResult = false;
		if(!$oUser->SocialId)
		{
			try
			{
//				$bResult = $this->oStorage->isUserExists($oUser);
			}
			catch (\Aurora\System\Exceptions\BaseException $oException)
			{
				$bResult = false;
				$this->setLastException($oException);
			}
		}
		return $bResult;
	}

	/**
	 * @param \Aurora\Modules\Core\Classes\User $oUser Helpdesk user object
     *
	 * @return bool
	 */
	public function updateUser(\Aurora\Modules\Core\Classes\User $oUser)
	{
		$bResult = false;
		try
		{
			if ($oUser->validate())
			{
//				$bResult = $this->oStorage->updateUser($oUser);
				if (!$bResult)
				{
//					$this->moveStorageExceptionToManager();
					throw new \Aurora\System\Exceptions\ManagerException(\Aurora\System\Exceptions\Errs::HelpdeskManager_UserUpdateFailed);
				}
			}
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$bResult = false;
			$this->setLastException($oException);
		}

		return $bResult;
	}

	/**
	 * @param int $iIdTenant
	 * @param int $iIdUser
	 *
	 * @return bool
	 */
	public function setUserAsBlocked($iIdTenant, $iIdUser)
	{
		$bResult = false;
		try
		{
//			$bResult = $this->oStorage->setUserAsBlocked($iIdTenant, $iIdUser);
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$bResult = false;
			$this->setLastException($oException);
		}

		return $bResult;
	}

	/**
	 * @param int $iIdTenant
	 * @param int $iIdHelpdeskUser
	 *
	 * @return bool
	 */
	public function deleteUser($iIdTenant, $iIdHelpdeskUser)
	{
		$bResult = false;
		try
		{
//			$bResult = $this->oStorage->deleteUser($iIdTenant, $iIdHelpdeskUser);
			/*if ($bResult)
			{
				//TODO
			}*/
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$bResult = false;
			$this->setLastException($oException);
		}

		return $bResult;
	}

	/**
	 * @param \Aurora\Modules\Core\Classes\User $oUser Helpdesk user object
	 * @param \CThread $oThread Helpdesk thread object
	 * @param array $aPostIds
	 *
	 * @return bool
	 */
	public function deletePosts(\Aurora\Modules\Core\Classes\User $oUser, $oThread, $aPostIds)
	{
		$bResult = false;
		try
		{
			if ($oThread instanceof \CThread && 0 < count($aPostIds))
			{
//				$bResult = $this->oStorage->deletePosts($oUser, $oThread, $aPostIds);
				if ($bResult)
				{
					$oThread->PostCount = $this->getPostsCount($oThread);
					$bResult = $this->updateThread($oThread);
				}
			}
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$bResult = false;
			$this->setLastException($oException);
		}

		return $bResult;
	}

	/**
	 * @param \Aurora\Modules\Core\Classes\User $oUser
	 * @param int $iThreadId
	 * @return bool
	 */
	public function doesUserOwnThread($oUser, $iThreadId)
	{
		$bResult = false;
		
		try
		{
			$iOffset = 0;
			$iLimit = 0;
			$aFilters = array(
				'$AND' => array(
					'IdThread' => $iThreadId,
					'IdViewer' => $oUser->EntityId,
				),
			);
			$aThreads = $this->oEavManager->getEntities('CThread', array(), $iOffset, $iLimit, $aFilters);
			$bResult = count($aThreads) > 0;
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$this->setLastException($oException);
		}
		
		return $bResult;
	}

	/**
	 * @param \Aurora\Modules\Core\Classes\User $oUser Helpdesk user object
	 * @param array $aPostIds
	 *
	 * @return bool
	 */
	public function verifyPostIdsBelongToUser(\Aurora\Modules\Core\Classes\User $oUser, $aPostIds)
	{
		$bResult = false;
		try
		{
			if (0 < count($aPostIds))
			{
//				$bResult = $this->oStorage->verifyPostIdsBelongToUser($oUser, $aPostIds);
			}
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$bResult = false;
			$this->setLastException($oException);
		}
		return $bResult;
	}

	/**
	 * @param int $iThreadId
	 * @return boolean
	 */
	public function archiveThread($iThreadId)
	{
		$bResult = false;
		
		try
		{
			$mThread = $this->getThread($iThreadId);
			if ($mThread !== false)
			{
				$mThread->IsArchived = true;
				$bResult = $this->oEavManager->saveEntity($mThread);
			}
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$this->setLastException($oException);
		}
		
		return $bResult;
	}
	
	/**
	 * @return bool
	 */
	public function archiveOutdatedThreads()
	{
		$bResult = false;
		try
		{
//			$bResult = $this->oStorage->archiveOutdatedThreads();
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$bResult = false;
			$this->setLastException($oException);
		}
		
		return $bResult;
	}

	/**
	 * @return bool
	 */
	public function notificateOutdatedThreads()
	{
		$bResult = false;
		try
		{
			$iIdOwner = 0;
			$iThreadId = null;
//			$iThreadId = $this->oStorage->notificateOutdatedThreadID($iIdOwner);
			if ($iThreadId && $iIdOwner)
			{
				$oUser = $this->getUserByIdWithoutTenantID($iIdOwner);
				if ($oUser)
				{
					$oThread = $this->getThread($iThreadId);
					if ($oThread)
					{
						$this->notifyOutdated($oThread);
					}
				}
			}
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$bResult = false;
			$this->setLastException($oException);
		}

		return $bResult;
	}

	/**
	 * @param int $iThreadId
	 * @return \CThread|false
	 */
	public function getThread($iThreadId)
	{
		$mThread = false;
		try
		{
			$mThread = $this->oEavManager->getEntity((int)$iThreadId, $this->getModule()->getNamespace() . '\Classes\Thread');
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$this->setLastException($oException);
		}
		return $mThread;
	}
	
	/**
	 * @param int $iTenantID
	 * @param string $sHash
	 *
	 * @return int
	 */
	public function getThreadIdByHash($iTenantID, $sHash)
	{
		$iThreadID = 0;
		try
		{
//			$iThreadID = $this->oStorage->getThreadIdByHash($iTenantID, $sHash);
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$iThreadID = 0;
			$this->setLastException($oException);
		}
		return $iThreadID;
	}

	/**
	 * @param \CThread $oThread Helpdesk thread object
	 *
	 * @return bool
	 */
	public function createThread(\CThread &$oThread)
	{
		$bResult = false;
		try
		{
			$bResult = $this->oEavManager->saveEntity($oThread);
			if (!$bResult)
			{
//				$this->moveStorageExceptionToManager();
				throw new \Aurora\System\Exceptions\ManagerException(\Aurora\System\Exceptions\Errs::HelpdeskManager_ThreadCreateFailed);
			}
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$bResult = false;
			$this->setLastException($oException);
		}

		return $bResult;
	}

	/**
	 * @param \CThread $oThread Helpdesk thread object
	 * @return bool
	 */
	public function updateThread(\CThread $oThread)
	{
		$bResult = false;
		try
		{
			$bResult = $this->oEavManager->saveEntity($oThread);
			if (!$bResult)
			{
//				$this->moveStorageExceptionToManager();
				throw new \Aurora\System\Exceptions\ManagerException(\Aurora\System\Exceptions\Errs::HelpdeskManager_ThreadUpdateFailed);
			}
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$this->setLastException($oException);
		}

		return $bResult;
	}

	/**
	 * @return bool|int
	 */
	public function getNextHelpdeskIdForMonitoring()
	{
		$mResult = false;
		
		try
		{
			$oHelpDeskModule = $this->GetModule();
			$iFetcherTimeLimitMinutes = $oHelpDeskModule ? (int) $oHelpDeskModule->getConfig('FetcherTimeLimitMinutes', 5) : 5;
//			$mResult = $this->oStorage->getNextHelpdeskIdForMonitoring($iFetcherTimeLimitMinutes);

			if (0 >= $mResult)
			{
				$mResult = false;
			}
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$this->setLastException($oException);
		}

		return $mResult;
	}

	/**
	 * @return bool
	 */
	public function startHelpdeskMailboxMonitor()
	{
		$iIdTenant = $this->getNextHelpdeskIdForMonitoring();
		if (false !== $iIdTenant)
		{
//			$this->oStorage->updateHelpdeskFetcherTimer($iIdTenant);
			$this->startMailboxMonitor($iIdTenant);
		}

		return true;
	}

	/**
	 * @return int
	 */
	public function getHelpdeskMailboxLastUid($iIdTenant, $sEmail)
	{
		$iResult = 0;
		try
		{
//			$iResult = $this->oStorage->getHelpdeskMailboxLastUid($iIdTenant, $sEmail);
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$this->setLastException($oException);
		}

		return $iResult;
	}

	/**
	 * @return bool
	 */
	public function setHelpdeskMailboxLastUid($iIdTenant, $sEmail, $iLastUid)
	{
		$bResult = false;
		try
		{
//			$bResult = $this->oStorage->setHelpdeskMailboxLastUid($iIdTenant, $sEmail, $iLastUid);
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$this->setLastException($oException);
		}

		return $bResult;
	}

	/**
	 * @param int $iIdTenant
	 *
	 * @return bool
	 */
	public function startMailboxMonitor($iIdTenant)
	{
		$aMainSettingsData = $this->getHelpdeskMainSettings($iIdTenant);
		if (!empty($aMainSettingsData['AdminEmailAccount']) && 0 < $aMainSettingsData['HelpdeskFetcherType'])
		{
			$oApiUsers = $this->_getApiUsers();
			$oApiMail = $this->_getApiMail();
			
			$oApiFileCache = /* @var $oApiFileCache \Aurora\System\Managers\Filecache */ new \Aurora\System\Managers\Filecache();
			$oApiFilestorage = /* @var $oApiFileCache \CApiFilestorageManager */ \Aurora\System\Api::Manager('filestorage');
			$oApiIntegrator = /* @var $oApiIntegrator \Aurora\Modules\Core\Managers\Integrator */ new \Aurora\Modules\Core\Managers\Integrator();
			
			if ($oApiUsers && $oApiMail && $oApiFileCache)
			{
				$oAccount = $oApiUsers->getAccountByEmail($aMainSettingsData['AdminEmailAccount']);
				if ($oAccount)
				{
					$iPrevLastUid = $this->getHelpdeskMailboxLastUid($iIdTenant, \strtolower($oAccount->Email));
					
					$iLastUid = 0;
					$aData = $oApiMail->getMessagesForHelpdeskSynch($oAccount, 0 < $iPrevLastUid ? $iPrevLastUid + 1 : 0, $iLastUid);
					if (0 < $iLastUid)
					{
						$this->setHelpdeskMailboxLastUid($iIdTenant, \strtolower($oAccount->Email), $iLastUid);
					}

					if (is_array($aData) && 0 < count($aData))
					{
						foreach ($aData as $oMessage)
						{
							$aMatch = array();
							$oFrom = $oMessage->getFrom();
							$aFrom = $oFrom ? $oFrom->GetAsArray() : array();
							$oAttachments = $oMessage->getAttachments();
							$aAttachments = $oAttachments ? $oAttachments->GetAsArray() : array();

							$sSubject = $oMessage->getSubject();
							if (
								is_array($aFrom) && 0 < count($aFrom) && (
								(\Aurora\Modules\HelpDesk\Enums\FetcherType::REPLY === $aMainSettingsData['HelpdeskFetcherType'] && !empty($sSubject) && preg_match('/\[#([a-zA-Z0-9]+)#\]/', $sSubject, $aMatch))
									||
								(\Aurora\Modules\HelpDesk\Enums\FetcherType::ALL === $aMainSettingsData['HelpdeskFetcherType'])
							))
							{
								$sThreadHash = '';
								$aMatch = array();
								if (preg_match('/\[#([a-zA-Z0-9]+)#\]/', $sSubject, $aMatch) && !empty($aMatch[1]))
								{
									$sThreadHash = (string) $aMatch[1];
								}

								$oEmail = $aFrom[0];
								$sEmail = $oEmail ? $oEmail->GetEmail() : '';
								$oUser = null;
								
								if (0 < \strlen($sEmail))
								{
									$oUser = $this->getUserByEmail($iIdTenant, $sEmail);
									if (!$oUser)
									{
										$sPassword = md5(microtime(true));
										$oUser = $oApiIntegrator->registerHelpdeskAccount($iIdTenant, $sEmail, '', $sPassword, true);
									}
								
									if ($oUser)
									{
										$oThread = null;
										if (!empty($sThreadHash))
										{
											$iThreadId = $this->getThreadIdByHash($iIdTenant, $sThreadHash);
											if (0 < $iThreadId)
											{
												$oThread = $this->getThread($iThreadId);
											}
										}
										else
										{
											$oThread = \CThread::createInstance('CThread', $this->GetModule()->GetName());
											$oThread->IdTenant = $iIdTenant;
											$oThread->IdOwner = $oUser->iObjectId;
											$oThread->Type = \Aurora\Modules\HelpDesk\Enums\ThreadType::Pending;
											$oThread->Subject = $sSubject;

											if (!$this->createThread($oUser, $oThread))
											{
												$oThread = null;
											}
										}

										if ($oThread)
										{
											$sText = trim($oMessage->getHtml());
											if (0 === strlen($sText))
											{
												$sText = trim($oMessage->getPlain());
											}
											else
											{
												$sText = \MailSo\Base\HtmlUtils::ConvertHtmlToPlain($sText);
											}

											$oPost = new \CHelpdeskPost();
											$oPost->IdTenant = $oUser->IdTenant;
											$oPost->IdOwner = $oUser->iObjectId;
											$oPost->IdThread = $oThread->IdThread;
											$oPost->Type = \Aurora\Modules\HelpDesk\Enums\PostType::Normal;
											$oPost->SystemType = \Aurora\Modules\HelpDesk\Enums\PostSystemType::None;
											$oPost->Text = $sText;

											$aResultAttachment = array();
											if (is_array($aAttachments) && 0 < count($aAttachments))
											{
												foreach ($aAttachments as /* @var $oAttachment \Aurora\Modules\Mail\Classes\Attachment */ $oAttachment)
												{
													$sUploadName = $oAttachment->getFileName(true);
													$sTempName = md5($sUploadName.rand(1000, 9999));

													$oApiMail->directMessageToStream($oAccount,
														function($rResource, $sContentType, $sFileName, $sMimeIndex = '') use ($oUser, &$sTempName, $oApiFileCache) {

															if (!$oApiFileCache->putFile($oUser, $sTempName, $rResource))
															{
																$sTempName = '';
															}

														}, $oAttachment->getFolder(), $oAttachment->getUid(), $oAttachment->MimeIndex());


													$rData = 0 < \strlen($sTempName) ? $oApiFileCache->getFile($oUser, $sTempName) : null;
													if ($rData)
													{
														$iFileSize = $oApiFileCache->fileSize($oUser, $sTempName);

														$sThreadID = (string) $oThread->IdThread;
														$sThreadID = str_pad($sThreadID, 2, '0', STR_PAD_LEFT);
														$sThreadIDSubFolder = substr($sThreadID, 0, 2);

														$sThreadFolderName = AU_API_HELPDESK_PUBLIC_NAME.'/'.$sThreadIDSubFolder.'/'.$sThreadID;

														$oApiFilestorage->createFolder($oUser, \Aurora\System\Enums\FileStorageType::Corporate, '',
															$sThreadFolderName);

														$oApiFilestorage->createFile($oUser,
															\Aurora\System\Enums\FileStorageType::Corporate, $sThreadFolderName, $sUploadName, $rData, false);

														if (is_resource($rData))
														{
															@fclose($rData);
														}

														$oAttachment = \CHelpdeskAttachment::createInstance('CHelpdeskAttachment', $this->GetModule()->GetName());
														$oAttachment->IdThread = $oThread->IdThread;
														$oAttachment->IdPost = $oPost->IdPost;
														$oAttachment->IdOwner = $oUser->iObjectId;
														$oAttachment->IdTenant = $oUser->IdTenant;

														$oAttachment->FileName = $sUploadName;
														$oAttachment->SizeInBytes = $iFileSize;
														$oAttachment->encodeHash($oUser, $sThreadFolderName);

														$oApiFileCache->clear($oUser, $sTempName);

														$aResultAttachment[] = $oAttachment;
													}
												}

												if (is_array($aResultAttachment) && 0 < count($aResultAttachment))
												{
													$oPost->Attachments = $aResultAttachment;
												}
											}

											$this->createPost($oUser, $oThread, $oPost, false, false);
										}
									}
								}
							}
							
							unset($oMessage);
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * @param int $iIdTenant
	 *
	 * @return bool
	 */
	public function startMailboxMonitorPrev($iIdTenant)
	{
		$aData = $this->getHelpdeskMainSettings($iIdTenant);
		if (!empty($aData['AdminEmailAccount']) && 0 < $aData['HelpdeskFetcherType'])
		{
			$oApiUsers = $this->_getApiUsers();
			$oApiMail = $this->_getApiMail();
			$oApiFileCache = /* @var $oApiFileCache \Aurora\System\Managers\Filecache */ new \Aurora\System\Managers\Filecache();
			$oApiFilestorage = /* @var $oApiFileCache \CApiFilestorageManager */ \Aurora\System\Api::Manager('filestorage');

			if ($oApiUsers && $oApiMail && $oApiFileCache)
			{
				$oAccount = $oApiUsers->getAccountByEmail($aData['AdminEmailAccount']);
				if ($oAccount)
				{
					$iPrevLastUid = $this->getHelpdeskMailboxLastUid($iIdTenant, \strtolower($oAccount->Email));

					$iLastUid = 0;
					$aData = $oApiMail->getMessagesForHelpdeskSynch($oAccount, 0 < $iPrevLastUid ? $iPrevLastUid + 1 : 0, $iLastUid);
					if (0 < $iLastUid)
					{
						$this->setHelpdeskMailboxLastUid($iIdTenant, \strtolower($oAccount->Email), $iLastUid);
					}

					if (is_array($aData) && 0 < count($aData))
					{
						foreach ($aData as $oMessage)
						{
							$aMatch = array();
							$oFrom = $oMessage->getFrom();
							$aFrom = $oFrom ? $oFrom->GetAsArray() : array();
							$oAttachments = $oMessage->getAttachments();
							$aAttachments = $oAttachments ? $oAttachments->GetAsArray() : array();

							$sSubject = $oMessage->getSubject();
							if (is_array($aFrom) && 0 < count($aFrom) && !empty($sSubject) &&
								preg_match('/\[#([a-zA-Z0-9]+)#\]/', $sSubject, $aMatch) && !empty($aMatch[1]))
							{
								$oEmail = $aFrom[0];
								$sEmail = $oEmail ? $oEmail->GetEmail() : '';
								if (0 < \strlen($sEmail))
								{
									$oUser = $this->getUserByEmail($iIdTenant, $sEmail);
									if ($oUser)
									{
										$sThreadHash = (string) $aMatch[1];
										if (!empty($sThreadHash))
										{
											$iThreadId = $this->getThreadIdByHash($iIdTenant, $sThreadHash);
											if (0 < $iThreadId)
											{
												$oThread = $this->getThread($iThreadId);
												if ($oThread)
												{
													$sText = trim($oMessage->getHtml());
													if (0 === strlen($sText))
													{
														$sText = trim($oMessage->getPlain());
													}
													else
													{
														$sText = \MailSo\Base\HtmlUtils::ConvertHtmlToPlain($sText);
													}

													$oPost = new \CHelpdeskPost();
													$oPost->IdTenant = $oUser->IdTenant;
													$oPost->IdOwner = $oUser->iObjectId;
													$oPost->IdThread = $oThread->IdThread;
													$oPost->Type = \Aurora\Modules\HelpDesk\Enums\PostType::Normal;
													$oPost->SystemType = \Aurora\Modules\HelpDesk\Enums\PostSystemType::None;
													$oPost->Text = $sText;

													$aResultAttachment = array();
													if (is_array($aAttachments) && 0 < count($aAttachments))
													{
														foreach ($aAttachments as /* @var $oAttachment \Aurora\Modules\Mail\Classes\Attachment */ $oAttachment)
														{
															$sUploadName = $oAttachment->getFileName(true);
															$sTempName = md5($sUploadName.rand(1000, 9999));

															$oApiMail->directMessageToStream($oAccount,
																function($rResource, $sContentType, $sFileName, $sMimeIndex = '') use ($oUser, &$sTempName, $oApiFileCache) {

																	if (!$oApiFileCache->putFile($oUser, $sTempName, $rResource))
																	{
																		$sTempName = '';
																	}

																}, $oAttachment->getFolder(), $oAttachment->getUid(), $oAttachment->MimeIndex());


															$rData = 0 < \strlen($sTempName) ? $oApiFileCache->getFile($oUser, $sTempName) : null;
															if ($rData)
															{
																$iFileSize = $oApiFileCache->fileSize($oUser, $sTempName);

																$sThreadID = (string) $oThread->IdThread;
																$sThreadID = str_pad($sThreadID, 2, '0', STR_PAD_LEFT);
																$sThreadIDSubFolder = substr($sThreadID, 0, 2);

																$sThreadFolderName = AU_API_HELPDESK_PUBLIC_NAME.'/'.$sThreadIDSubFolder.'/'.$sThreadID;

																$oApiFilestorage->createFolder($oUser, \Aurora\System\Enums\FileStorageType::Corporate, '',
																	$sThreadFolderName);

																$oApiFilestorage->createFile($oUser,
																	\Aurora\System\Enums\FileStorageType::Corporate, $sThreadFolderName, $sUploadName, $rData, false);

																if (is_resource($rData))
																{
																	@fclose($rData);
																}

																$oAttachment = \CHelpdeskAttachment::createInstance('CHelpdeskAttachment', $this->GetModule()->GetName());
																$oAttachment->IdThread = $oThread->IdThread;
																$oAttachment->IdPost = $oPost->IdPost;
																$oAttachment->IdOwner = $oUser->iObjectId;
																$oAttachment->IdTenant = $oUser->IdTenant;

																$oAttachment->FileName = $sUploadName;
																$oAttachment->SizeInBytes = $iFileSize;
																$oAttachment->encodeHash($oUser, $sThreadFolderName);

																$oApiFileCache->clear($oUser, $sTempName);

																$aResultAttachment[] = $oAttachment;
															}
														}

														if (is_array($aResultAttachment) && 0 < count($aResultAttachment))
														{
															$oPost->Attachments = $aResultAttachment;
														}
													}

													$this->createPost($oUser, $oThread, $oPost, false, false);
												}
											}
										}
									}
								}
							}

							unset($oMessage);
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * @param \Aurora\Modules\Core\Classes\User $oUser Core user object
	 * @param int $iFilter Default value is **0** \Aurora\Modules\HelpDesk\Enums\ThreadFilterType::All.
	 * @param string $sSearch = ''
	 * 
	 * @return int
	 */
	public function getThreadsCount(\Aurora\Modules\Core\Classes\User $oUser, $iFilter = \Aurora\Modules\HelpDesk\Enums\ThreadFilterType::All, $sSearch = '')
	{
		$iResult = 0;
		try
		{
			$aFilters = $this->_getFilters($oUser, $iFilter, $sSearch);
			return $this->oEavManager->getEntitiesCount('CThread', $aFilters);
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$this->setLastException($oException);
		}

		return $iResult;
	}

	/**
	 * @param int $iTenantId Default value is **0**.
	 *
	 * @return int
	 */
	public function getThreadsPendingCount($iTenantId)
	{
		$iResult = 0;
		try
		{
//			$iResult = $this->oStorage->getThreadsPendingCount($iTenantId);
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$this->setLastException($oException);
		}

		return $iResult;
	}

	protected function _getFilters(\Aurora\Modules\Core\Classes\User $oUser, $iFilter = \Aurora\Modules\HelpDesk\Enums\ThreadFilterType::All, $sSearch = '')
	{
		$bIsAgent = $this->isAgent($oUser);
		$iSearchOwner = $this->_getOwnerFromSearch($oUser->IdTenant, $sSearch);
		
		$aFilters = array(
			'IdTenant' => $oUser->IdTenant,
			'IsArchived' => \Aurora\Modules\HelpDesk\Enums\ThreadFilterType::Archived === $iFilter,
		);

		if (\Aurora\Modules\HelpDesk\Enums\ThreadFilterType::Archived !== $iFilter)
		{
			switch ($iFilter)
			{
				case \Aurora\Modules\HelpDesk\Enums\ThreadFilterType::PendingOnly:
					$aFilters['Type'] = array(array(\Aurora\Modules\HelpDesk\Enums\ThreadType::Pending, \Aurora\Modules\HelpDesk\Enums\ThreadType::Deferred), 'IN');
					break;
				case \Aurora\Modules\HelpDesk\Enums\ThreadFilterType::ResolvedOnly:
					$aFilters['Type'] = array(\Aurora\Modules\HelpDesk\Enums\ThreadType::Resolved, '=');
					break;
				case \Aurora\Modules\HelpDesk\Enums\ThreadFilterType::Open:
					if ($bIsAgent)
					{
						$aFilters['$OR'] = array(
							'Type' => array(array(\Aurora\Modules\HelpDesk\Enums\ThreadType::Pending, \Aurora\Modules\HelpDesk\Enums\ThreadType::Deferred, \Aurora\Modules\HelpDesk\Enums\ThreadType::Waiting), 'IN'),
							'$AND' => array(
								'IdOwner' => array($oUser->EntityId, '='),
								'Type' => array(\Aurora\Modules\HelpDesk\Enums\ThreadType::Answered, '='),
							)
						);
					}
					else
					{
						$aFilters['Type'] = array(array(\Aurora\Modules\HelpDesk\Enums\ThreadType::Waiting, \Aurora\Modules\HelpDesk\Enums\ThreadType::Answered, \Aurora\Modules\HelpDesk\Enums\ThreadType::Pending, \Aurora\Modules\HelpDesk\Enums\ThreadType::Deferred), 'IN');
					}
					break;
				case \Aurora\Modules\HelpDesk\Enums\ThreadFilterType::InWork:
					$aFilters['Type'] = array(array(\Aurora\Modules\HelpDesk\Enums\ThreadType::Waiting, \Aurora\Modules\HelpDesk\Enums\ThreadType::Answered, \Aurora\Modules\HelpDesk\Enums\ThreadType::Pending, \Aurora\Modules\HelpDesk\Enums\ThreadType::Deferred), 'IN');
					break;
			}
		}

		if (!$bIsAgent)
		{
			$aFilters['IdOwner'] = array($oUser->EntityId, '=');
		}

		if (0 < $iSearchOwner)
		{
			$aFilters['IdOwner'] = array($iSearchOwner, '=');
		}

		// TODO: search

		return array('$AND' => $aFilters);
	}
	
	/**
	 * @param \Aurora\Modules\Core\Classes\User $oUser Core user object
	 * @param int $iOffset Default value is **0**.
	 * @param int $iLimit Default value is **20**.
	 * @param int $iFilter Default value is **0** \Aurora\Modules\HelpDesk\Enums\ThreadFilterType::All
	 * @param string $sSearch Default value is empty string.
	 *
	 * @return array|bool
	 */
	public function getThreads(\Aurora\Modules\Core\Classes\User $oUser, $iOffset = 0, $iLimit = 20, $iFilter = \Aurora\Modules\HelpDesk\Enums\ThreadFilterType::All, $sSearch = '')
	{
		$aResult = null;
		try
		{
			$sOrderBy = 'Updated';
			$iOrderType = \Aurora\System\Enums\SortOrder::DESC;
			$aFilters = $this->_getFilters($oUser, $iFilter, $sSearch);
			$aResult = $this->oEavManager->getEntities('CThread', array(), $iOffset, $iLimit, $aFilters, $sOrderBy, $iOrderType);
			if (is_array($aResult) && 0 < count($aResult))
			{
				$aThreadsIdList = array();
				foreach ($aResult as $oItem)
				{
					$aThreadsIdList[] = $oItem->IdThread;
				}
			}
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$this->setLastException($oException);
		}

		return $aResult;
	}
	
	/**
	 * @param \CThread $oThread Helpdesk thread object
	 * @return int
	 */
	public function getPostsCount($oThread)
	{
		$iResult = 0;
		try
		{
			$iResult = $this->oEavManager->getEntitiesCount('CPost', array('IdThread' => $oThread->EntityId));
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$this->setLastException($oException);
		}

		return $iResult;
	}

	/**
	 * @param \Aurora\Modules\Core\Classes\User $oUser Helpdesk user object
	 * @param \CThread $oThread Helpdesk thread object
	 *
	 * @return array|bool
	 */
	public function getAttachments(\Aurora\Modules\Core\Classes\User $oUser, \CThread $oThread)
	{
		$aResult = null;
		try
		{
//			$aResult = $this->oStorage->getAttachments($oUser, $oThread);
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$this->setLastException($oException);
		}

		return $aResult;
	}

	/**
	 * @param \CThread $oThread Helpdesk thread object
	 * @param int $iStartFromId Default value is **0**.
	 * @param int $iLimit Default value is **20**.
	 * @return array|boolean
	 */
	public function getPosts($oThread, $iStartFromId = 0, $iLimit = 20)
	{
		$aResult = null;
		try
		{
			$aFilters = array('IdThread' => array($oThread->EntityId, '='));
			if ($iStartFromId > 0)
			{
				$aFilters = array('$AND' => array(
					'IdThread' => array($oThread->EntityId, '='),
					'IdPost' => array($iStartFromId, '<')
				));
			}
			$iOffset = 0;
			$aResult = $this->oEavManager->getEntities('CPost', array(), $iOffset, $iLimit, $aFilters, 'Created', \Aurora\System\Enums\SortOrder::DESC);
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$this->setLastException($oException);
		}

		return $aResult;
	}

	/**
	 * @param \Aurora\Modules\Core\Classes\User $oUser Helpdesk user object
	 * @param \CThread $oThread Helpdesk thread object
	 *
	 * @return array|bool
	 */
	public function getExtPostsCount(\Aurora\Modules\Core\Classes\User $oUser, $oThread)
	{
		$aResult = null;
		try
		{
//			$aResult = $this->oStorage->getExtPostsCount($oUser, $oThread);
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$this->setLastException($oException);
		}

		return $aResult;
	}

	/**
	 * @param \Aurora\Modules\Core\Classes\User $oUser Helpdesk user object
	 */
	//MOVED TO THE MAIN MODULE CLASS
//	public function NotifyForgot($oHelpdeskAccount)
//	{
//		if ($oHelpdeskAccount)
//		{
//			$oFromAccount = null;
//			
//			$oUser = $this->oCoreDecorator->GetUser($oHelpdeskAccount->IdUser);
//			$aData = $this->getHelpdeskMainSettings($oUser->IdTenant);
//			if (!empty($aData['AdminEmailAccount']))
//			{
//				$oApiUsers = $this->_getApiUsers();
//				if ($oApiUsers)
//				{
//					$oFromAccount = $oApiUsers->getAccountByEmail($aData['AdminEmailAccount']);
//				}
//			}
//
//			$sSiteName = isset($aData['SiteName']) ? $aData['SiteName'] : '';
//
//			if ($oFromAccount)
//			{
//				$oApiMail = $this->_getApiMail();
//				if ($oApiMail)
//				{
//					$sEmail = $oHelpdeskAccount->resultEmail();
//					if (!empty($sEmail))
//					{
//						$oFromEmail = \MailSo\Mime\Email::NewInstance($oFromAccount->Email, $sSiteName);
//						$oToEmail = \MailSo\Mime\Email::NewInstance($sEmail, $oHelpdeskAccount->Name);
//
//						$oUserMessage = $this->_buildUserMailMail(AU_APP_ROOT_PATH.'templates/helpdesk/user.forgot.html',
//							$oFromEmail->ToString(), $oToEmail->ToString(),
//							'Forgot', '', '', $oHelpdeskAccount, $sSiteName);
//
//						$oApiMail->sendMessage($oFromAccount, $oUserMessage);
//					}
//				}
//			}
//		}
//	}
	
	/**
	 * @param \Aurora\Modules\Core\Classes\User $oUser Helpdesk user object
	 * @param bool $bCreateFromFetcher Default value is **false**.
	 *
	 * @return bool
	 */
	public function NotifyRegistration($oUser, $bCreateFromFetcher = false)
	{
		if ($oUser)
		{
			$oFromAccount = null;
			$aData = $this->getHelpdeskMainSettings($oUser->User->IdTenant);
			if (!empty($aData['AdminEmailAccount']))
			{
				$oApiUsers = $this->_getApiUsers();
				if ($oApiUsers)
				{
					$oFromAccount = $oApiUsers->getAccountByEmail($aData['AdminEmailAccount']);
				}
			}

			$sSiteName = isset($aData['SiteName']) ? $aData['SiteName'] : '';

			if ($oFromAccount)
			{
				$oApiMail = $this->_getApiMail();
				if ($oApiMail)
				{
					$sEmail = $oUser->resultEmail();
					if (!empty($sEmail))
					{
						$oFromEmail = \MailSo\Mime\Email::NewInstance($oFromAccount->Email, $sSiteName);
						$oToEmail = \MailSo\Mime\Email::NewInstance($sEmail, $oUser->Name);

						$oUserMessage = $this->_buildUserMailMail(AU_APP_ROOT_PATH.'templates/helpdesk/user.registration'.($bCreateFromFetcher ? '.fetcher' : '').'.html',
							$oFromEmail->ToString(), $oToEmail->ToString(), 'Registration', '', '', $oUser, $sSiteName);

						$oApiMail->sendMessage($oFromAccount, $oUserMessage);
					}
				}
			}
		}
	}

	/**
	 * @param \CThread $oThread Helpdesk thread object
	 * @param \CPost $oPost Helpdesk post object
	 * @param bool $bIsNew Default value is **false**.
	 * @param string $sCc Default value is empty string.
	 * @param string $sBcc Default value is empty string.
	 */
	public function sendPostNotify($oThread, $oPost, $bIsNew = false, $sCc = '', $sBcc = '')
	{
		if ($oThread && $oPost)
		{
			$oFromAccount = null;

			$aData = $this->getHelpdeskMainSettings($oPost->IdTenant);
			if (!empty($aData['AdminEmailAccount']))
			{
				$oApiUsers = $this->_getApiUsers();
				if ($oApiUsers)
				{
					$oFromAccount = $oApiUsers->getAccountByEmail($aData['AdminEmailAccount']);
				}
			}

			$sSiteName = isset($aData['SiteName']) ? $aData['SiteName'] : '';

			$oThreadOwnerUser = $this->getUserById($oThread->IdTenant, $oThread->IdOwner);

			// mail notifications
			if ($oFromAccount && $oThreadOwnerUser)
			{
				$oApiMail = $this->_getApiMail();
				if ($oApiMail)
				{
					$oHelpdeskPostOwnerUser = $this->getUserById($oPost->IdTenant, $oPost->IdOwner);

					$aDeMail = array();
					$sEmail = $oThreadOwnerUser->resultEmail();
					if (!empty($sEmail))
					{
						$oHelpdeskSenderEmail = \MailSo\Mime\Email::NewInstance($oFromAccount->Email, $sSiteName);
						$oThreadOwnerEmail = \MailSo\Mime\Email::NewInstance($sEmail, $oThreadOwnerUser->Name);

						if (\Aurora\Modules\HelpDesk\Enums\PostType::Normal === $oPost->Type && ($bIsNew || $oThreadOwnerUser->iObjectId !== $oPost->IdOwner))
						{
							$oUserMessage = $this->_buildPostMail(AU_APP_ROOT_PATH.'templates/helpdesk/user.post'.($bIsNew ? '.new' : '').'.html',
								$oHelpdeskSenderEmail->ToString(), $oThreadOwnerEmail->ToString(),
								'New Post', $sCc, $sBcc, $oThreadOwnerUser, $oHelpdeskPostOwnerUser, $oThread, $oPost, $sSiteName);

							if ($oUserMessage)
							{
								$aDeMail[] = $oThreadOwnerUser->resultEmail();
								$oApiMail->sendMessage($oFromAccount, $oUserMessage);
							}
						}

						if (\Aurora\Modules\HelpDesk\Enums\PostType::Internal === $oPost->Type || $oThreadOwnerUser->IobjectId === $oPost->IdOwner)
						{
							$aDeMail[] = $oThreadOwnerUser->resultEmail();
						}

						if (0 < count($aDeMail))
						{
							$aDeMail = array_unique($aDeMail);
						}

						$aAgents = $this->getAgentsEmailsForNotification($oPost->IdTenant, $aDeMail);
						if (is_array($aAgents) && 0 < count($aAgents))
						{
							$oAgentMessage = $this->_buildPostMail(AU_APP_ROOT_PATH.'templates/helpdesk/agent.post.html',
								$oHelpdeskSenderEmail->ToString(), is_array($aAgents) && 0 < count($aAgents) ? implode(', ', $aAgents) : '',
								'New Post', $sCc, $sBcc, $oThreadOwnerUser, $oHelpdeskPostOwnerUser, $oThread, $oPost, $sSiteName);

							if ($oAgentMessage)
							{
								$oApiMail->sendMessage($oFromAccount, $oAgentMessage);
							}
						}
					}
				}
			}
		}
	}

	/**
	 * @param \CThread $oThread Helpdesk thread object
	 */
	public function notifyOutdated($oThread)
	{
		if ($oThread)
		{
			$oFromAccount = null;

			$aData = $this->getHelpdeskMainSettings($oThread->IdTenant);
			if (!empty($aData['AdminEmailAccount']))
			{
				$oApiUsers = $this->_getApiUsers();
				if ($oApiUsers)
				{
					$oFromAccount = $oApiUsers->getAccountByEmail($aData['AdminEmailAccount']);
				}
			}

			$sSiteName = isset($aData['SiteName']) ? $aData['SiteName'] : '';

			$oThreadOwnerUser = $this->getUserById($oThread->IdTenant, $oThread->IdOwner);

			// mail notifications
			if ($oFromAccount && $oThreadOwnerUser)
			{
				$oApiMail = $this->_getApiMail();
				if ($oApiMail)
				{
					$oHelpdeskPostOwnerUser = $this->getUserById($oThread->IdTenant, $oThread->IdOwner);

					$sEmail = $oThreadOwnerUser->resultEmail();
					if (!empty($sEmail))
					{
						$oHelpdeskSenderEmail = \MailSo\Mime\Email::NewInstance($oFromAccount->Email, $sSiteName);
						$oThreadOwnerEmail = \MailSo\Mime\Email::NewInstance($sEmail, $oThreadOwnerUser->Name);

						if ($oThreadOwnerUser->iObjectId === $oThread->IdOwner)
						{
							$oUserMessage = $this->_buildPostMail(AU_APP_ROOT_PATH.'templates/helpdesk/user.post.notification.html',
								$oHelpdeskSenderEmail->ToString(), $oThreadOwnerEmail->ToString(),
								'New Post', '', '', $oThreadOwnerUser, $oHelpdeskPostOwnerUser, $oThread, null, $sSiteName);

							if ($oUserMessage)
							{
								$oApiMail->sendMessage($oFromAccount, $oUserMessage);
							}
						}
					}
				}
			}
		}
	}

	/**
	 * @param \Aurora\Modules\Core\Classes\User $oUser Core user object
	 * @param \CThread $oThread Helpdesk thread object
	 * @param \CPost $oPost Helpdesk post object
	 * @param bool $bIsNew Default value is **false**.
	 * @param bool $bSendNotify Default value is **true**.
	 * @param string $sCc Default value is empty string.
	 * @param string $sBcc Default value is empty string.
	 *
	 * @return bool
	 */
	public function createPost(\Aurora\Modules\Core\Classes\User $oUser, $oThread, \CPost $oPost, $bIsNew = false, $bSendNotify = true, $sCc = '', $sBcc = '')
	{
		$bResult = false;
		try
		{
			if ($oPost->Type === \Aurora\Modules\HelpDesk\Enums\PostType::Internal && !$this->isAgent($oUser))
			{
				$oPost->Type = \Aurora\Modules\HelpDesk\Enums\PostType::Normal;
			}

			if ($this->isAgent($oUser) && !$bIsNew && $oUser->EntityId !== $oThread->IdOwner)
			{
				if ($oPost->Type !== \Aurora\Modules\HelpDesk\Enums\PostType::Internal)
				{
					$oThread->Type = \Aurora\Modules\HelpDesk\Enums\ThreadType::Answered;
				}
			}
			else
			{
				$oThread->Type = \Aurora\Modules\HelpDesk\Enums\ThreadType::Pending;
			}

			$bResult = $this->oEavManager->saveEntity($oPost);
			if (!$bResult)
			{
//				$this->moveStorageExceptionToManager();
				throw new \Aurora\System\Exceptions\ManagerException(\Aurora\System\Exceptions\Errs::HelpdeskManager_PostCreateFailed);
			}
			else
			{
				$oPost->IdPost = $oPost->EntityId;
				$this->oEavManager->saveEntity($oPost);
//				if (is_array($oPost->Attachments) && 0 < count($oPost->Attachments))
//				{
//					$this->oStorage->addAttachments($oUser, $oThread, $oPost, $oPost->Attachments);
//				}

				$oThread->Updated = date('Y-m-d H:i:s');
				$oThread->PostCount = $this->getPostsCount($oThread);
				$oThread->Notificated = false;
				$oThread->resetUsersRead($oPost->IdOwner);

				if (!$oThread->HasAttachments)
				{
					$oThread->HasAttachments = is_array($oPost->Attachments) && 0 < count($oPost->Attachments);
				}

				$bResult = $this->updateThread($oThread);

//				if ($bSendNotify)
//				{
//					$this->sendPostNotify($oThread, $oPost, $bIsNew, $sCc, $sBcc);
//				}
//
//				if (!empty($sCc) || !empty($sBcc))
//				{
//					//$this->sendPostCopy($oThread, $oPost, $bIsNew, $sCc, $sBcc);
//				}
			}
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			if ($oException->getCode() !== \Aurora\System\Exceptions\Errs::Mail_MailboxUnavailable)
			{
				$bResult = false;
				$this->setLastException($oException);
			}
			else
			{
				$bResult = true;
			}
		}

		return $bResult;
	}

	/**
	 * @param int $iUserId
	 * @param \CThread $oThread Helpdesk thread object
	 *
	 * @return bool
	 */
	public function setThreadSeen($iUserId, $oThread)
	{
		$bResult = false;
		try
		{
			$oThread->addUserRead($iUserId);
			$bResult = $this->oEavManager->saveEntity($oThread);
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$this->setLastException($oException);
		}
		return $bResult;
	}

	/**
	 * @return bool
	 */
	public function clearUnregistredUsers()
	{
		$mResult = false;
		try
		{
//			$mResult = $this->oStorage->clearUnregistredUsers();
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$this->setLastException($oException);
		}

		return $mResult;
	}
	
	/**
	 * @return string
	 */
	public function getHelpdeskStyleText($sStyleText = '')
	{
		return '' !== $sStyleText ? base64_decode($sStyleText) : '';
	}

	/**
	 * @param string $sStyle
	 */
//	public function setHelpdeskStyleText($sStyle)
//	{
//		$sStyle = trim($sStyle);
//		$this->HelpdeskStyleText = ('' !== $sStyle) ? base64_encode($sStyle) : '';
//	}
}
