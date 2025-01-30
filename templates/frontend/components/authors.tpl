{**
 * templates/frontend/components/authors.tpl
 *
 * Copyright (c) 2014-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Display authors of book or chapter
 *
 * @uses $monograph Monograph The monograph to be displayed
 * @uses $authors Array List of authors associated with this monograph or chapter
 * @uses $editors Array List of editors for this monograph if this is an edited
 *       volume. Otherwise empty.
 * @uses $isChapterRequest bool Is true, if a chapter landing page is requested and not a monograph landing page
 *}

<div class="item authors">
	<h2 class="pkp_screen_reader">
		{translate key="submission.authors"}
	</h2>

	{* Only show editors for edited volumes *}
	{if $monograph->getData('workType') == $monograph::WORK_TYPE_EDITED_VOLUME && $editors|@count && !$isChapterRequest}
		{assign var="authors" value=$editors}
		{assign var="identifyAsEditors" value=true}
	{/if}

	{* Show short author lists on multiple lines *}
	{if $authors|@count < 5}
		{foreach from=$authors item=author}
			<div class="sub_item">
				<div class="label">
					{if $identifyAsEditors}
						{translate key="submission.editorName" editorName=$author->getFullName()|escape}
					{else}
						{$author->getFullName()|escape}
					{/if}
				</div>
				{if count($author->getAffiliations()) > 0}
					<div class="value">
						{foreach name="affiliations" from=$author->getAffiliations() item="affiliation"}
							{$affiliation->getLocalizedName()|escape}
							{if $affiliation->getRor()}<a href="{$affiliation->getRor()|escape}">{$rorIdIcon}</a>{/if}
							{if !$smarty.foreach.affiliations.last}{translate key="common.commaListSeparator"}{/if}
						{/foreach}
					</div>
				{/if}
				{if $author->getOrcid()}
					<span class="orcid">
						{if $author->hasVerifiedOrcid()}
							{$orcidIcon}
						{else}
							{$orcidUnauthenticatedIcon}
						{/if}
						<a href="{$author->getOrcid()|escape}" target="_blank">
							{$author->getOrcidDisplayValue()|escape}
						</a>
					</span>
				{/if}
			</div>
		{/foreach}

		{* Show long author lists on one line *}
	{else}
		{foreach name="authors" from=$authors item=author}
			{* strip removes excess white-space which creates gaps between separators *}
			{strip}
				{if $author->getLocalizedAffiliation()}
					{if $identifyAsEditors}
						{capture assign="authorName"}<span class="label">{translate key="submission.editorName" editorName=$author->getFullName()|escape}</span>{/capture}
					{else}
						{capture assign="authorName"}<span class="label">{$author->getFullName()|escape}</span>{/capture}
					{/if}
					{capture assign="authorAffiliations"}<span class="value">{$author->getLocalizedAffiliationNamesAsString(null, ', ')|escape}</span>{/capture}
					{translate key="submission.authorWithAffiliation" name=$authorName affiliation=$authorAffiliation}
				{else}
					<span class="label">{$author->getFullName()|escape}</span>
				{/if}
				{if !$smarty.foreach.authors.last}
					{translate key="submission.authorListSeparator"}
				{/if}
			{/strip}
		{/foreach}
	{/if}
</div>
