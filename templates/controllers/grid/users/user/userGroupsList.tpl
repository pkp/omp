{**
 * userGroupsList.tpl
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * List of user groups. 
 *}

{assign var=cellId value="cell-"|concat:$id}
<span id="{$cellId}">
	<ul>	
	{foreach from=$userGroups->toArray() item=userGroup}
		<li>
		{$userGroup->getLocalizedName()|escape}
		</li>
	{/foreach}
	</ul>
</span>
