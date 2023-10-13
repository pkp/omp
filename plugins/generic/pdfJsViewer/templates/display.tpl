{**
 * plugins/generic/pdfJsViewer/templates/display.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Embedded viewing of a PDF galley.
 *
 * @hook Templates::Common::Footer::PageFooter []
 *}
<!DOCTYPE html>
<html lang="{$currentLocale|replace:"_":"-"}" xml:lang="{$currentLocale|replace:"_":"-"}">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>{translate key="catalog.viewableFile.title" type=$publicationFormat->getLocalizedName()|escape title=$submissionFile->getLocalizedData('name')|escape}</title>

	{load_header context="frontend" headers=$headers}
	{load_stylesheet context="frontend" stylesheets=$stylesheets}
	{load_script context="frontend" scripts=$scripts}
</head>
<body class="pkp_page_{$requestedPage|escape} pkp_op_{$requestedOp|escape}">

	{* Header wrapper *}
	<header class="header_viewable_file">

		{capture assign="submissionUrl"}{url op="book" path=$publishedSubmission->getBestId()}{/capture}

		<a href="{$submissionUrl}" class="return">
			<span class="pkp_screen_reader">
				{translate key="catalog.viewableFile.return" monographTitle=$publishedSubmission->getLocalizedTitle()|escape}
			</span>
		</a>

		<span class="title">
			{$submissionFile->getLocalizedData('name')|escape}
		</span>

		<a href="{$downloadUrl|escape}" class="download" download>
			<span class="label">
				{translate key="common.download"}
			</span>
			<span class="pkp_screen_reader">
				{translate key="common.downloadPdf"}
			</span>
		</a>

	</header>

	<script type="text/javascript" src="{$pluginUrl}/pdf.js/build/pdf.js"></script>
	<script type="text/javascript">
		{literal}
			$(document).ready(function() {
				PDFJS.workerSrc='{/literal}{$pluginUrl}/pdf.js/build/pdf.worker.js{literal}';
				PDFJS.getDocument({/literal}'{$downloadUrl|escape:"javascript"}'{literal}).then(function(pdf) {
					// Using promise to fetch the page
					pdf.getPage(1).then(function(page) {
						var pdfCanvasContainer = $('#pdfCanvasContainer');
						var canvas = document.getElementById('pdfCanvas');
						canvas.height = pdfCanvasContainer.height();
						canvas.width = pdfCanvasContainer.width()-2; // 1px border each side
						var viewport = page.getViewport(canvas.width / page.getViewport(1.0).width);
						var context = canvas.getContext('2d');
						var renderContext = {
							canvasContext: context,
							viewport: viewport
						};
						page.render(renderContext);
					});
				});
			});
		{/literal}
	</script>
	<script type="text/javascript" src="{$pluginUrl}/pdf.js/web/viewer.js"></script>
	<script type="text/javascript">
		// Creating iframe's src in JS instead of Smarty so that EZProxy-using sites can find our domain in $pdfUrl and do their rewrites on it.
		$(document).ready(function() {ldelim}
			var urlBase = "{$pluginUrl}/pdf.js/web/viewer.html?file=";
			var pdfUrl = {$downloadUrl|json_encode};
			$("#pdfCanvasContainer > iframe").attr("src", urlBase + encodeURIComponent(pdfUrl));
		{rdelim});
	</script>

	<div id="pdfCanvasContainer" class="viewable_file_frame{if !$isLatestPublication} viewable_file_frame_with_notice{/if}">
		{if !$isLatestPublication}
			<div class="viewable_file_frame_notice">
				<div class="viewable_file_frame_notice_message" role="alert">
					{translate key="submission.outdatedVersion" datePublished=$filePublication->getData('datePublished') urlRecentVersion=$submissionUrl}
				</div>
			</div>
		{/if}
		<iframe src="" width="100%" height="100%" style="min-height: 500px;" allowfullscreen webkitallowfullscreen></iframe>
	</div>
	{call_hook name="Templates::Common::Footer::PageFooter"}
</body>
</html>
