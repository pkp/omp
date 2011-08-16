{**
 * controllers/grid/settings/user/gridFilterElements/searchInput.tpl
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Grid filter search input and submit.
 *}
{fbvFormSection title="common.search" required="true" for="search"}
	{fbvElement type="text" name="search" id="search" value=$filterSelectionData.search size=$fbvStyles.size.LONG}
{/fbvFormSection}
{if $filterSelectionData.includeNoRole}{assign var="checked" value="checked"}{/if}
{fbvElement type="checkbox" name="includeNoRole" id="includeNoRole" value="1" checked=$checked label="user.noRoles.selectUsersWithoutRoles" translate="true"}

{fbvElement id="submit" type="submit" label="common.search"}
