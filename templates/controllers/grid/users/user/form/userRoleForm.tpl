{**
 * userRoleForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form for managing roles for a newly created user.
 *}

<h3>{translate key="grid.user.step2"}</h3>

<form name="userRoleForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.users.user.UserGridHandler" op="updateUserRoles"}">

<input type="hidden" id="userId" name="userId" value="{$userId|escape}" />

<div id="userRolesContainer" class="full left">
{url|assign:userRolesUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.users.UserUserGroupListbuilderHandler" op="fetch" userId=$userId title="grid.user.addRoles" escape=false}
{load_url_in_div id="userRolesContainer" url=$userRolesUrl}
</div>

</form>
