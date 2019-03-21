<?php
/**
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 */

namespace Aurora\Modules\HelpDesk\Enums;

/**
 * @license https://afterlogic.com/products/common-licensing AfterLogic Software License
 * @copyright Copyright (c) 2019, Afterlogic Corp.
 */
class ThreadType extends \Aurora\System\Enums\AbstractEnumeration
{
	const None = 0;
	const Pending = 1;
	const Waiting = 2;
	const Answered = 3;
	const Resolved = 4;
	const Deferred = 5;
}