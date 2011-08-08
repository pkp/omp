{**
 * templates/workflow/publicationFormat.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Accordion with publication format grid and related actions.
 *}
{assign var='publicationFormatId' value=$publicationFormat->getId()}
<div id="publication_format_{$publicationFormatId}">
	{url|assign:proofGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.proof.ProofFilesGridHandler" op="fetchGrid" monographId=$monograph->getId() publicationFormatId=$publicationFormatId escape=false}
	{load_url_in_div id="proofGrid-$publicationFormatId" url=$proofGridUrl}
</div>
