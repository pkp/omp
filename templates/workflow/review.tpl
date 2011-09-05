{**
 * templates/workflow/review.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Review workflow stage.
 *}
{strip}
{include file="workflow/header.tpl"}
{/strip}

<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#reviewTabs').pkpHandler(
				'$.pkp.controllers.TabHandler',
					{ldelim}
						{assign var=roundIndex value=$selectedRound-1}
						selected: {$roundIndex}
					{rdelim}
		);
	{rdelim});
</script>
<div id=reviewTabs>
	<ul>
		{section name="rounds" start=0 loop=$currentRound}
			{assign var="round" value=$smarty.section.rounds.index+1}
			<li>
				<a href="{url op=$reviewRoundOp path=$monograph->getId()|to_array:$round stageId=$stageId}">{translate key="submission.round" round=$round}</a>
			</li>
		{/section}
		{if $newRoundAction}
			<li>
				{** FIXME: this <a> tag is here just to get the CSS to work **}
				<a id="newRoundTabContainer" href="/" style="padding-left: 0px; padding-right: 0px;">{include file="linkAction/linkAction.tpl" image="add_item" action=$newRoundAction contextId="newRoundTabContainer"}</a>
			</li>
		{/if}
	</ul>
</div>

{include file="common/footer.tpl"}
