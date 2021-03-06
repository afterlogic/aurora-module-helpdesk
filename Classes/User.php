<?php
/**
 * This code is licensed under Afterlogic Software License.
 * For full statements of the license see LICENSE file.
 */

namespace Aurora\Modules\HelpDesk\Classes;

/**
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2019, Afterlogic Corp.
 *
 * @property int $IdHelpdeskUser
 * @property int $IdSystemUser
 * @property int $IdTenant
 * @property bool $Activated
 * @property string $ActivateHash
 * @property bool $Blocked
 * @property bool $IsAgent
 * @property string $Name
 * @property string $Email
 * @property string $NotificationEmail
 * @property string $Language
 * @property string $DateFormat
 * @property int $TimeFormat
 * @property string $PasswordHash
 * @property string $PasswordSalt
 * @property string $NotificationPassword
 * @property bool $MailNotifications
 * @property int $Created
 * @property string $Signature
 * @property bool $SignatureEnable
 *
 * @package Helpdesk
 * @subpackage Classes
 */
class User extends \Aurora\System\AbstractContainer
{
	public function __construct()
	{
		parent::__construct(get_class($this));

		$this->SetTrimer(array('Name', 'Email', 'PasswordHash'));

		$this->SetLower(array('Email'));

		$oSettings =&\Aurora\System\Api::GetSettings();

		$oModuleManager = \Aurora\System\Api::GetModuleManager();
		
		$this->SetDefaults(array(
			'IdHelpdeskUser'		=> 0,
			'IdSystemUser'			=> 0,
			'IdTenant'				=> 0,
			'Activated'				=> false,
			'Blocked'				=> false,
			'IsAgent'				=> false,
			//'IsSocial'			=> false,
			'Name'					=> '',
			'Email'					=> '',
			'NotificationEmail'		=> '',
			'SocialId'				=> '',
			'SocialType'			=> '',
			'ActivateHash'			=> md5(microtime(true).rand(1000, 9999)),
			'Language'				=> \Aurora\System\Api::GetLanguage(true),
			'DateFormat'			=> $oModuleManager->getModuleConfigValue('Core', 'DateFormat'),
			'TimeFormat'			=> $oModuleManager->getModuleConfigValue('Core', 'TimeFormat'),
			'NotificationPassword'	=> '',
			'PasswordHash'			=> '',
			'PasswordSalt'			=> md5(microtime(true).rand(10000, 99999)),
			'MailNotifications'		=> false,
			'Created'				=> time(),
            'Signature'		    	=> '',
            'SignatureEnable'	    => false
		));
	}

	public function regenerateActivateHash()
	{
		$this->ActivateHash = md5(microtime(true).rand(1000, 9999).$this->ActivateHash);
	}

	/**
	 * @param string $sPassword
	 * @param bool $bCreateFromFetcher = false
	 */
	public function setPassword($sPassword, $bCreateFromFetcher = false)
	{
		$this->PasswordHash = md5($sPassword.'/'.$this->PasswordSalt);
		if ($bCreateFromFetcher)
		{
			$this->NotificationPassword = $sPassword;
		}
	}

	/**
	 * @param string $sPassword
	 *
	 * @return bool
	 */
	public function validatePassword($sPassword)
	{
		return $this->PasswordHash === md5($sPassword.'/'.$this->PasswordSalt);
	}

	/**
	 * @throws \Aurora\System\Exceptions\ValidationException 1106 \Aurora\System\Exceptions\Errs::Validation_ObjectNotComplete
	 *
	 * @return bool
	 */
	public function validate()
	{
		if ($this->SocialId)
		{
			switch (true)
			{
				case (\Aurora\System\Utils\Validate::IsEmpty($this->NotificationEmail)) :
					throw new \Aurora\System\Exceptions\ValidationException(\Aurora\System\Exceptions\Errs::Validation_FieldIsEmpty, null, array(
						'{{ClassName}}' => 'CHelpdeskUser', '{{NotificationEmail}}' => 'NotificationEmail'));
			}
		}
		else
		{
			switch (true)
			{
				case (\Aurora\System\Utils\Validate::IsEmpty($this->Email)) :
					throw new \Aurora\System\Exceptions\ValidationException(\Aurora\System\Exceptions\Errs::Validation_FieldIsEmpty, null, array(
						'{{ClassName}}' => 'CHelpdeskUser', '{{ClassField}}' => 'Email'));

				case (\Aurora\System\Utils\Validate::IsEmpty($this->PasswordHash)) :
					throw new \Aurora\System\Exceptions\ValidationException(\Aurora\System\Exceptions\Errs::Validation_FieldIsEmpty, null, array(
						'{{ClassName}}' => 'CHelpdeskUser', '{{ClassField}}' => 'PasswordHash'));
			}
		}

		return true;
	}

	/**
	 * @return string
	 */
	public function helpdeskLink()
	{
		$sPath = rtrim(\MailSo\Base\Http::SingletonInstance()->GetFullUrl(), '\\/ ');
		if ('/crons' === substr($sPath, -6))
		{
			$sPath = substr($sPath, 0, -6);
		}
		$sPath .= '/?helpdesk';

		if (0 < $this->IdTenant)
		{
			$sHash = substr(md5($this->IdTenant.\Aurora\System\Api::$sSalt), 0, 8);
			$sPath .= '='.$sHash;
		}

		return $sPath;
	}

	/**
	 * @return string
	 */
	public function activationLink()
	{
		$sPath = rtrim(\MailSo\Base\Http::SingletonInstance()->GetFullUrl(), '\\/ ');
		if ('/crons' === substr($sPath, -6))
		{
			$sPath = substr($sPath, 0, -6);
		}
		$sPath .= '/?helpdesk';

		if (0 < $this->IdTenant)
		{
			$sHash = substr(md5($this->IdTenant.\Aurora\System\Api::$sSalt), 0, 8);
			$sPath .= '='.$sHash;
		}

		$sPath .= '&activate='.$this->ActivateHash;

		return $sPath;
	}

	/**
	 * @return string
	 */
	public function forgotLink()
	{
		$sPath = rtrim(\MailSo\Base\Http::SingletonInstance()->GetFullUrl(), '\\/ ');
		if ('/crons' === substr($sPath, -6))
		{
			$sPath = substr($sPath, 0, -6);
		}
		$sPath .= '/?helpdesk';

		if (0 < $this->IdTenant)
		{
			$sHash = substr(md5($this->IdTenant.\Aurora\System\Api::$sSalt), 0, 8);
			$sPath .= '='.$sHash;
		}

		$sPath .= '&forgot='.$this->ActivateHash;

		return $sPath;
	}

	/**
	 * @return string
	 */
	//TODO moved to HelpDesk Account
//	public function resultEmail()
//	{
//		$sEmail = $this->NotificationEmail;
//		if (empty($sEmail))
//		{
//			$sEmail = $this->Email;
//		}
//
//		return $sEmail;
//	}
	
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
			'IdHelpdeskUser'	    => array('int', 'id_helpdesk_user', false, false),
			'IdSystemUser'		    => array('int', 'id_system_user', true, false),
			'IdTenant'			    => array('int', 'id_tenant', true, false),
			'IsAgent'			    => array('bool', 'is_agent'),
			'Activated'			    => array('bool', 'activated'),
			'ActivateHash'		    => array('string', 'activate_hash'),
			'Blocked'			    => array('bool', 'blocked'),
			'Email'				    => array('string', 'email', true, false),
			'NotificationEmail'	    => array('string', 'notification_email'),
			'Name'				    => array('string', 'name'),
			'SocialId'			    => array('string', 'social_id'),
			'SocialType'		    => array('string', 'social_type'),
			'Language'			    => array('string(100)', 'language'),
			'DateFormat'		    => array('string(50)', 'date_format'),
			'TimeFormat'		    => array('int', 'time_format'),
			'NotificationPassword'	=> array('string'),
			'PasswordHash'		    => array('string', 'password_hash'),
			'PasswordSalt'		    => array('string', 'password_salt'),
			'MailNotifications'	    => array('bool', 'mail_notifications'),
			'Created'			    => array('datetime', 'created', true, false),
			'Signature'			    => array('string', 'signature'),
			'SignatureEnable'	    => array('bool', 'signature_enable')
		);
	}
}
