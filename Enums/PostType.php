<?php
/**
 * This code is licensed under Afterlogic Software License.
 * For full statements of the license see LICENSE file.
 */

namespace Aurora\Modules\HelpDesk\Enums;

/**
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2019, Afterlogic Corp.
 */
class PostType extends \Aurora\System\Enums\AbstractEnumeration
{
	const Normal = 0;
	const Internal = 1;
	const System = 2;
}