{**
 * templates/submission/form/step3.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Step 3 of author monograph submission.
 *}
{capture assign="additionalContributorsFields"}
	<!--  Chapters -->
	{url|assign:chaptersGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.users.chapter.ChapterGridHandler" op="fetchGrid" submissionId=$submissionId escape=false}
	{load_url_in_div id="chaptersGridContainer" url=$chaptersGridUrl}
{/capture}

{capture assign="additionalFormFields"}
	{include file="submission/form/categories.tpl"}
{/capture}

{include file="core:submission/form/step3.tpl"}
