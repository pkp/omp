{**
 * statusCell.tpl
 *
 * Copyright (c) 2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * a regular grid cell (with or without actions)
 *}
{assign var=cellId value="cell-"|concat:$id}
<span id="{$cellId}" class="pkp_linkActions">
	{if count($actions) gt 0}
		{assign var=defaultCellAction value=$actions[0]}
		{if is_a($defaultCellAction, 'LegacyLinkAction')}
			{include file="linkAction/legacyLinkAction.tpl" id=$cellId|concat:"-action-":$defaultCellAction->getId() action=$defaultCellAction objectId=$cellId actionCss="task"}
		{else}
			{include file="linkAction/linkAction.tpl" action=$defaultCellAction contextId=$cellId}
		{/if}
	{else}
		<a class="task {$status}">status</a>
	{/if}
</span>

