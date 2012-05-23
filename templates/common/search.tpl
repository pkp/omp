{**
 * templates/common/search.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
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
	<form id="topSearchForm" action="{url page="catalog" op="results"}" method="post">
		<input id="topSearchFormField" name="query" value="{$searchQuery|escape}" type="text" title="{translate key="common.searchCatalog"}..." />
		<button class="go">{translate key="common.go"}</button>
	</form>
</div>
