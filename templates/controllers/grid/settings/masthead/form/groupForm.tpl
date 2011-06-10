{**
 * templates/controllers/grid/settings/masthead/form/groupForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Group form under press management.
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#groupForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form pkp_controllers_form" id="groupForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.masthead.MastheadGridHandler" op="updateGroup"}">

{include file="common/formErrors.tpl"}

{fbvFormArea id="mastheadInfo"}
{fbvFormSection title="manager.groups.title" required="true" for="title"}
	{fbvElement type="text" multilingual="true" id="title" value=$title name="title" maxlength="80"}
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
	<div id="membershipContainer">
		{url|assign:mastheadMembersUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.settings.MastheadMembershipListbuilderHandler" op="fetch" groupId=$group->getId()}
		{load_url_in_div id="membershipContainer" url=$mastheadMembersUrl}
	</div>
{/if}
{include file="form/formButtons.tpl" submitText="common.save"}
</form>

