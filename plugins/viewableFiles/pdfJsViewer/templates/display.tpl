{**
 * plugins/viewableFile/pdfSubmissionFile/display.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Embedded viewing of a PDF galley.
 *}
{url|assign:"pdfUrl" op="download" path=$publishedMonograph->getId()|to_array:$submissionFile->getAssocId():$submissionFile->getFileIdAndRevision() inline=true escape=false}{* Assoc ID is publication format *}
{include file="$pluginTemplatePath/pdfViewer.tpl" pdfUrl=$pdfUrl}
