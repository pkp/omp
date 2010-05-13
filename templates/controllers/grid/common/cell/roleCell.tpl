{**
 * roleCell.tpl
 *
 * Copyright (c) 2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * a regular grid cell (with or without actions)
 *}
{assign var=cellId value="cell-"|concat:$id}
<td id="{$cellId}">
	{if count($actions) gt 0}
		{assign var=defaultCellAction value=$actions[0]}
		{include file="controllers/grid/gridAction.tpl" id=$cellId|concat:"-action-":$defaultCellAction->getId() action=$defaultCellAction objectId=$cellId actionCss="task"}
	{else}
		<a href="#" class="task {$status}">status</a>
	{/if}
</td>
