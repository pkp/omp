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
	{url|assign:galleyFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.galley.GalleyFilesGridHandler" op="fetchGrid" monographId=$monograph->getId() stageId=$stageId escape=false}
	{load_url_in_div id="galleyFilesGridDiv" url=$galleyFilesGridUrl}

	<div id="metadataAccordion">
		<h3><a href="#">{translate key="cataloguing metadata"}</a></h3>
		<div>
			cataloguing metadata container
		</div>
	</div>
	<div id="publicationFormatContainer">
		{iterate from=publicationFormats item=publicationFormat}
			<h3><a href="#">{$publicationFormat->getLocalizedName()|escape}</a></h3>
			<div>
				{url|assign:publicationFormatUrl router=$smarty.const.ROUTE_PAGE op="fetchPublicationFormat" monographId=$monograph->getId() publicationFormatId=$publicationFormat->getId() escape=false}
				{load_url_in_div id="publicationFormatDiv-"|concat:$publicationFormat->getId() url=$publicationFormatUrl}
			</div>
		{/iterate}
	</div>
</div>

{include file="common/footer.tpl"}
