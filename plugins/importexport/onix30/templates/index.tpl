{**
 * plugins/importexport/onix30/index.tpl
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * List of operations this plugin can perform
 *}
{strip}
{assign var="pageTitle" value="plugins.importexport.onix30.displayName"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#importTabs').pkpHandler('$.pkp.controllers.TabHandler');
	{rdelim});
</script>
<div id="importTabs">
	<ul>
		<li><a href="#export-tab">{translate key="plugins.importexport.native.export"}</a></li>
	</ul>
	<div id="export-tab">
		<script type="text/javascript">
			$(function() {ldelim}
				// Attach the form handler.
				$('#exportXmlForm').pkpHandler('$.pkp.controllers.form.FormHandler');
			{rdelim});
		</script>
		<form id="exportXmlForm" class="pkp_form" action="{plugin_url path="export"}" method="post">
			{fbvFormArea id="exportForm"}
				{url|assign:submissionsListGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.exportableSubmissions.ExportableSubmissionsListGridHandler" op="fetchGrid"}
				{load_url_in_div id="submissionsListGridContainer" url=$submissionsListGridUrl}
				{fbvFormButtons hideCancel="true"}
			{/fbvFormArea}
		</form>
	</div>
</div>


{include file="common/footer.tpl"}
