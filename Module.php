<?php
/**
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 */
 
namespace Aurora\Modules\HelpDesk;

/**
 * @license https://afterlogic.com/products/common-licensing AfterLogic Software License
 * @copyright Copyright (c) 2018, Afterlogic Corp.
 *
 * @package Modules
 */
class Module extends \Aurora\System\Module\AbstractLicensedModule
{
	public $oCurrentAccount = null;
	
	public $oCurrentUser = null;
	
	public $oMainManager = null;
	
	public $oAccountsManager = null;
	
	public $oCoreDecorator = null;
	
	public $oAuthDecorator = null;
	
	public function init() 
	{
		$this->oMainManager = new Managers\Main\Manager('', $this);
		$this->oAccountsManager = new Managers\Accounts\Manager('', $this);
		
		$this->oCoreDecorator = \Aurora\Modules\Core\Module::Decorator();
		$this->oAuthDecorator = \Aurora\Modules\StandardAuth\Module::Decorator();
		
		\Aurora\Modules\Core\Classes\User::extend(
			self::GetName(),
			[
				'AllowEmailNotifications'	=> array('bool', $this->getConfig('AllowEmailNotifications', false)),
				'Signature'					=> array('bool', $this->getConfig('Signature', '')),
				'UseSignature'				=> array('bool', $this->getConfig('UseSignature', false)),
			]
		);

		\Aurora\Modules\Core\Classes\Tenant::extend(
			self::GetName(),
			[
				'AdminEmail'		=> array('string', ''),
				'AdminEmailAccount'	=> array('string', ''),
				'ClientIframeUrl'	=> array('string', ''),
				'AgentIframeUrl'	=> array('string', ''),
				'SiteName'			=> array('string', ''),
				'StyleAllow'		=> array('bool', false),
				'StyleImage'		=> array('string', ''),
				'FetcherType'		=> array('int', Enums\FetcherType::NONE),
				'StyleText'			=> array('string', ''),
				'AllowFetcher'		=> array('bool', false),
				'FetcherTimer'		=> array('int', 0)
			]
		);

//		$this->subscribeEvent('HelpDesk::Login', array($this, 'checkAuth'));
	}
	
	/**
	 * Obtains list of module settings for authenticated user.
	 * 
	 * @return array
	 */
	public function GetSettings()
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::Anonymous);
		
		return array(
			'ActivatedEmail' => $this->getConfig('ActivatedEmail', ''),
			'AllowEmailNotifications' => $this->getConfig('AllowEmailNotifications', false),
			'AllowFacebookAuth' => $this->getConfig('AllowFacebookAuth', true),
			'AllowGoogleAuth' => $this->getConfig('AllowGoogleAuth', true),
			'AllowTwitterAuth' => $this->getConfig('AllowTwitterAuth', true),
			'AfterThreadsReceivingAction' => $this->getConfig('AfterThreadsReceivingAction', 'add'), // add, close
			'ClientDetailsUrl' => $this->getConfig('ClientDetailsUrl', ''),
			'ClientSiteName' => $this->getConfig('ClientSiteName', ''),
			'IsAgent' => $this->isAgent(),
			'ForgotHash' => $this->getConfig('ForgotHash', ''),
			'LoginLogoUrl' => $this->getConfig('LoginLogoUrl', ''),
			'SelectedThreadId' => $this->getConfig('SelectedThreadId', 0),
			'Signature' => $this->getConfig('Signature', ''),
			'SocialEmail' => $this->getConfig('SocialEmail', ''),
			'SocialIsLoggedIn' => false,
			'ThreadsPerPage' => $this->getConfig('ThreadsPerPage', 10),
			'UserEmail' => $this->getConfig('UserEmail', ''), // AppData.User.Email
			'UseSignature' => $this->getConfig('UseSignature', true),
		);
	}
	
	/**
	 * TODO it must set extended properties of tenant
	 * temp method
	 */
	public function setInheritedSettings()
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::NormalUser);
		
//		$oSettings =&\Aurora\System\Api::GetSettings();
//		$oMap = $this->getStaticMap();
		
//		if (isset($oMap['HelpdeskFacebookAllow'][2]) && !$oMap['HelpdeskFacebookAllow'][2])
//		{
//			$this->HelpdeskFacebookAllow = !!$oSettings->GetValue('Helpdesk/FacebookAllow');
//		}
//		
//		if (isset($oMap['HelpdeskFacebookId'][2]) && !$oMap['HelpdeskFacebookId'][2])
//		{
//			$this->HelpdeskFacebookId = (string) $oSettings->GetValue('Helpdesk/FacebookId');
//		}
//		
//		if (isset($oMap['HelpdeskFacebookSecret'][2]) && !$oMap['HelpdeskFacebookSecret'][2])
//		{
//			$this->HelpdeskFacebookSecret = (string) $oSettings->GetValue('Helpdesk/FacebookSecret');
//		}
//		
//		if (isset($oMap['HelpdeskGoogleAllow'][2]) && !$oMap['HelpdeskGoogleAllow'][2])
//		{
//			$this->HelpdeskGoogleAllow = !!$oSettings->GetValue('Helpdesk/GoogleAllow');
//		}
//		
//		if (isset($oMap['HelpdeskGoogleId'][2]) && !$oMap['HelpdeskGoogleId'][2])
//		{
//			$this->HelpdeskGoogleId = (string) $oSettings->GetValue('Helpdesk/GoogleId');
//		}
//		
//		if (isset($oMap['HelpdeskGoogleSecret'][2]) && !$oMap['HelpdeskGoogleSecret'][2])
//		{
//			$this->HelpdeskGoogleSecret = (string) $oSettings->GetValue('Helpdesk/GoogleSecret');
//		}
//		
//		if (isset($oMap['HelpdeskTwitterAllow'][2]) && !$oMap['HelpdeskTwitterAllow'][2])
//		{
//			$this->HelpdeskTwitterAllow = !!$oSettings->GetValue('Helpdesk/TwitterAllow');
//		}
//		
//		if (isset($oMap['HelpdeskTwitterId'][2]) && !$oMap['HelpdeskTwitterId'][2])
//		{
//			$this->HelpdeskTwitterId = (string) $oSettings->GetValue('Helpdesk/TwitterId');
//		}
//		
//		if (isset($oMap['HelpdeskTwitterSecret'][2]) && !$oMap['HelpdeskTwitterSecret'][2])
//		{
//			$this->HelpdeskTwitterSecret = (string) $oSettings->GetValue('Helpdesk/TwitterSecret');
//		}
	}
	
	protected function GetCurrentAccount()
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::Anonymous);
		
		$iUserId = \Aurora\System\Api::getAuthenticatedUserId();
	
		if (!$this->oCurrentAccount && $iUserId)
		{
			$this->oCurrentAccount = $this->oAccountsManager->getAccountByUserId($iUserId);
		}
		
		return $this->oCurrentAccount;
	}
	
	/**
	 * @param \Aurora\Modules\StandardAuth\Classes\Account $oAccount
	 * 
	 * @param string $Login
	 * @param string $Password
	 * @param boolean $SignMe
	 * @return boolean
	 * @throws \Aurora\System\Exceptions\ApiException
	 */
	public function Login($Login = '', $Password = '', $SignMe = false)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::Anonymous);
		
		@\setcookie('aft-cache-ctrl', '', \strtotime('-1 hour'), \Aurora\System\Api::getCookiePath());
		$sTenantName = \Aurora\System\Api::getTenantName();

		if (0 === \strlen($Login) || 0 === \strlen($Password))
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::InvalidInputParameter);
		}

		$mIdTenant = $this->oCoreDecorator->GetTenantIdByName($sTenantName);

		if (!\is_int($mIdTenant))
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::InvalidInputParameter);
		}

		try
		{
//				$oApiIntegrator = new \Aurora\Modules\Core\Managers\Integrator();
//				$oUser = $oApiIntegrator->loginToHelpdeskAccount($mIdTenant, $sEmail, $sPassword);
//				if ($oUser && !$oUser->Blocked)
//				{
//					$oApiIntegrator->setHelpdeskUserAsLoggedIn($oUser, $bSignMe);
//					return true;
//				}

			$mResult = null;

			$aArgs = array(
				'Login' => $Login,
				'Password' => $Password,
				'SignMe' => $SignMe
			);
			$this->broadcastEvent(
				'Login', 
				$aArgs,
				$mResult
			);

			if (\is_array($mResult))
			{
				$aAccountHashTable = $mResult;

	//			$iTime = $bSignMe ? time() + 60 * 60 * 24 * 30 : 0;
				$sAccountHashTable = \Aurora\System\Api::EncodeKeyValues($aAccountHashTable);

				$sAuthToken = \md5(\microtime(true).\rand(10000, 99999));

				$sAuthToken = \Aurora\System\Api::Cacher()->Set('AUTHTOKEN:'.$sAuthToken, $sAccountHashTable) ? $sAuthToken : '';

				return array(
					'AuthToken' => $sAuthToken
				);
			}
		}
		catch (\Exception $oException)
		{
			$iErrorCode = \Aurora\System\Notifications::UnknownError;
			if ($oException instanceof \Aurora\System\Exceptions\ManagerException)
			{
				switch ($oException->getCode())
				{
					case \Aurora\System\Exceptions\Errs::HelpdeskManager_AccountSystemAuthentication:
						$iErrorCode = \Aurora\System\Notifications::HelpdeskSystemUserExists;
						break;
					case \Aurora\System\Exceptions\Errs::HelpdeskManager_AccountAuthentication:
						$iErrorCode = \Aurora\System\Notifications::AuthError;
						break;
					case \Aurora\System\Exceptions\Errs::HelpdeskManager_UnactivatedUser:
						$iErrorCode = \Aurora\System\Notifications::HelpdeskUnactivatedUser;
						break;
					case \Aurora\System\Exceptions\Errs::Db_ExceptionError:
						$iErrorCode = \Aurora\System\Notifications::DataBaseError;
						break;
				}
			}

			throw new \Aurora\System\Exceptions\ApiException($iErrorCode);
		}

		return false;
	}

	public function Logout()
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::Customer);
		
		@\setcookie('aft-cache-ctrl', '', \strtotime('-1 hour'), \Aurora\System\Api::getCookiePath());
		$oApiIntegrator = \Aurora\Modules\Core\Managers\Integrator::getInstance();
		$oApiIntegrator->logoutHelpdeskUser();

		return true;
	}	
	
	/**
	 * 
	 * @param string $Email
	 * @param string $Password
	 * @return boolean
	 * @throws \Aurora\System\Exceptions\ApiException
	 */
	public function Register($Email, $Password)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::Anonymous);
		
		$sTenantName = \Aurora\System\Api::getTenantName();

		if (0 === \strlen($Email) || 0 === \strlen($Password))
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::InvalidInputParameter);
		}

		$mIdTenant = $this->oCoreDecorator->GetTenantIdByName($sTenantName);
		if (!\is_int($mIdTenant))
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::InvalidInputParameter);
		}

		$bResult = false;
		try
		{
			$oEventResult = null;
			$iUserId = \Aurora\System\Api::getAuthenticatedUserId();

			$aArgs = array(
				'TenantId' => $mIdTenant,
				'UserId' => $iUserId,
				'login' => $Email,
				'password' => $Password
			);
			$this->broadcastEvent(
				'CreateAccount::before', 
				$aArgs,
				$oEventResult
			);

			if ($oEventResult instanceOf \Aurora\Modules\Core\Classes\User)
			{
				//Create account for auth
				$oAuthAccount = \Aurora\Modules\StandardAuth\Classes\Account::createInstance('HelpDesk');
				$oAuthAccount->IdUser = $oEventResult->EntityId;
				$oAuthAccount->Login = $Email;
				$oAuthAccount->Password = $Password;

				if ($this->oAuthDecorator->SaveAccount($oAuthAccount))
				{
					//Create propertybag account
					$oAccount = Classes\Account::createInstance();
					$oAccount->IdUser = $oEventResult->EntityId;
					$oAccount->NotificationEmail = $Email;

					$bResult = $this->oAccountsManager->createAccount($oAccount);
				}
				else
				{
					$this->oAuthDecorator->DeleteAccount($oAuthAccount);
				}

				return $bResult;
			}
			else
			{
				throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::NonUserPassed);
			}
		}
		catch (\Exception $oException)
		{
			$iErrorCode = \Aurora\System\Notifications::UnknownError;
			if ($oException instanceof \Aurora\System\Exceptions\ManagerException)
			{
				switch ($oException->getCode())
				{
					case \Aurora\System\Exceptions\Errs::HelpdeskManager_UserAlreadyExists:
						$iErrorCode = \Aurora\System\Notifications::HelpdeskUserAlreadyExists;
						break;
					case \Aurora\System\Exceptions\Errs::HelpdeskManager_UserCreateFailed:
						$iErrorCode = \Aurora\System\Notifications::CanNotCreateHelpdeskUser;
						break;
					case \Aurora\System\Exceptions\Errs::Db_ExceptionError:
						$iErrorCode = \Aurora\System\Notifications::DataBaseError;
						break;
				}
			}

			throw new \Aurora\System\Exceptions\ApiException($iErrorCode);
		}

		return $bResult;
	}	
	
	/**
	 * @return array
	 */
	protected function isAgent()
	{
		$oUser = \Aurora\System\Api::getAuthenticatedUser();
		return $oUser ? $this->oMainManager->isAgent($oUser) : false;
	}	
	
	/**
	 * 
	 * @param string $Email
	 * @return boolean
	 * @throws \Aurora\System\Exceptions\ApiException
	 */
	public function Forgot($Email = '')
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::Anonymous);
		
		$sTenantName = \Aurora\System\Api::getTenantName();
		$Email = \trim($Email);

		if (0 === \strlen($Email))
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::InvalidInputParameter);
		}

		$mIdTenant = $this->oCoreDecorator->GetTenantIdByName($sTenantName);

		if (!\is_int($mIdTenant))
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::InvalidInputParameter);
		}

		$oAccount = $this->oAccountsManager->getAccountByEmail($mIdTenant, $Email);

		if (!($oAccount instanceof Classes\Account))
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::HelpdeskUnknownUser);
		}

//			return $this->oMainManager->forgotUser($oAccount);

		$oFromAccount = null;

		$aData = $this->oMainManager->getHelpdeskMainSettings($mIdTenant);

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
			$oApiMail = $this->oMainManager->_getApiMail();
			if ($oApiMail)
			{
				$Email = $oAccount->getNotificationEmail();
				if (!empty($Email))
				{
					$oFromEmail = \MailSo\Mime\Email::NewInstance($oFromAccount->Email, $sSiteName);
					$oToEmail = \MailSo\Mime\Email::NewInstance($Email, $oAccount->Name);

					$oUserMessage = $this->oMainManager->_buildUserMailMail(AU_APP_ROOT_PATH.'templates/helpdesk/user.forgot.html',
						$oFromEmail->ToString(), $oToEmail->ToString(),
						'Forgot', '', '', $oAccount, $sSiteName);

					$oApiMail->sendMessage($oFromAccount, $oUserMessage);
				}
			}
		}
		
		return false;
	}	
	
	/**
	 * 
	 * @param string $ActivateHash
	 * @param string $NewPassword
	 * @return boolean
	 * @throws \Aurora\System\Exceptions\ApiException
	 */
	public function ForgotChangePassword($ActivateHash, $NewPassword)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::Anonymous);
		
		$sTenantName = \Aurora\System\Api::getTenantName();

		if (0 === \strlen($NewPassword) || 0 === \strlen($ActivateHash))
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::InvalidInputParameter);
		}

		$mIdTenant = $this->oCoreDecorator->GetTenantIdByName($sTenantName);
		if (!\is_int($mIdTenant))
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::InvalidInputParameter);
		}

		$oUser = $this->oMainManager->getUserByActivateHash($mIdTenant, $ActivateHash);
		if (!($oUser instanceof \Aurora\Modules\Core\Classes\User))
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::HelpdeskUnknownUser);
		}

		$oUser->Activated = true;
		$oUser->setPassword($NewPassword);
		$oUser->regenerateActivateHash();

		return $this->oMainManager->updateUser($oUser);
	}	
	
	/**
	 * 
	 * @param int $ThreadId
	 * @param boolean $IsInternal
	 * @param string $Subject
	 * @param string $Text
	 * @param string $Cc
	 * @param string $Bcc
	 * @param array $Attachments
	 * @return boolean
	 * @throws \Aurora\System\Exceptions\ApiException
	 */
	public function CreatePost($ThreadId = 0, $IsInternal = false, $Subject = '', $Text = '', $Cc = '', $Bcc = '', $Attachments = null)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::Customer);
		
		$oUser = \Aurora\System\Api::getAuthenticatedUser();
		
		if (0 === \strlen($Text) || (0 === $ThreadId && 0 === \strlen($Subject)))
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::InvalidInputParameter);
		}

		$mResult = false;
		$bIsNew = false;

		$oThread = null;
		if (0 === $ThreadId)
		{
			$bIsNew = true;
			
			$oThread = Classes\Thread::createInstance(
				Classes\Thread::class, self::GetName()
			);
			$oThread->IdTenant = $oUser->IdTenant;
			$oThread->IdOwner = $oUser->EntityId;
			$oThread->Type = Enums\ThreadType::Pending;
			$oThread->Subject = $Subject;
			
			if (!$this->oMainManager->createThread($oThread))
			{
				$oThread = null;
			}
		}
		else
		{
			$oThread = $this->oMainManager->getThread($ThreadId);
		}

		if ($oThread && 0 < $oThread->EntityId)
		{
			$oPost = Classes\Post::createInstance(
				Classes\Post::class,
				self::GetName()
			);
			$oPost->IdTenant = $oUser->IdTenant;
			$oPost->IdOwner = $oUser->EntityId;
			$oPost->IdThread = $oThread->EntityId;
			$oPost->Type = $IsInternal ? Enums\PostType::Internal : Enums\PostType::Normal;
			$oPost->SystemType = Enums\PostSystemType::None;
			$oPost->Text = $Text;

//			$aResultAttachment = array();
//			if (\is_array($Attachments) && 0 < \count($Attachments))
//			{
//				foreach ($Attachments as $sTempName => $sHash)
//				{
//					$aDecodeData = \Aurora\System\Api::DecodeKeyValues($sHash);
//					if (!isset($aDecodeData['HelpdeskUserID']))
//					{
//						continue;
//					}
//
//					$rData = $this->ApiFileCache()->getFile($oUser, $sTempName);
//					if ($rData)
//					{
//						$iFileSize = $this->ApiFileCache()->fileSize($oUser, $sTempName);
//
//						$sThreadID = (string) $oThread->IdThread;
//						$sThreadID = \str_pad($sThreadID, 2, '0', STR_PAD_LEFT);
//						$sThreadIDSubFolder = \substr($sThreadID, 0, 2);
//
//						$sThreadFolderName = AU_API_HELPDESK_PUBLIC_NAME.'/'.$sThreadIDSubFolder.'/'.$sThreadID;
//
//						$this->oApiFilestorage->createFolder($oUser, \Aurora\System\Enums\FileStorageType::Corporate, '',
//							$sThreadFolderName);
//
//						$sUploadName = isset($aDecodeData['Name']) ? $aDecodeData['Name'] : $sTempName;
//
//						$this->oApiFilestorage->createFile($oUser,
//							\Aurora\System\Enums\FileStorageType::Corporate, $sThreadFolderName, $sUploadName, $rData, false);
//
//						$oAttachment = \CHelpdeskAttachment::createInstance('CHelpdeskAttachment', self::GetName());
//						$oAttachment->IdThread = $oThread->IdThread;
//						$oAttachment->IdPost = $oPost->IdPost;
//						$oAttachment->IdOwner = $oUser->EntityId;
//						$oAttachment->IdTenant = $oUser->IdTenant;
//
//						$oAttachment->FileName = $sUploadName;
//						$oAttachment->SizeInBytes = $iFileSize;
//						$oAttachment->encodeHash($oUser, $sThreadFolderName);
//						
//						$aResultAttachment[] = $oAttachment;
//					}
//				}
//
//				if (\is_array($aResultAttachment) && 0 < \count($aResultAttachment))
//				{
//					$oPost->Attachments = $aResultAttachment;
//				}
//			}

			$mResult = $this->oMainManager->createPost($oUser, $oThread, $oPost, $bIsNew, true, $Cc, $Bcc);

			if ($mResult)
			{
				$mResult = array(
					'ThreadId' => $oThread->EntityId,
					'ThreadIsNew' => $bIsNew
				);
			}
		}

		return $mResult;
	}	
	
	/**
	 * 
	 * @param int $PostId
	 * @param int $ThreadId
	 * @return boolean
	 * @throws \Aurora\System\Exceptions\ApiException
	 */
	public function DeletePost($PostId = 0, $ThreadId = 0)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::Customer);
		
		$oUser = \Aurora\System\Api::getAuthenticatedUser();

		if (!$oUser)
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::AccessDenied);
		}

		$ThreadId = (int) $ThreadId;
		$PostId = (int) $PostId;
		
		if (0 >= $ThreadId || 0 >= $PostId)
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::InvalidInputParameter);
		}

		$oThread = $this->oMainManager->getThread($ThreadId);
		if (!$oThread)
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::InvalidInputParameter);
		}

		if (!$this->oMainManager->verifyPostIdsBelongToUser($oUser, array($PostId)))
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::AccessDenied);
		}

		return $this->oMainManager->deletePosts($oUser, $oThread, array($PostId));
	}	
	
	/**
	 * 
	 * @param int $ThreadId
	 * @param string $ThreadHash
	 * @return \CThread
	 * @throws \Aurora\System\Exceptions\ApiException
	 */
	public function GetThreadByIdOrHash($ThreadId = 0, $ThreadHash = '')
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::Anonymous);
		
		$oThread = false;
		$oUser = \Aurora\System\Api::getAuthenticatedUser();

		if (empty($ThreadHash) && $ThreadId === 0)
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::InvalidInputParameter);
		}

		$iThreadId = $ThreadId ? $ThreadId : $this->oMainManager->getThreadIdByHash($oUser->IdTenant, $ThreadHash);
		if (!\is_int($iThreadId) || 1 > $iThreadId)
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::InvalidInputParameter);
		}

		$oThread = $this->oMainManager->getThread($iThreadId);
		if (!$oThread)
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::InvalidInputParameter);
		}

		$aUserInfo = $this->oMainManager->userInformation($oUser, array($oThread->IdOwner));
		if (\is_array($aUserInfo) && 0 < \count($aUserInfo))
		{
			if (isset($aUserInfo[$oThread->IdOwner]) && \is_array($aUserInfo[$oThread->IdOwner]))
			{
				$sEmail = isset($aUserInfo[$oThread->IdOwner][0]) ? $aUserInfo[$oThread->IdOwner][0] : '';
				$sName = isset($aUserInfo[$oThread->IdOwner][1]) ? $aUserInfo[$oThread->IdOwner][1] : '';

				if (empty($sEmail) && !empty($aUserInfo[$oThread->IdOwner][3]))
				{
					$sEmail = $aUserInfo[$oThread->IdOwner][3];
				}

				if (!$this->isAgent() && 0 < \strlen($sName))
				{
					$sEmail = '';
				}

				$oThread->Owner = array($sEmail, $sName);
			}
		}

		return $oThread;
	}	
	
	/**
	 * 
	 * @param int $ThreadId
	 * @param int $StartFromId
	 * @param int $Limit
	 * @return array
	 * @throws \Aurora\System\Exceptions\ApiException
	 */
	public function GetPosts($ThreadId = 0, $StartFromId = 0, $Limit = 10)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::Anonymous);
		
		$oUser = \Aurora\System\Api::getAuthenticatedUser();
		

		if (1 > $ThreadId || 0 > $StartFromId || 1 > $Limit)
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::InvalidInputParameter);
		}
		
		$oThread = $this->oMainManager->getThread($ThreadId);
		if (!$oThread)
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::InvalidInputParameter);
		}

		$aPostList = $this->oMainManager->getPosts($oThread, $StartFromId, $Limit);
		$bIsAgent = $this->isAgent();
		$iExtPostsCount = !$bIsAgent ? $this->oMainManager->getExtPostsCount($oUser, $oThread) : 0;

		foreach ($aPostList as &$oPost)
		{
			$oPost->IsThreadOwner = $oThread->IdOwner === $oPost->IdOwner;
		}

//		if ($oThread->HasAttachments)
//		{
//			$aAttachments = $this->oMainManager->getAttachments($oUser, $oThread);
//			if (\is_array($aAttachments))
//			{
//				foreach ($aPostList as &$oItem)
//				{
//					if (isset($aAttachments[$oItem->IdPost]) && \is_array($aAttachments[$oItem->IdPost]) &&
//						0 < \count($aAttachments[$oItem->IdPost]))
//					{
//						$oItem->Attachments = $aAttachments[$oItem->IdPost];
//
//						foreach ($oItem->Attachments as $oAttachment)
//						{
//							if ($oAttachment && '.asc' === \strtolower(\substr(\trim($oAttachment->FileName), -4)))
//							{
//								$oAttachment->populateContent($oUser, $this->oApiHelpdesk, $this->oApiFilestorage);
//							}
//						}
//					}
//				}
//			}
//		}

		return array(
			'ThreadId' => $oThread->EntityId,
			'StartFromId' => $StartFromId,
			'Limit' => $Limit,
			'ItemsCount' => $iExtPostsCount ? $iExtPostsCount : ($oThread->PostCount > \count($aPostList) ? $oThread->PostCount : \count($aPostList)),
			'List' => $aPostList
		);
	}
	
	/**
	 * @param int $ThreadId
	 * @return boolean
	 * @throws \Aurora\System\Exceptions\ApiException
	 */
	public function DeleteThread($ThreadId = 0)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::Customer);
		
		$oUser = \Aurora\System\Api::getAuthenticatedUser();

		if (!$oUser || !$this->isAgent() && !$this->oMainManager->doesUserOwnThread($oUser, $ThreadId))
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::AccessDenied);
		}

		$bResult = $this->oMainManager->archiveThread($ThreadId);

		return $bResult;
	}	
	
	/**
	 * 
	 * @param int $ThreadId
	 * @param int $Type
	 * @return boolean
	 * @throws \Aurora\System\Exceptions\ApiException
	 */
	public function ChangeThreadState($ThreadId = 0, $Type = Enums\ThreadType::None)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::Customer);
		
		$oUser = \Aurora\System\Api::getAuthenticatedUser();
		$aTypes = array(Enums\ThreadType::Pending, Enums\ThreadType::Waiting, Enums\ThreadType::Answered, Enums\ThreadType::Resolved, Enums\ThreadType::Deferred);
		if (1 > $ThreadId || !\in_array($Type, $aTypes))
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::InvalidInputParameter);
		}

		if (!$oUser || ($Type !== Enums\ThreadType::Resolved && !$this->isAgent()))
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::AccessDenied);
		}

		$bResult = false;
		$oThread = $this->oMainManager->getThread($ThreadId);
		if ($oThread)
		{
			$oThread->Type = $Type;
			$bResult = $this->oMainManager->updateThread($oThread);
		}
		
		return $bResult;
	}	
	
	/**
	 * 
	 * @param int $ThreadId
	 * @return boolean
	 * @throws \Aurora\System\Exceptions\ApiException
	 */
	public function PingThread($ThreadId = 0)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::Anonymous);
		
		$oUser = \Aurora\System\Api::getAuthenticatedUser();

		if (0 === $ThreadId)
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::InvalidInputParameter);
		}
		
		$oOnlineManager = new Managers\Online\Manager('', $this);

		$oOnlineManager->removeOldOnline();
		$oOnlineManager->removeViewerOnline($oUser, $ThreadId);
		
		$oOnline = Classes\Online::createInstance(
			Classes\Online::class, self::GetName()
		);
		$oOnline->IdThread = $ThreadId;
		$oOnline->IdViewer = $oUser->EntityId;
		$oOnline->Email = $oUser->PublicId;
		$oOnlineManager->setOnline($oOnline);

		$aResult = [];
		if ($this->isAgent())
		{
			$aOnlines = $oOnlineManager->getOnlineList($ThreadId);
			if (is_array($aOnlines))
			{
				foreach ($aOnlines as $oOnlineItem)
				{
					if ($oOnlineItem->IdViewer !== $oUser->EntityId)
					{
						$aResult[] = ['', $oOnlineItem->Email];
					}
				}
			}
		}
		return $aResult;
	}
	
	/**
	 * 
	 * @param int $ThreadId
	 * @return boolean
	 * @throws \Aurora\System\Exceptions\ApiException
	 */
	public function SetThreadSeen($ThreadId = 0)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::Customer);
		
		$iUserId = \Aurora\System\Api::getAuthenticatedUserId();

		if (0 === $ThreadId)
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::InvalidInputParameter);
		}

		$oThread = $this->oMainManager->getThread($ThreadId);
		if (!$oThread)
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::AccessDenied);
		}

		return $this->oMainManager->setThreadSeen($iUserId, $oThread);
	}	
	
	/**
	 * 
	 * @param int $Offset
	 * @param int $Limit
	 * @param int $Filter
	 * @param string $Search
	 * @return array
	 * @throws \Aurora\System\Exceptions\ApiException
	 */
	public function GetThreads($Offset = 0, $Limit = 10, $Filter = Enums\ThreadFilterType::All, $Search = '')
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::Anonymous);
		
		$oUser = \Aurora\System\Api::getAuthenticatedUser();
		
		if (0 > $Offset || 1 > $Limit)
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::InvalidInputParameter);
		}

		$aThreadsList = array();
		$iCount = $this->oMainManager->getThreadsCount($oUser, $Filter, $Search);
		if ($iCount)
		{
			$aThreadsList = $this->oMainManager->getThreads($oUser, $Offset, $Limit, $Filter, $Search);
		}

//		$aOwnerDataList = array();
//		if (\is_array($aThreadsList) && 0 < \count($aThreadsList))
//		{
//			foreach ($aThreadsList as &$oItem)
//			{
//				$oOwnerUser = $this->oCoreDecorator->GetUser($oItem->IdOwner);
//				$oOwnerAccount = $this->oAccountsManager->getAccountByUserId($oItem->IdOwner);
//				
//				if ($oOwnerUser)
//				{
//					$aOwnerDataList[$oOwnerUser->EntityId] = array(
//						'Email' => '', //actualy, it's a User Login stored in Auth account
//						'Name' => $oOwnerUser->Name,
//						'NotificationEmail' => isset($oOwnerAccount) ? $oOwnerAccount->NotificationEmail : ''
//					);
//				}
//			}
//		}
//
//		if (0 < count($aOwnerDataList))
//		{
////			$aOwnerList = array_values($aOwnerList);
//			
////			$aUserInfo = $this->oMainManager->userInformation($oUser, $aOwnerList);
////			id_helpdesk_user, email, name, is_agent, notification_email
//			
//			if (\is_array($aOwnerDataList) && 0 < \count($aOwnerDataList))
//			{
//				foreach ($aThreadsList as &$oItem)
//				{
//					if ($oItem && isset($aOwnerDataList[$oItem->IdOwner]))
//					{
//						$oOwnerData = $aOwnerDataList[$oItem->IdOwner];
//						$sEmail = isset($oOwnerData['Email']) ? $oOwnerData['Email'] : '';
//						$sName = isset($oOwnerData['Name']) ? $oOwnerData['Name'] : '';
//
//						if (empty($sEmail) && !empty($oOwnerData['NotificationEmail']))
//						{
//							$sEmail = $oOwnerData['NotificationEmail'];
//						}
//
//						if (!$this->isAgent() && 0 < \strlen($sName))
//						{
//							$sEmail = '';
//						}
//						
//						$oItem->Owner = array($sEmail, $sName);
//					}
//				}
//			}
//		}

		return array(
			'Search' => $Search,
			'Filter' => $Filter,
			'List' => $aThreadsList,
			'Offset' => $Offset,
			'Limit' => $Limit,
			'ItemsCount' =>  $iCount
		);
	}	
	
	public function GetThreadsPendingCount()
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::Anonymous);
		
		$oUser = \Aurora\System\Api::getAuthenticatedUser();

		return $this->oMainManager->getThreadsPendingCount($oUser->IdTenant);
	}	
	
	/**
	 * 
	 * @return boolean
	 * @throws \Aurora\System\Exceptions\ApiException
	 */
	public function UpdateUserPassword()
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::Customer);
		
		$oUser = \Aurora\System\Api::getAuthenticatedUser();

		$sCurrentPassword = (string) $this->getParamValue('CurrentPassword', '');
		$sNewPassword = (string) $this->getParamValue('NewPassword', '');

		$bResult = false;
		if ($oUser && $oUser->validatePassword($sCurrentPassword) && 0 < \strlen($sNewPassword))
		{
			$oUser->setPassword($sNewPassword);
			if (!$this->oMainManager->updateUser($oUser))
			{
				throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::CanNotChangePassword);
			}
		}

		return $bResult;
	}	
	
	/**
	 * 
	 * @param string $Name
	 * @param string $Language
	 * @param string $DateFormat
	 * @param int $TimeFormat
	 * @return boolean
	 */
	public function UpdateSettings($Name, $Language, $DateFormat, $TimeFormat)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::Customer);
		
		@\setcookie('aft-cache-ctrl', '', \strtotime('-1 hour'), \Aurora\System\Api::getCookiePath());
		$oUser = \Aurora\System\Api::getAuthenticatedUser();

		$oUser->Name = \trim($Name);
		$oUser->Language = \trim($Language);
		$oUser->DateFormat = $DateFormat;
		$oUser->TimeFormat = $TimeFormat;
		
		return $this->oMainManager->updateUser($oUser);
	}	
	
	/**
	 * 
	 * @param boolean $AllowEmailNotifications
	 * @param string $Signature
	 * @param boolean $UseSignature
	 * @return boolean
	 */
	public function UpdateUserSettings($AllowEmailNotifications, $Signature, $UseSignature)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::Customer);
		
		$oUser = \Aurora\System\Api::getAuthenticatedUser();
		if ($oUser)
		{
			if ($oUser->Role === \Aurora\System\Enums\UserRole::NormalUser)
			{
				$oUser->{self::GetName().'::AllowEmailNotifications'} = $AllowEmailNotifications;
				$oUser->{self::GetName().'::Signature'} = $Signature;
				$oUser->{self::GetName().'::UseSignature'} = $UseSignature;
				return $this->oCoreDecorator->UpdateUserObject($oUser);
			}
			if ($oUser->Role === \Aurora\System\Enums\UserRole::SuperAdmin)
			{
				return true;
			}
		}
		
		return false;
	}	
	
//	public function checkAuth($sLogin, $sPassword, &$mResult)
//	{
//		$oAccount = $this->oAccountsManager->getAccountByCredentials($sLogin, $sPassword);
//
//		if ($oAccount)
//		{
//			$mResult = array(
//				'token' => 'auth',
//				'sign-me' => true,
//				'id' => $oAccount->IdUser,
//				'email' => $oAccount->Login
//			);
//		}
//	}
}
