{**
 * plugins/viewableFile/pdfSubmissionFile/display.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Embedded viewing of a PDF galley.
 *
 * @uses $publishedSubmission Monograph Monograph this file is attached to
 * @uses $downloadUrl string (optional) A URL to download this file
 * @uses $pluginUrl string URL to this plugin's files
 *}
{* Display metadata *}
{include file="frontend/objects/monographFile_dublinCore.tpl" monograph=$publishedSubmission}
{include file="frontend/objects/monographFile_googleScholar.tpl" monograph=$publishedSubmission}

<div class="viewable_file_frame">
    <iframe class="viewable_file_frame" src="{$pluginUrl}/pdf.js/web/viewer.html?file={$downloadUrl|escape:"url"}" allowfullscreen webkitallowfullscreen></iframe>
</div>
