{**
 * templates/controllers/grid/content/spotlights/form/spotlightForm.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
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

<form class="pkp_form" id="spotlightForm" method="post" action="{url router=PKPApplication::ROUTE_COMPONENT component="grid.content.spotlights.ManageSpotlightsGridHandler" op="updateSpotlight"}">
	{csrf}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="spotlightFormNotification"}
	{fbvFormArea id="spotlightInfo"}
		{if $spotlight}
			<input type="hidden" name="spotlightId" value="{$spotlight->getId()|escape}" />
		{/if}
		{fbvFormSection label="grid.content.spotlights.form.item" for="assocId" required="true"}
			{fbvElement type="autocomplete" name="assocId" id="assocId" required="true" value=$assocTitle autocompleteValue=$assocId  autocompleteUrl=$addSpotlightItemUrl size=$fbvStyles.size.LARGE  disableSync="true"}
		{/fbvFormSection}
		{fbvFormSection label="grid.content.spotlights.form.title" for="title" required="true"}
			{fbvElement type="text" multilingual="true" name="title" id="title" required="true" value=$title maxlength="255" size=$fbvStyles.size.LARGE}
		{/fbvFormSection}
		{fbvFormSection label="common.description" for="description"}
			{fbvElement type="textarea" multilingual=true name="description" id="description" value=$description rich=true height=$fbvStyles.height.SHORT}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormButtons id="spotlightFormSubmit" submitText="common.save"}
	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
</form>
