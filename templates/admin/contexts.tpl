{**
 * templates/admin/contexts.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display list of contexts in administration.
 *}
{strip}
{assign var="pageTitle" value="press.presses"}
{include file="common/header.tpl"}
{/strip}

<div class="pkp_page_content pkp_page_admin">

	<script type="text/javascript">
		// Initialise JS handler.
		$(function() {ldelim}
			$('#contexts').pkpHandler(
					'$.pkp.pages.admin.ContextsHandler');
		{rdelim});
	</script>

	<div id="contexts">
		{if $openWizardLinkAction}
			<div id="{$openWizardLinkAction->getId()}" class="pkp_linkActions inline">
				{include file="linkAction/linkAction.tpl" action=$openWizardLinkAction contextId="contexts" selfActivate=true}
			</div>
		{/if}

		{url|assign:contextsUrl router=$smarty.const.ROUTE_COMPONENT component="grid.admin.press.PressGridHandler" op="fetchGrid" escape=false}
		{load_url_in_div id="contextGridContainer" url=$contextsUrl}
	</div>
</div>

{include file="common/footer.tpl"}
