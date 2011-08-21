{**
 * pressSettings.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Basic press settings under site administration.
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#pressSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="pressSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.admin.press.PressGridHandler" op="updatePress"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="pressSettingsNotification"}

	{if $pressId}
		{fbvElement id="pressId" type="hidden" name="pressId" value=$pressId}
	{else}
		<p>{translate key="admin.presses.createInstructions"}</p>
	{/if}

	{fbvFormArea id="pressSettings"}
		{fbvFormSection title="manager.setup.pressName" required=true for="name"}
			{fbvElement type="text" id="name" value=$name multilingual=true}
		{/fbvFormSection}
		{fbvFormSection title="admin.presses.pressDescription" required=true for="description"}
			{fbvElement type="textarea" id="description" value=$description multilingual=true rich=true}
		{/fbvFormSection}
		{fbvFormSection title="press.path" required=true for="path"}
			{fbvElement type="text" id="path" value=$path size=$smarty.const.SMALL maxlength="32"}
			{url|assign:"sampleUrl" router=$smarty.const.ROUTE_PAGE press="path"}
			{** FIXME: is this class instruct still the right one? **}
			<span class="instruct">{translate key="admin.presses.urlWillBe" sampleUrl=$sampleUrl}</span>
		{/fbvFormSection}
		{fbvFormSection title="admin.presses.enablePressInstructions" for="enabled" list=true}
			{if $enabled}{assign var="enabled" value="checked"}{/if}
			{fbvElement type="checkbox" id="enabled" checked=$enabled value="1"}
		{/fbvFormSection}

		<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
		{fbvFormButtons id="pressSettingsFormSubmit" submitText="common.save"}
	{/fbvFormArea}
</form>


