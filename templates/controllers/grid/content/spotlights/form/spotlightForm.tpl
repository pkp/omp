{**
 * templates/controllers/grid/content/spotlights/form/spotlightForm.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to read/create/edit spotlights.
 *}
{capture assign=addSpotlightItemUrl}{url op="itemAutocomplete" pressId=$pressId escape=false}{/capture}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#spotlightForm').pkpHandler('$.pkp.controllers.grid.content.spotlights.form.SpotlightFormHandler',
			{ldelim}
				autocompleteUrl: {$addSpotlightItemUrl|json_encode}
			{rdelim}
		);
	{rdelim});
</script>

<form class="pkp_form" id="spotlightForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.content.spotlights.ManageSpotlightsGridHandler" op="updateSpotlight"}">
	{csrf}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="spotlightFormNotification"}
	{fbvFormArea id="spotlightInfo"}
		{if $spotlight}
			<input type="hidden" name="spotlightId" value="{$spotlight->getId()|escape}" />
		{/if}

		{fbvFormSection for="title"}
			{fbvElement type="autocomplete" id="assocId" required="true" value=$assocTitle autocompleteValue=$assocId label="grid.content.spotlights.form.item" autocompleteUrl=$addSpotlightItemUrl size=$fbvStyles.size.MEDIUM inline="true" disableSync="true"}
			{fbvElement type="text" multilingual="true" id="title" required="true" label="grid.content.spotlights.form.title" value=$title maxlength="255" size=$fbvStyles.size.MEDIUM inline="true"}
		{/fbvFormSection}

		{fbvFormSection label="common.description" for="description"}
			{fbvElement type="textarea" multilingual=true name="description" id="description" value=$description rich=true height=$fbvStyles.height.SHORT}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormButtons id="spotlightFormSubmit" submitText="common.save"}
	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
</form>
