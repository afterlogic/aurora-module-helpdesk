<?php
/**
 * @copyright Copyright (c) 2017, Afterlogic Corp.
 * @license AfterLogic Software License
 *
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 */

namespace Aurora\Modules\HelpDesk\Enums;

class FetcherType extends \Aurora\System\Enums\AbstractEnumeration
{
	const NONE = 0;
	const REPLY = 1;
	const ALL = 2;
}