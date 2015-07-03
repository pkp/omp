{**
 * templates/header/search.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common search box.
 *}
<script type="text/javascript">
	$(function() {ldelim}
		$('#topSearchFormField').jLabel();
	{rdelim});
</script>

<div class="pkp_structure_search pkp_helpers_align_right">
	<form id="topSearchForm" action="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="results"}" method="post">
		<fieldset>
			<input id="topSearchFormField" name="query" value="{$searchQuery|escape}" type="text" title="{translate key="common.searchCatalog"}..." />
			<button class="go">{translate key="common.go"}</button>
		</fieldset>
	</form>
</div>
