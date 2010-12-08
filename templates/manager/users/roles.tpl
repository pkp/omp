{**
 * roles.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Page for managing role to user group mappings.
 *}
{assign var="pageTitle" value="manager.users.roles"}
{include file="manager/users/usersHeader.tpl"}

<script type="text/javascript">
	<!--
	// Role key to role name string key mapping
	var roleMap = new Array();
	{foreach from=$roleOptions key=roleKey item=roleStringKey}
		roleMap[{$roleKey}] = '{$roleStringKey}';
	{/foreach}

	$(document).ready(function() {ldelim}
		// Role select drop-down list 
		$('#selectRole').change(function() {ldelim}
			var roleId = $('#selectRole').val();
			// Display user group listbuilder for selected role
			if (roleId) {ldelim}
				var roleTitle = $('#selectRole :selected').text();
				$.post(
					'{url router=$smarty.const.ROUTE_COMPONENT component="listbuilder.settings.UserGroupListbuilderHandler" op="fetch"}',
					{ldelim}
						'roleId': roleId,
						'title': roleMap[roleId]
					{rdelim},
					function(jsonData) {ldelim}
						if (jsonData !== null && jsonData.status === true) {ldelim}
							$('#userGroupsContainer').empty();
							$('#userGroupsContainer').append(jsonData.content);
						{rdelim}
					{rdelim},
					'json'
				);
			{rdelim}
		{rdelim});
	{rdelim});
	// -->
</script>

<div class="clear">&nbsp;</div>

<form id="roleSelectForm" name="roleSelectForm" method="post" action="#">

<label for="selectRole">{translate key="manager.users.selectRole"}: </label>
<select name="selectRole" id="selectRole" size="1" class="selectMenu">
	{html_options_translate options=$roleOptions selected=$smarty.const.ROLE_ID_PRESS_MANAGER}
</select>

</form>

<div id="userGroupsContainer">
{url|assign:siteAdminRolesUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.settings.UserGroupListbuilderHandler" op="fetch" roleId=$smarty.const.ROLE_ID_PRESS_MANAGER title='user.role.siteAdmin'}
{load_url_in_div id="userGroupsContainer" url=$siteAdminRolesUrl}
</div>

{include file="common/footer.tpl"}
