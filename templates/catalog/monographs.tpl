{**
 * templates/catalog/monographs.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Present a list of monographs.
 *}
<div id="monographsContainer">
	<ul class="pkp_catalog_monographList">
		{iterate from=publishedMonographs item=monograph}
			{include file="catalog/monograph.tpl"}
		{/iterate}
	</ul>
</div>
