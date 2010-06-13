{**
 * seriesForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Group form under press management.
 *
 * $Id$
 *}

<form name="seriesForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.series.SeriesGridHandler" op="updateSeries"}">
{include file="common/formErrors.tpl"}

{fbvFormArea id="mastheadInfo"}
{fbvFormSection title="common.name" required="true" for="title"}
	{fbvElement type="text" id="title" value="$title" maxlength="80" required="true"}
{/fbvFormSection}
{fbvFormSection title="settings.setup.division" for="context"}
<select name="division" class="field select">
	<option>{translate key='common.none'}</option>
	{foreach from=$divisions item=division}
	<option value="{$division.id}" {if $currentDivision == $division.id}selected="selected"{/if}>{$division.title}</option>
	{/foreach}
</select>
{/fbvFormSection}


{fbvFormSection title="user.affiliation" for="context"}
 	{fbvElement type="text" id="affiliation" value="$affiliation" maxlength="80" required="true"}
{/fbvFormSection}
{/fbvFormArea}

{if $gridId}
	<input type="hidden" name="gridId" value="{$gridId|escape}" />
{/if}
{if $rowId}
	<input type="hidden" name="rowId" value={$rowId|escape} />
{/if}
{if $seriesId}
	<input type="hidden" name="seriesId" value="{$seriesId|escape}" />
{/if}

<br />
{if $seriesId}
	<input type="hidden" name="seriesId" value="{$seriesId}"/>
	{url|assign:seriesEditorsUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.settings.SeriesEditorsListbuilderHandler" op="fetch" seriesId=$seriesId}
	{* Need a random div ID to load listbuilders in modals *}
	{assign var='randomId' value=1|rand:99999}
	{load_url_in_div id=$randomId url=$seriesEditorsUrl}
{/if}

</form>
