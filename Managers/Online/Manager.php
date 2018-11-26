<?php
/**
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 */

namespace Aurora\Modules\HelpDesk\Managers\Online;

/**
 * @license https://afterlogic.com/products/common-licensing AfterLogic Software License
 * @copyright Copyright (c) 2018, Afterlogic Corp.
 */
class Manager extends \Aurora\System\Managers\AbstractManager
{
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

		if ($oModule instanceof \Aurora\System\Module\AbstractModule)
		{
			$this->oEavManager = \Aurora\System\Managers\Eav::getInstance();
		}
	}

	/**
	 * @param COnline $oOnline
	 * @return boolean
	 */
	public function setOnline($oOnline)
	{
		$bResult = false;
		try
		{
			$bResult = $this->oEavManager->saveEntity($oOnline);
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$this->setLastException($oException);
		}
		return $bResult;
	}

	/**
	 * @param int $iThreadId
	 * @return array|bool
	 */
	public function getOnlineList($iThreadId)
	{
		$mResult = false;
		
		try
		{
			$iOffset = 0;
			$iLimit = 0;
			$aFilters = array(
				'IdThread' => array($iThreadId, '=')
			);
			$mResult = $this->oEavManager->getEntities('COnline', array(), $iOffset, $iLimit, $aFilters);
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$this->setLastException($oException);
		}

		return $mResult;
	}
	
	/**
	 * @param \Aurora\Modules\Core\Classes\User $oUser
	 * @param int $iThreadId
	 * @return boolean
	 */
	public function removeViewerOnline(\Aurora\Modules\Core\Classes\User $oUser, $iThreadId)
	{
		$aFilters = array(
			'IdViewer' => array($oUser->EntityId, '=')
		);
		return $this->_removeOnline($aFilters);
	}

	/**
	 * @return boolean
	 */
	public function removeOldOnline()
	{
		$oDate = new \DateTime();
		$oInterval = new \DateInterval('PT15M');
		$oInterval->invert = 1; //Make it negative.
		$oDate->add($oInterval);
		
		$aFilters = array('PingTime' => array($oDate->format('Y-m-d H:i:s'), '<'));
		
		return $this->_removeOnline($aFilters);
	}
	
	/**
	 * @param array $aFilters
	 * @return boolean
	 */
	protected function _removeOnline($aFilters)
	{
		$iOffset = 0;
		$iLimit = 0;
		$aOnline = $this->oEavManager->getEntities('COnline', array(), $iOffset, $iLimit, $aFilters);
		
		if (is_array($aOnline))
		{
			$aUUIDs = array();
			foreach ($aOnline as $oOnline)
			{
				$aUUIDs[] = $oOnline->UUID;
			}
			return $this->oEavManager->deleteEntities($aUUIDs);
		}
		
		return true;
	}
}
