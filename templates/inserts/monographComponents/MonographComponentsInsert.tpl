
<input type="hidden" name="isEditedVolume" value="{$isEditedVolume}" />

{if $componentSummary}

{else}

	{include file="inserts/contributors/ContributorInsert.tpl"}
	<div class="separator"></div>
	{if !$authors_only}
	{include file="inserts/monographComponents/components.tpl"}
	<div class="separator"></div>
	{/if}

{/if}
