<?php
/**
 * @copyright Copyright (c) 2017, Afterlogic Corp.
 * @license AGPL-3.0 or AfterLogic Software License
 *
 * This code is licensed under AGPLv3 license or AfterLogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

/**
 * @package Helpdesk
 * @subpackage Enum
 */
class EHelpdeskPostType extends \Aurora\System\Enums\AbstractEnumeration
{
	const Normal = 0;
	const Internal = 1;
	const System = 2;
}

/**
 * @package Helpdesk
 * @subpackage Enum
 */
class EHelpdeskPostSystemType extends \Aurora\System\Enums\AbstractEnumeration
{
	const None = 0;
}

/**
 * @package Helpdesk
 * @subpackage Enum
 */
class EHelpdeskThreadType extends \Aurora\System\Enums\AbstractEnumeration
{
	const None = 0;
	const Pending = 1;
	const Waiting = 2;
	const Answered = 3;
	const Resolved = 4;
	const Deferred = 5;
}

/**
 * @package Helpdesk
 * @subpackage Enum
 */
class EHelpdeskThreadFilterType extends \Aurora\System\Enums\AbstractEnumeration
{
	const All = 0;
	const PendingOnly = 1;
	const ResolvedOnly = 2;
	const InWork = 3;
	const Open = 4;
	const Archived = 9;
}

/**
 * @package Api
 * @subpackage Enum
 */
class EHelpdeskFetcherType extends \Aurora\System\Enums\AbstractEnumeration
{
	const NONE = 0;
	const REPLY = 1;
	const ALL = 2;
}

