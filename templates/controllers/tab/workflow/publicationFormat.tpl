{**
 * templates/workflow/publicationFormat.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Accordion with publication format grid and related actions.
 *}
{assign var="publicationFormatId" value=$publicationFormat->getId()}

{url|assign:proofGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.proof.ProofFilesGridHandler" op="fetchGrid" submissionId=$submission->getId() publicationFormatId=$publicationFormatId escape=false}
{assign var=proofContainerId value='proofGrid-'|concat:$publicationFormatId|concat:'-'|uniqid}
{load_url_in_div id=$proofContainerId url=$proofGridUrl}
