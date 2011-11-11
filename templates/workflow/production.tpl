{**
 * templates/workflow/production.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
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
			'$.pkp.pages.workflow.ProductionHandler'
		);
	{rdelim});
</script>

<div id="production">
	{url|assign:productionReadyFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.productionReady.ProductionReadyFilesGridHandler" op="fetchGrid" monographId=$monograph->getId() stageId=$stageId escape=false}
	{load_url_in_div id="productionReadyFilesGridDiv" url=$productionReadyFilesGridUrl}

	<div id="metadataAccordion">
		<h3><a href="#">{translate key="submission.cataloguingMetadata"}</a></h3>
		<div>
			{url|assign:submissionMetadataViewFormUrl router=$smarty.const.ROUTE_COMPONENT  component="modals.submissionMetadata.ProductionSubmissionMetadataHandler" op="fetch" monographId=$monograph->getId() stageId=$stageId escape=false}
			{load_url_in_div id="submissionMetadataFormWrapper" url=$submissionMetadataViewFormUrl}
		</div>
	</div>
	<div id="publicationFormatContainer">
		{iterate from=publicationFormats item=publicationFormat}
			<h3><a href="#">{$publicationFormat->getLocalizedName()|escape}</a></h3>
			<div>
				{url|assign:publicationFormatUrl router=$smarty.const.ROUTE_PAGE op="fetchPublicationFormat" monographId=$monograph->getId() publicationFormatId=$publicationFormat->getId() escape=false}
				{load_url_in_div id="publicationFormatDiv-"|concat:$publicationFormat->getId() class="stageParticipantGridContainer" url=$publicationFormatUrl}
			</div>
		{/iterate}
	</div>
</div>

{include file="common/footer.tpl"}
