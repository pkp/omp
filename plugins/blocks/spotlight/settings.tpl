{**
 * plugins/blocks/spotlight/settings.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Spotlight block plugin settings form
 *}
<form class="pkp_form" id="spotlightPluginSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="plugin" category="blocks" plugin=$pluginName verb="settings" save="true"}">
	{fbvFormArea id="pluginSettingsInfo"}
		{fbvFormSection for="displayMode" title="plugins.block.spotlight.form.displayMode" required="true" list=true}
			{foreach from=$displayModes key=modeValue item=modeLocaleString}
				{if $displayMode == $modeValue}{assign var="checked" value=true}{else}{assign var="checked" value=false}{/if}
				{fbvElement type="radio" id="displayMode-$modeValue" name="displayMode" value=$modeValue label=$modeLocaleString translate=false checked=$checked required=true inline=true}
			{/foreach}
		{/fbvFormSection}
		{fbvFormButtons}
	{/fbvFormArea}
</form>