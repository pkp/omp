{**
 * userGroupOptions.tpl
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Grid filter user group options.
 *}
<select name="userGroup" id="userGroup" size="1" class="selectMenu">
	{html_options options=$filterData.userGroupOptions selected=$filterSelectionData.userGroup|escape}
</select>