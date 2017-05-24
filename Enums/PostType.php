<?php
/**
 * @copyright Copyright (c) 2017, Afterlogic Corp.
 * @license AGPL-3.0 or AfterLogic Software License
 *
 * This code is licensed under AGPLv3 license or AfterLogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\HelpDesk\Enums;

class PostType extends \Aurora\System\Enums\AbstractEnumeration
{
	const Normal = 0;
	const Internal = 1;
	const System = 2;
}