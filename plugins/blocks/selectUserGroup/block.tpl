{**
 * block.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site sidebar menu -- user tools -- select user group block.
 *}

{if $isUserLoggedIn}
	<div class="block" id="sidebarSelectUserGroup">
		<script type='text/javascript'>
			$(function(){ldelim}
				// Create a map of user group ids to role ids.
				var userGroupRoleMap = {ldelim}
					{foreach from=$userGroups item=group}
						{$group->getId()}: {$group->getRoleId()},
					{/foreach}
				{rdelim};

				// jQuerify the user group drop down.
				var $userGroupDropDown = $('#changedActingAsUserGroupId');
				
				// Save the initial user group.
				$userGroupDropDown.data('orig-data', $userGroupDropDown.val());
				
				// Bind to the change event.
				$userGroupDropDown.change(function() {ldelim}
					// Retrieve the previous and new user groups.
					var previousUserGroupId = $userGroupDropDown.data('orig-data');
					var newUserGroupId = $userGroupDropDown.val();

					// Did the user group really change?
					if (previousUserGroupId !== newUserGroupId) {ldelim}
						// Change the selected user group on the server.
						$.post(
							'{url router=$smarty.const.ROUTE_COMPONENT component="api.user.UserApiHandler" op="changeActingAsUserGroup"}',
							$(this.form).serialize(),
							function(jsonData) {ldelim}
								// Display error message (if any)
								if (jsonData.status == false) {ldelim}
									alert(jsonData.content);
									return;
								{rdelim}

								// Retrieve the previous and new roles.
								var previousRoleId = userGroupRoleMap[previousUserGroupId];
								var newRoleId = userGroupRoleMap[newUserGroupId];

								// Issue a custom event that client components can subscribe to
								// to refresh any components that are registered to refresh on
								// user group change.
								$('body').triggerHandler('user-group-change', [newRoleId, previousRoleId, newUserGroupId, previousUserGroupId]);
								
								// Save the new user group.
								$userGroupDropDown.data('orig-data', newUserGroupId);
							{rdelim},
							"json"
						);
					{rdelim}
				{rdelim});
			{rdelim});
		</script>
		
		<span class="blockTitle">{translate key="user.roles"}</span>
		<br />
		<form id="changeActingAsUserGroupForm" action="">
			<select id="changedActingAsUserGroupId" name="changedActingAsUserGroupId" class="field select">
				{foreach from=$userGroups item=group}
					<option value="{$group->getId()}" {if $group->getId() == $currentActingAsUserGroupId}selected="selected"{/if}>{$group->getLocalizedName()}</option>
				{/foreach}
			</select>
			<label for="toolbox_press_roles">{translate key="plugins.block.selectUserGroup.changeTo"}</label>
		</form>
	</div>
{/if}