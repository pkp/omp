{**
 * block.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site sidebar menu -- user tools.
 *
 * $Id$
 *}

{if $currentPress && $isUserLoggedIn}
	<div class="block" id="sidebarUser">
		<span class="blockTitle">{translate key="user.roles"}</span>
		<br />
		<form>
			<select id="toolbox_press_roles" name="toolbox_press_roles" class="field select" onchange="window.location.href=this.form.toolbox_press_roles.options[this.form.toolbox_press_roles.selectedIndex].value">
				{iterate from=$userGroups item=group}
					<option value="{url page=$group->getPath()}">{$group->getLocalizedName()}</option>
				{/iterate}
			</select>
		</form>
		<label for="toolbox_press_roles">{translate key="plugins.block.selectRole.changeTo"}</label>
	</div>
{/if}