<?php
/**
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 */

namespace Aurora\Modules\HelpDesk\Enums;

/**
 * @license https://afterlogic.com/products/common-licensing AfterLogic Software License
 * @copyright Copyright (c) 2018, Afterlogic Corp.
 */
class FetcherType extends \Aurora\System\Enums\AbstractEnumeration
{
	const NONE = 0;
	const REPLY = 1;
	const ALL = 2;
}