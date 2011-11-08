{**
 * controllers/modals/submissionMetadata/form/selectMonographForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a monograph selection form with the monograph's metadata
 * below.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler. (Triggers selectMonograph event.)
		$('#selectMonographForm').pkpHandler(
			'$.pkp.controllers.modals.submissionMetadata.SelectMonographFormHandler',
			{ldelim}
				getSubmissionsUrl: '{url|escape:"javascript" router=$smarty.const.ROUTE_COMPONENT op="getSubmissions" escape=false}'
			{rdelim}
		);
		// Attach the containing div handler. (Consumes selectMonograph event.)
		$('#selectMonographContainer').pkpHandler('$.pkp.controllers.modals.submissionMetadata.MonographlessCatalogEntryHandler');
	{rdelim});
</script>

<div id="selectMonographContainer">
	<form class="pkp_form" id="selectMonographForm">
		<select id="monographSelect">
			{* The JavaScript handler will populate this list. *}
			<option value="">{translate key="common.loading"}</option>
		</select>
	</form>

	<div id="metadataFormContainer">
	</div>
</div>
