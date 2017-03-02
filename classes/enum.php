<?php
/**
 * @copyright Copyright (c) 2017, Afterlogic Corp.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 * 
 * @package Modules
 */

/**
 * @package Helpdesk
 * @subpackage Enum
 */
class EHelpdeskPostType extends AbstractEnumeration
{
	const Normal = 0;
	const Internal = 1;
	const System = 2;
}

/**
 * @package Helpdesk
 * @subpackage Enum
 */
class EHelpdeskPostSystemType extends AbstractEnumeration
{
	const None = 0;
}

/**
 * @package Helpdesk
 * @subpackage Enum
 */
class EHelpdeskThreadType extends AbstractEnumeration
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
class EHelpdeskThreadFilterType extends AbstractEnumeration
{
	const All = 0;
	const PendingOnly = 1;
	const ResolvedOnly = 2;
	const InWork = 3;
	const Open = 4;
	const Archived = 9;
}
