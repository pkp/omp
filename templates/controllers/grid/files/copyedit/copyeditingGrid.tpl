{**
 * templates/controllers/grid/files/copyedit.tpl
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Copyediting grid
 *}
<table id="component-copyeditingFiles-table">
	<colgroup>
		{foreach from=$columns item=column}<col />{/foreach}
	</colgroup>
	<thead>
		{** build the column headers **}
		<tr>
			{foreach name=columns from=$columns item=column}
				<th scope="col">
					{$column->getLocalizedTitle()}
					{if $smarty.foreach.columns.last && $grid->getActions($smarty.const.GRID_ACTION_POSITION_LASTCOL)}
						<span class="options pkp_linkActions">
							{foreach from=$grid->getActions($smarty.const.GRID_ACTION_POSITION_LASTCOL) item=action}
								{include file="linkAction/linkAction.tpl" action=$action contextId=$gridId}
							{/foreach}
						</span>
					{/if}
				</th>
			{/foreach}
		</tr>
	</thead>
	{$renderedGridRows}
</table>
