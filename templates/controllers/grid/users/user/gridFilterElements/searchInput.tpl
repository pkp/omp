{**
 * searchInput.tpl
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Grid filter search input and submit.
 *}
<select name="searchField" id="searchField" size="1" class="selectMenu">
	{html_options_translate options=$filterData.fieldOptions selected=$filterSelectionData.searchField|escape}
</select>
<select name="searchMatch" id="searchMatch" size="1" class="selectMenu">
	{html_options_translate options=$filterData.matchOptions selected=$filterSelectionData.searchMatch|escape}
</select>
<input type="text" size="15" name="search" id="search" class="textField" value="{$search|escape}" />&nbsp;<input type="submit" name="searchButton" id="searchButton" value="{translate key="common.search"}" class="button" />