{**
 * templates/workflow/production.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Production workflow stage
 *}
{strip}
{include file="workflow/header.tpl"}
{/strip}

<script type="text/javascript">
	// Initialise JS handler.
	$(function() {ldelim}
		$('#production').pkpHandler(
			'$.pkp.pages.workflow.ProductionHandler',
			{ldelim}
				accordionUrl: '{url|escape:"javascript" op="productionFormatsAccordion" monographId=$monograph->getId()}'
			{rdelim}
		);
	{rdelim});
</script>

<div id="production">
	{url|assign:productionReadyFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.productionReady.ProductionReadyFilesGridHandler" op="fetchGrid" monographId=$monograph->getId() stageId=$stageId escape=false}
	{load_url_in_div id="productionReadyFilesGridDiv" url=$productionReadyFilesGridUrl}

	<div id="metadataAccordion">
		<h3><a href="#">{translate key="submission.metadata"}</a></h3>
		<div>
			{url|assign:submissionMetadataViewFormUrl router=$smarty.const.ROUTE_COMPONENT  component="modals.submissionMetadata.ProductionSubmissionMetadataHandler" op="fetch" monographId=$monograph->getId() stageId=$stageId escape=false}
			{load_url_in_div id="submissionMetadataFormWrapper" url=$submissionMetadataViewFormUrl}
		</div>

		<h3><a href="#">{translate key="monograph.publicationFormats"}</a></h3>
		<div>
			{fbvFormArea id="publicationFormats"}
				{fbvFormSection}
					<!--  Formats -->
					{url|assign:formatGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.catalogEntry.PublicationFormatGridHandler" op="fetchGrid" monographId=$monograph->getId()}
					{load_url_in_div id="formatsGridContainer" url="$formatGridUrl"}
				{/fbvFormSection}
			{/fbvFormArea}
		</div>
	</div>

	<div id="publicationFormatContainer">
		{* Will be filled in by Javascript *}
	</div>
</div>

{include file="common/footer.tpl"}
