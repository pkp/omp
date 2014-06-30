{**
 * plugins/viewableFile/pdfSubmissionFile/display.tpl
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Embedded viewing of a PDF galley.
 *}

<script src="{$pluginJSPath}/inlinePdf.js"></script>
<script src="{$baseUrl}/lib/pkp/lib/pdfobject/pdfobject.js"></script>

{url|assign:"pdfUrl" op="download" path=$publishedMonograph->getId()|to_array:$submissionFile->getAssocId():$submissionFile->getFileIdAndRevision() inline=true escape=false}{* Assoc ID is publication format *}

{translate|assign:"noPluginText" key="submission.pdf.pluginMissing"}
<script type="text/javascript"><!--{literal}
	$(document).ready(function(){
		if ($.browser.webkit) { // PDFObject does not correctly work with safari's built-in PDF viewer
			var embedCode = "<object id='pdfObject' type='application/pdf' data='{/literal}{$pdfUrl|escape:'javascript'}{literal}' width='99%' height='800px'><div id='pluginMissing'>{/literal}{$noPluginText|escape:'javascript'}{literal}</div></object>";
			$("#inlinePdf").html(embedCode);
			if($("#pluginMissing").is(":hidden")) {
				$('#fullscreenShow').show();
				$("#inlinePdf").resizable({ containment: 'parent', handles: 'se' });
			} else { // Chrome Mac hides the embed object, obscuring the text.  Reinsert.
				$("#inlinePdf").html('{/literal}<div id="pluginMissing">{$noPluginText|escape:"javascript"}</div>{literal}');
			}
		} else {
			var success = new PDFObject({ url: "{/literal}{$pdfUrl|escape:'javascript'}{literal}" }).embed("inlinePdf");
			if (success) {
				// PDF was embedded; enable fullscreen mode and the resizable widget
				$('#fullscreenShow').show();
				$("#inlinePdfResizer").resizable({ containment: 'parent', handles: 'se' });
			}
		}
	});
		{/literal}
	// -->
</script>
<div id="inlinePdfResizer">
	<div id="inlinePdf" class="ui-widget-content">
		{translate key="submission.pdf.pluginMissing"}
	</div>
</div>
<p>
	{* The target="_parent" is for the sake of iphones, which present scroll problems otherwise. *}
	<a class="action" target="_parent" href="{url op="download" path=$publishedMonograph->getId()|to_array:$submissionFile->getAssocId():$submissionFile->getFileIdAndRevision()}">{translate key="submission.pdf.download"}</a>
	<a class="action" href="#" id="fullscreenShow">{translate key="common.fullscreen"}</a>
	<a class="action" href="#" id="fullscreenHide">{translate key="common.fullscreenOff"}</a>
</p>
