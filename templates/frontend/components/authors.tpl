{**
 * templates/frontend/components/authors.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
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
				<div class="label name">
					{if $identifyAsEditors}
						{translate key="submission.editorName" editorName=$author->getFullName()|escape}
					{else}
						{$author->getFullName()|escape}
					{/if}
					{if isset($pubLocaleData.opts.author)}
						<span class="collapse-switcher" data-pkp-switcher-target="author{$author@index}" aria-description="{translate key="plugins.themes.default.ariaDescription.languageSwitcher"}"></span>
					{/if}
				</div>
				{if $author->getData('affiliation')}
					<span class="affiliation">
						<span id="publication-author{$author@index}" data-pkp-switcher-text="author{$author@index}">
							{$authorAffiliations=$author->getData('affiliation')}
							{$first=true}
							{foreach from=$pubLocaleData.localeOrder item=$localeKey}
								{if !isset($authorAffiliations[$localeKey])}{continue}{/if}
								<span
									class="collapse-text{if $first} show-text{/if}"
									lang="{$pubLocaleData.langTags[$localeKey]}"
									data-pkp-locale="{$localeKey}"
									data-pkp-locale-name="{$pubLocaleData.langTags[$localeKey]}"
								>
									{$authorAffiliations[$localeKey]|escape}
								</span>
								{if !isset($pubLocaleData.opts.author)}{break}{/if}
								{$first=false}
							{/foreach}
						</span>
					</span>
				{/if}
				{if $author->getOrcid()}
					<span class="orcid">
						<a href="{$author->getOrcid()|escape}" target="_blank">
							{$author->getOrcid()|escape}
						</a>
					</span>
				{/if}
			</div>
		{/foreach}

		{* Show long author lists on one line *}
	{else}
		<span id="publication-authors" data-pkp-switcher-text="authors">
		{$first=true}
		{foreach from=$pubLocaleData.localeOrder item=$localeKey}
			<span 
				class="collapse-text{if $first} show-text{/if}"
				data-pkp-locale="{$localeKey}"
				data-pkp-locale-name="{$pubLocaleData.langTags[$localeKey]}"
			>
			{foreach from=$authors item=author}
				{* strip removes excess white-space which creates gaps between separators *}
				{strip}
					{$authorAffiliations=$author->getData('affiliation')}
					{if isset($authorAffiliations[$localeKey])}
						{if $identifyAsEditors}
							{capture assign="authorName"}<span class="label">{translate key="submission.editorName" editorName=$author->getFullName()|escape}</span>{/capture}
						{else}
							{capture assign="authorName"}<span class="label">{$author->getFullName()|escape}</span>{/capture}
						{/if}
						{capture assign="authorAffiliation"}<span class="value" lang="{$pubLocaleData.langTags[$localeKey]}">{$authorAffiliations[$localeKey]|escape}</span>{/capture}
						{translate key="submission.authorWithAffiliation" name=$authorName affiliation=$authorAffiliation}
					{else}
						<span class="label">{$author->getFullName()|escape}</span>
					{/if}
					{if !$author@last}
						{translate key="submission.authorListSeparator"}
					{/if}
				{/strip}
			{/foreach}
			</span>
			{if !isset($pubLocaleData.opts.author)}{break}{/if}
			{$first=false}
		{/foreach}
		{if isset($pubLocaleData.opts.author)}
			<span class="collapse-switcher" data-pkp-switcher-target="authors" aria-description="{translate key="plugins.themes.default.ariaDescription.languageSwitcher"}"></span>
		{/if}
		</span>
	{/if}
</div>
