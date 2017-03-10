{**
 * controllers/modals/submissionMetadata/form/selectMonographForm.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a monograph selection form with the monograph's metadata
 * below.
 *
 *}

{* Help Link *}
{help file="catalog.md#new-catalog-entry" class="pkp_help_modal"}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler. (Triggers selectMonograph event.)
		$('#selectMonographForm').pkpHandler(
			'$.pkp.controllers.form.DropdownHandler',
			{ldelim}
				getOptionsUrl: {url|json_encode router=$smarty.const.ROUTE_COMPONENT op="getSubmissions" escape=false},
				eventName: 'selectMonograph'
			{rdelim}
		);
		// Attach the containing div handler. (Consumes selectMonograph event.)
		$('#selectMonographContainer').pkpHandler(
			'$.pkp.controllers.modals.submissionMetadata.MonographlessCatalogEntryHandler',
			{ldelim}
				metadataFormUrlTemplate: {url|json_encode router=$smarty.const.ROUTE_COMPONENT component="modals.submissionMetadata.CatalogEntryHandler" op="fetch" submissionId=MONOGRAPH_ID stageId=STAGE_ID escape=false}
			{rdelim}
		);
	{rdelim});
</script>

<div id="selectMonographContainer">

	<form class="pkp_form" id="selectMonographForm">
		{fbvFormArea id="monographSelectForm"}
			{fbvFormSection}
				{fbvElement type="select" class="noStyling" id="monographSelect" from="submission.select"|translate|to_array translate=false}
			{/fbvFormSection}
		{/fbvFormArea}
	</form>

	<div id="metadataFormContainer">
	</div>
</div>
