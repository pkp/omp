{**
 * templates/workflow/production.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Production workflow stage
 *}
{strip}
{include file="common/header.tpl"}
{/strip}

{include file="workflow/header.tpl"}

<script type="text/javascript">
	// Initialise JS handler.
	$(function() {ldelim}
		$('#production').pkpHandler(
			'$.pkp.pages.workflow.ProductionHandler'
		);
	{rdelim});
</script>

<div class="ui-widget ui-widget-content ui-corner-all" id="production">
	<!-- TEMPORARY: Drop a grid in here. Sample content only. -->
	{url|assign:copyeditingFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.copyedit.AuthorCopyeditingFilesGridHandler" op="fetchGrid" monographId=$monograph->getId() escape=false}
	{load_url_in_div id="copyeditingFilesGridDiv" url=$copyeditingFilesGridUrl}

	<div id="productionAccordion">
		<h3><a href="#">Accordion</a></h3>
		<!-- Accordion contents go here -->
		{url|assign:copyeditingFilesGrid2Url router=$smarty.const.ROUTE_COMPONENT component="grid.files.copyedit.AuthorCopyeditingFilesGridHandler" op="fetchGrid" monographId=$monograph->getId() escape=false}
		{load_url_in_div id="copyeditingFilesGrid2Div" url=$copyeditingFilesGrid2Url}
	</div>
</div>

{include file="common/footer.tpl"}
