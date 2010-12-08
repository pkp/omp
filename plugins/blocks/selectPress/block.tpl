{**
 * block.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site sidebar menu -- user tools.
 *}

{if $isUserLoggedIn}
	<form action="#">
		<div class="block" id="sidebarPress">
			<span class="blockTitle">{translate key="press.press"}</span>
			<br />
			<select id="toolbox_press_presses" name="toolbox_press_presses" class="field select" onchange="window.location.href=this.form.toolbox_press_presses.options[this.form.toolbox_press_presses.selectedIndex].value">
				{foreach from=$presses item=press}
					<option value="{url press=$press->getPath()}">{$press->getLocalizedName()}</option>
				{/foreach}
			</select> <br />
			<label for="toolbox_press_presses">{translate key="plugins.block.selectPress.changeTo"}</label>
		</div>
	</form>
{/if}
