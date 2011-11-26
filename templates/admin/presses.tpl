{**
 * templates/admin/presses.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display list of presses in administration.
 *}
{strip}
{assign var="pageTitle" value="press.presses"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
	// Initialise JS handler.
	$(function() {ldelim}
		$('#presses').pkpHandler(
				'$.pkp.pages.admin.PressesHandler');
	{rdelim});
</script>

<div id="presses" >

	{if $openWizardLinkAction}
		<div id="{$openWizardLinkAction->getId()}" class="pkp_linkActions inline">
			{include file="linkAction/linkAction.tpl" action=$openWizardLinkAction contextId="presses" selfActivate=true}
		</div>
	{/if}

	{url|assign:pressesUrl router=$smarty.const.ROUTE_COMPONENT component="grid.admin.press.PressGridHandler" op="fetchGrid"}
	{load_url_in_div id="pressGridContainer" url=$pressesUrl}
</div>

{include file="common/footer.tpl"}