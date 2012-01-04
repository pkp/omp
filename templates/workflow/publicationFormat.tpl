{**
 * templates/workflow/publicationFormat.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Accordion with publication format grid and related actions.
 *}
{assign var="publicationFormatId" value=$publicationFormat->getId()}

{url|assign:proofGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.proof.ProofFilesGridHandler" op="fetchGrid" monographId=$monograph->getId() publicationFormatId=$publicationFormatId escape=false}
{load_url_in_div id="proofGrid-$publicationFormatId" url=$proofGridUrl}

{include file="linkAction/linkAction.tpl" action=$approveProofAction}
