{**
 * templates/catalog/index.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Header that contains details about the submission
 *}

{strip}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
	// Initialise JS handler.
	$(function() {ldelim}
		$('#catalogHeader').pkpHandler(
			'$.pkp.pages.catalog.CatalogHeaderHandler'
		);
	{rdelim});
</script>

<div id="catalogHeader" class="pkp_submission_header">
</div>

{include file="common/footer.tpl"}

