{**
 * block.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site sidebar menu -- user tools.
 *}

{if $currentPress && $isUserLoggedIn}
	<div class="block" id="sidebarSelectRole">
		<span class="blockTitle">{translate key="user.roles"}</span>
		<br />
		<form id="changeActingAsUserGroupForm" action="">
			<select id="changedActingAsUserGroupId" name="changedActingAsUserGroupId" class="field select">
				{iterate from="userGroups" item=group}
					<option value="{$group->getId()}">{$group->getLocalizedName()}</option>
				{/iterate}
			</select>
			<label for="toolbox_press_roles">{translate key="plugins.block.selectRole.changeTo"}</label>
			{literal}<script type='text/javascript'>
				$(function(){
					$('#changedActingAsUserGroupId').change(function() {
						$.post(
							'{/literal}{url router=$smarty.const.ROUTE_COMPONENT component="api.user.RoleApiHandler" op="changeActingAsUserGroup"}{literal}',
							$(this.form).serialize(),
							function(jsonData) {
								// Display error message (if any)
								if (jsonData.status == false) alert(jsonData.content);
							},
							"json"
						);
					});
				});
			</script>{/literal}
		</form>
	</div>
{/if}