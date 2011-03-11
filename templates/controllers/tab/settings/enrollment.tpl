{**
 * enrollment.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * User role enrollment.
 *
 *}

{url|assign:usersUrl router=$smarty.const.ROUTE_COMPONENT component="grid.users.user.UserEnrollmentGridHandler" op="fetchGrid"}
{assign var=gridContainerId value="userEnrollmentGridContainer"}
{load_url_in_div id=$gridContainerId url=$usersUrl}