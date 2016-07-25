{**
 * plugins/generic/usageStats/templates/privacyInformation.tpl
 *
 * Copyright (c) 2013-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display usage stats privacy information and an opt-out option.
 *
 *}
{include file="frontend/components/header.tpl"}

{translate key="plugins.generic.usageStats.optout.description" privacyStatementUrl=$privacyStatementUrl}
<form action="{url}" method="POST">
	{if $hasOptedOut}
		{translate key="plugins.generic.usageStats.optout.done"}
		<input type="submit" name="opt-in" class="button defaultButton" value="{translate key="plugins.generic.usageStats.optin"}"/>
	{else}
		{translate key="plugins.generic.usageStats.optout.cookie" privacyStatementUrl=$privacyStatementUrl}
		<input type="submit" name="opt-out" class="button defaultButton" value="{translate key="plugins.generic.usageStats.optout"}"/>
	{/if}
</form>

{include file="frontend/components/footer.tpl"}
