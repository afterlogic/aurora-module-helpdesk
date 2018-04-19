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
class ThreadFilterType extends \Aurora\System\Enums\AbstractEnumeration
{
	const All = 0;
	const PendingOnly = 1;
	const ResolvedOnly = 2;
	const InWork = 3;
	const Open = 4;
	const Archived = 9;
}