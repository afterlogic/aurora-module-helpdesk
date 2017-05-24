<?php
/**
 * @copyright Copyright (c) 2017, Afterlogic Corp.
 * @license AGPL-3.0 or AfterLogic Software License
 *
 * This code is licensed under AGPLv3 license or AfterLogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

use \Modules\HelpDesk\CAccount as CHelpDeskAccount;

namespace Aurora\Modules\HelpDesk\Managers\Accounts;

/**
 * CApiAccountsManager class summary
 * 
 * @package Accounts
 */
class Manager extends \Aurora\System\Managers\AbstractManager
{
	/**
	 * @var \Aurora\System\Managers\Eav\Manager
	 */
	public $oEavManager = null;
	
	public $oCoreDecorator = null;
	
	public $sAccountClassName = '';
	
	/**
	 * @param \Aurora\System\Managers\GlobalManager &$oManager
	 */
	public function __construct($sForcedStorage = '', \Aurora\System\Module\AbstractModule $oModule = null)
	{
		parent::__construct('main', $oModule);
		
		$this->oEavManager = \Aurora\System\Api::GetSystemManager('eav', 'db');
		
		$this->oCoreDecorator = \Aurora\System\Api::GetModuleDecorator('Core');
		
		$this->sAccountClassName = 'Modules\HelpDesk\CAccount';
	}

	/**
	 * 
	 * @param int $iAccountId
	 * @return boolean|CAccount
	 * @throws \Aurora\System\Exceptions\BaseException
	 */
	public function getAccountById($iAccountId)
	{
		$oAccount = null;
		try
		{
			if (is_numeric($iAccountId))
			{
				$iAccountId = (int) $iAccountId;
				if (null === $oAccount)
				{
					$oAccount = $this->oEavManager->getEntity($iAccountId);
				}
			}
			else
			{
				throw new \Aurora\System\Exceptions\BaseException(Errs::Validation_InvalidParameters);
			}
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$oAccount = false;
			$this->setLastException($oException);
		}
		return $oAccount;
	}
	
	/**
	 * Retrieves information on particular WebMail Pro user. 
	 * 
	 * @todo not used
	 * 
	 * @param int $iUserId User identifier.
	 * 
	 * @return \CUser | false
	 */
	public function getAccountByCredentials($sLogin, $sPassword)
	{
		$oAccount = null;
		try
		{
			$aResults = $this->oEavManager->getEntities(
				$this->sAccountClassName, 
				array(
					'IsDisabled', 'Login', 'Password', 'IdUser'
				),
				0,
				0,
				array(
					'Login' => $sLogin,
					'Password' => $sPassword,
					'IsDisabled' => false
				)
			);
			
			if (is_array($aResults) && count($aResults) === 1)
			{
				$oAccount = $aResults[0];
			}
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$oAccount = false;
			$this->setLastException($oException);
		}
		return $oAccount;
	}
	
	
	public function getAccountByEmail($sIdTenant, $sLogin)
	{
		$oAccount = null;
		try
		{
			$aResults = $this->oEavManager->getEntities(
				$this->sAccountClassName, 
				array(
					'IsDisabled', 'Login', 'IdUser'
				),
				0,
				0,
				array(
					'Login' => $sLogin,
					'IsDisabled' => false
				)
			);
			
			if (is_array($aResults))
			{
//				$aUsserIds = Underscode\Types\Arrays::pluck($aResults, 'IdUser');
				
				foreach ($aResults as $key => $oAccount) {
					$oUser = $this->oCoreDecorator->GetUser($oAccount->IdUser);
					
					if ($oUser && $oUser->IdTenant !== $sIdTenant)
					{
						unset($aResults[$key]);
					}
				}
			}
			
			if (count($aResults) === 1)
			{
				$oAccount = array_values($aResults)[0];
			}
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$oAccount = false;
			$this->setLastException($oException);
		}
		return $oAccount;
	}
	
	public function getAccountByUserId($sUserId)
	{
		$oAccount = null;
		try
		{
			$aResults = $this->oEavManager->getEntities(
				$this->sAccountClassName, 
				array(
					'IsDisabled', 'Login', 'IdUser'
				),
				0,
				0,
				array(
					'IdUser' => $sUserId,
					'IsDisabled' => false
				)
			);
			
			if (count($aResults) === 1)
			{
				$oAccount = array_values($aResults)[0];
			}
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$oAccount = false;
			$this->setLastException($oException);
		}
		return $oAccount;
	}

	/**
	 * Obtains list of information about accounts.
	 * @param int $iPage List page.
	 * @param int $iUsersPerPage Number of users on a single page.
	 * @param string $sOrderBy = 'email'. Field by which to sort.
	 * @param int $iOrderType = \ESortOrder::ASC. If **\ESortOrder::ASC** the sort order type is ascending.
	 * @param string $sSearchDesc = ''. If specified, the search goes on by substring in the name and email of default account.
	 * @return array | false
	 */
	public function getAccountList($iPage, $iUsersPerPage, $sOrderBy = 'Login', $iOrderType = \ESortOrder::ASC, $sSearchDesc = '')
	{
		$aResult = false;
		try
		{
			$aFilters =  array();
			
			if ($sSearchDesc !== '')
			{
				$aFilters['Login'] = '%'.$sSearchDesc.'%';
			}
				
			$aResults = $this->oEavManager->getEntities(
				'CAccount', 
				array(
					'IsDisabled', 'Login', 'Password', 'IdUser'
				),
				$iPage,
				$iUsersPerPage,
				$aFilters,
				$sOrderBy,
				$iOrderType
			);

			if (is_array($aResults))
			{
				foreach($aResults as $oItem)
				{
					$aResult[$oItem->EntityId] = array(
						$oItem->Login,
						$oItem->Password,
						$oItem->IdUser,
						$oItem->IsDisabled
					);
				}
			}
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$aResult = false;
			$this->setLastException($oException);
		}
		return $aResult;
	}

	/**
	 * @param CHelpDeskAccount $oAccount
	 *
	 * @return bool
	 */
	public function isExists(CHelpDeskAccount $oAccount)
	{
		$bResult = false;
		try
		{
			$this->sAccountClassName;
			$aResults = $this->oEavManager->getEntities(
				$this->sAccountClassName,
				array('IdUser'),
				0,
				0,
				array(
					'IdUser' => $oAccount->IdUser
				)
			);

			if ($aResults)
			{
				foreach($aResults as $oObject)
				{
					if ($oObject->EntityId !== $oAccount->EntityId)
					{
						$bResult = true;
						break;
					}
				}
			}
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$this->setLastException($oException);
		}
		return $bResult;
	}
	
	/**
	 * @param CAccount $oAccount
	 *
	 * @return bool
	 */
	public function createAccount (CHelpDeskAccount &$oAccount)
	{
		$bResult = false;
		try
		{
			if ($oAccount->validate())
			{
				if (!$this->isExists($oAccount))
				{
					if (!$this->oEavManager->saveEntity($oAccount))
					{
						throw new \Aurora\System\Exceptions\ManagerException(Errs::UsersManager_UserCreateFailed);
					}
				}
				else
				{
					throw new \Aurora\System\Exceptions\ManagerException(Errs::UsersManager_UserAlreadyExists);
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
	 * @param CAccount $oAccount
	 *
	 * @return bool
	 */
	public function updateAccount (CAccount &$oAccount)
	{
		$bResult = false;
		try
		{
			if ($oAccount->validate())
			{
//				if ($this->isExists($oAccount))
//				{
					if (!$this->oEavManager->saveEntity($oAccount))
					{
						throw new \Aurora\System\Exceptions\ManagerException(Errs::UsersManager_UserCreateFailed);
					}
//				}
//				else
//				{
//					throw new \Aurora\System\Exceptions\ManagerException(Errs::UsersManager_UserAlreadyExists);
//				}
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
	 * 
	 * @param CAccount $oAccount
	 * @return bool
	 */
	public function deleteAccount(CAccount $oAccount)
	{
		$bResult = false;
		try
		{
			$bResult = $this->oEavManager->deleteEntity($oAccount->EntityId);
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$this->setLastException($oException);
		}
		return $bResult;
	}
}
