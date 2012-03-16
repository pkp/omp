{**
 * templates/controllers/grid/content/navigation/form/footerCategoryForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * form to read/create/edit social media entries.
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#mediaForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="mediaForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.content.navigation.ManageSocialMediaGridHandler" op="updateMedia"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="spotlightFormNotification"}
	{fbvFormArea id="categoryInfo"}
		{if $socialMedia}
			<input type="hidden" name="socialMediaId" value="{$socialMedia->getId()|escape}" />
		{/if}

		{fbvFormSection for="platform" label="grid.content.navigation.socialMedia.platform" required="true"}
			{fbvElement type="text" multilingual="true" id="platform" value=$platform maxlength="255" size=$fbvStyles.size.MEDIUM inline="true"}
		{/fbvFormSection}

		{fbvFormSection label="grid.content.navigation.socialMedia.code" for="description" required="true"}
			{fbvElement type="textarea" name="code" id="code" value=$code height=$fbvStyles.height.SHORT}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormButtons id="mediaFormSubmit" submitText="common.save"}
</form>
