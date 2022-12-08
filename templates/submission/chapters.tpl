{**
 * templates/submission/chapters.tpl
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2003-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Template which adds the chapters grid to the submission wizard
 *}
{capture assign=chaptersGridUrl}{url router=$smarty.const.ROUTE_COMPONENT  component="grid.users.chapter.ChapterGridHandler" op="fetchGrid" submissionId=$submission->getId() publicationId=$submission->getCurrentPublication()->getId() escape=false}{/capture}
{load_url_in_div id="chaptersGridContainer" url=$chaptersGridUrl inVueEl=true}