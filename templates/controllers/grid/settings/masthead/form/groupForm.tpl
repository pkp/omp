{**
 * groupForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Group form under press management.
 *}

<form id="groupForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.masthead.MastheadGridHandler" op="updateGroup"}">

{include file="common/formErrors.tpl"}
<table class="data" width="100%">
{if count($formLocales) > 1}
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="formLocale" key="form.formLanguage"}</td>
		<td width="80%" class="value">
			{if $group}{url|assign:"groupFormUrl" op="editGroup" path=$group->getId()}
			{else}{url|assign:"groupFormUrl" op="createGroup"}
			{/if}
			{form_language_chooser form="groupForm" url=$groupFormUrl}
			<span class="instruct">{translate key="form.formLanguage.description"}</span>
		</td>
	</tr>
{/if}
</table>

{fbvFormArea id="mastheadInfo"}
{fbvFormSection title="manager.groups.title" required="true" for="title"}
	{fbvElement type="text" id="title" value=$title.$formLocale maxlength="80" required="true"}
{/fbvFormSection}
{fbvFormSection title="common.type" for="context"}
	{foreach from=$groupContextOptions item=groupContextOptionKey key=groupContextOptionValue}
		{if $context == $groupContextOptionValue}
			{assign var="checked" value=true}
		{else}
			{assign var="checked" value=false}
		{/if}
		{fbvElement type="radio" name="context" id="context"|concat:$groupContextOptionValue value=$groupContextOptionValue checked=$checked label=$groupContextOptionKey}
	{/foreach}
{/fbvFormSection}
{/fbvFormArea}

<br />
{if $group}
	<input type="hidden" name="groupId" value="{$group->getId()}"/>
	{url|assign:mastheadMembersUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.settings.MastheadMembershipListbuilderHandler" op="fetch" groupId=$group->getId()}
	{* Need a random div ID to load listbuilders in modals *}
	{load_url_in_div id= url=$mastheadMembersUrl}
{/if}

</form>

