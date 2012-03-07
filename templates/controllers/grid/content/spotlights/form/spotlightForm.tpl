{**
 * templates/controllers/grid/content/spotlights/form/spotlightForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Announcement form to read/create/edit spotlights.
 *}
 {url|assign:addSpotlightItemUrl op="itemAutocomplete" pressId=$pressId type=$type escape=false}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#spotlightForm').pkpHandler('$.pkp.controllers.grid.content.spotlights.form.SpotlightFormHandler',
			{ldelim}
				autocompleteUrl: '{$addSpotlightItemUrl}'
			{rdelim}
		);
	{rdelim});
</script>

<form class="pkp_form" id="spotlightForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.content.spotlights.ManageSpotlightsGridHandler" op="updateSpotlight"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="spotlightFormNotification"}
	{fbvFormArea id="spotlightInfo"}
		{if $spotlight}
			<input type="hidden" name="spotlightId" value="{$spotlight->getId()|escape}" />
		{/if}

		{fbvFormSection for="title"}
			{fbvElement type="text" multilingual="true" id="title" required="true" label="common.title" value=$title maxlength="255" size=$fbvStyles.size.MEDIUM inline="true"}
			{fbvElement type="select" id="type" from=$spotlightTypes selected=$type translate=false required="true" label="grid.content.spotlights.form.type" size=$fbvStyles.size.MEDIUM inline="true"}
		{/fbvFormSection}

		{fbvFormSection label="common.description" for="description"}
			{fbvElement type="textarea" multilingual=true name="description" id="description" value=$description rich=true height=$fbvStyles.height.SHORT}
		{/fbvFormSection}

		{fbvFormSection}
			{fbvElement type="autocomplete" id="assocId" required="true" value=$assocTitle autocompleteValue=$assocId label="grid.content.spotlights.form.item" autocompleteUrl=$addSpotlightItemUrl size=$fbvStyles.size.MEDIUM inline="true"}
			{fbvElement type="select" id="location" from=$spotlightLocations selected=$location translate=false required="true" label="grid.content.spotlights.form.location" size=$fbvStyles.size.MEDIUM inline="true"}
		{/fbvFormSection}

	{/fbvFormArea}
	{fbvFormButtons id="spotlightFormSubmit" submitText="common.save"}
</form>