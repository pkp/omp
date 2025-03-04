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

{**
 * Add as editor to author's name
 * @param array $authorFullNames, Required
 * @param bool $identifyAsEditors, Required
 * @return array, As variable $authorFullNames
*}
{function addAuthorAsEditor}
	{foreach from=$authorFullNames item=$authorFullName key=$locale}
		{if $identifyAsEditors}
			{capture assign="authorFullName"}{translate key="submission.editorName" editorName=$authorFullName}{/capture}
			{$authorFullNames[$locale]=$authorFullName}
		{/if}
	{/foreach}
	{assign "authorFullNames" value=$authorFullNames scope="parent" nocache}
{/function}

{**
 * Authors' affiliations: concat affiliation and ror icon
 * @param array $affiliationNamesWithRors, Required
 * @param string $rorIdIcon, Required
 * @param array $filters, Optional, E.g. ['escape']
 * @return array, As variable $affiliationNamesWithRors
 *}
{function concatAuthorAffiliationsWithRors}
	{foreach from=$affiliationNamesWithRors item=$namesPerLocale key=$locale}
		{foreach from=$namesPerLocale item=$nameWithRor key=$key}
			{* Affiliation name *}
			{$affiliationRor=$nameWithRor.name|useFilters:$filters}
			{* Ror *}
			{if $nameWithRor.ror}
				{capture assign="ror"}<a href="{$nameWithRor.ror|useFilters:$filters}">{$rorIdIcon}</a>{/capture}
				{$affiliationRor="{$affiliationRor}{$ror}"}
			{/if}
			{$affiliationNamesWithRors[$locale][$key]=$affiliationRor}
		{/foreach}
	{/foreach}
	{assign "affiliationNamesWithRors" value=$affiliationNamesWithRors scope="parent" nocache}
{/function}

{**
 * Concat authors' name and affiliation 
 * Filters texts using function filterPubPropValue
 * @param array $authorsWithAffiliationData, Required
 * @param bool $identifyAsEditors, Required
 * @param string $rorIdIcon, Required
 * @param string $separator, Required
 * @param array $filters, Optional, E.g. ['escape']
 * @return array, As varaible $authorsWithAffiliationData
 *}
{function concatAuthorsWithAffiliationData}
	{foreach from=$authorsWithAffiliationData item=$namesWithAffiliationsPerLocale key=$locale}
		{foreach from=$namesWithAffiliationsPerLocale item=$nameWithAffiliations key=$key}
			{* Author's name *}
			{$authorName=$nameWithAffiliations.name}
			{if $identifyAsEditors}
				{capture assign="authorName"}{translate key="submission.editorName" editorName=$authorName}{/capture}
			{/if}
			{capture assign="nameAffiliation"}<span class="label">{$authorName|useFilters:$filters}</span>{/capture}
			{* Author's affiliations *}
			{if isset($nameWithAffiliations.affiliations[$locale])}
				{concatAuthorAffiliationsWithRors affiliationNamesWithRors=$nameWithAffiliations.affiliations rorIdIcon=$rorIdIcon filters=$filters}
				{capture assign="affiliation"}<span class="value">{$separator|join:$affiliationNamesWithRors[$locale]}</span>{/capture}
				{capture assign="nameAffiliation"}{translate key="submission.authorWithAffiliation" name=$nameAffiliation affiliation=$affiliation}{/capture}
			{/if}
			{$authorsWithAffiliationData[$locale][$key]=$nameAffiliation}
		{/foreach}
	{/foreach}
	{assign "authorsWithAffiliationData" value=$authorsWithAffiliationData scope="parent"}
{/function}

<div class="item authors">
	<h2 class="pkp_screen_reader">
		{translate key="submission.authors"}
	</h2>

	{* Only show editors for edited volumes *}
	{if $monograph->getData('workType') == $monograph::WORK_TYPE_EDITED_VOLUME && $editors|@count && !$isChapterRequest}
		{assign var="authors" value=$editors}
		{assign var="identifyAsEditors" value=true}
	{/if}
	{* Comma list separator for authors' affiliations *}
	{capture assign="commaListSeparator"}{translate key="common.commaListSeparator"}{/capture}

	{* Show short author lists on multiple lines *}
	{if $authors|@count < 5}
		{foreach from=$authors item=author}
			<div class="sub_item" aria-live="polite">
				<div class="label">
					{* Publication author name for json *}
					{addAuthorAsEditor authorFullNames=$author|getAuthorFullNames identifyAsEditors=$identifyAsEditors}
					{$pubLocaleData["author{$author@index}Name"]=$authorFullNames|wrapData:"author{$author@index}":['escape']}
					{* Name *}
					<span
						class="name"
						data-pkp-switcher-data="author{$author@index}Name"
						lang="{$pubLocaleData.accessibility.langAttrs[$pubLocaleData["author{$author@index}Name"].defaultLocale]}"
					>
						{$pubLocaleData["author{$author@index}Name"].data[$pubLocaleData["author{$author@index}Name"].defaultLocale]}
					</span>
					{* Author switcher *}
					{if isset($pubLocaleData.opts.author)}
						<span aria-label="{translate key="plugins.themes.default.languageSwitcher.ariaDescription.author"}" role="group" data-pkp-switcher="author{$author@index}">{switcherContainer}</span>
					{/if}
				</div>
				{if $author->getAffiliations()}
					{* Publication author affiliations for json *}
					{concatAuthorAffiliationsWithRors affiliationNamesWithRors=$author|getAffiliationNamesWithRors rorIdIcon=$rorIdIcon filters=['escape']}
					{$pubLocaleData["author{$author@index}Affiliation"]=$affiliationNamesWithRors|wrapData:"author{$author@index}":null:$commaListSeparator}
					{* Affiliation *}
					<div class="value affiliation">
						<span
							data-pkp-switcher-data="author{$author@index}Affiliation"
							lang="{$pubLocaleData.accessibility.langAttrs[$pubLocaleData["author{$author@index}Affiliation"].defaultLocale]}"
						>
							{$pubLocaleData["author{$author@index}Affiliation"].data[$pubLocaleData["author{$author@index}Affiliation"].defaultLocale]}
						</span>
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
		{capture assign="authorSeparator"}{translate key="submission.authorListSeparator"}{/capture}
		{* Publication authors for json *}
		{concatAuthorsWithAffiliationData authorsWithAffiliationData=$authors|getAuthorsFullNamesWithAffiliations identifyAsEditors=$identifyAsEditors rorIdIcon=$rorIdIcon separator=$commaListSeparator filters=['escape']}
		{$pubLocaleData.authors=$authorsWithAffiliationData|wrapData:"authors":null:$authorSeparator}
		{* Names and affiliations *}
		<span aria-live="polite" data-pkp-switcher-data="authors" lang="{$pubLocaleData.accessibility.langAttrs[$pubLocaleData.authors.defaultLocale]}">
			{$pubLocaleData.authors.data[$pubLocaleData.authors.defaultLocale]}
		</span>
		{* Authors switcher *}
		{if isset($pubLocaleData.opts.author)}
			<span aria-label="{translate key="plugins.themes.default.languageSwitcher.ariaDescription.author"}" role="group" data-pkp-switcher="authors">{switcherContainer}</span>
		{/if}
	{/if}
</div>
