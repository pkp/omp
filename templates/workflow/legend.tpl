{**
 * templates/workflow/legend.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display workflow legend
 *}

<div id="legend">
	<table class="legend">
		<tr>
			<td colspan="2" class="pkp_legend_header pkp_helpers_text_center"><p><strong>{translate key="editor.monograph.legend.submissionActions"}</strong> -
			{translate key="editor.monograph.legend.submissionActionsDescription"}</p></td>
		</tr>
		<tr>
			<td colspan="2"><a href="javascript:$.noop();" class="sprite information">{translate key="submission.catalogEntry"}</a> {translate key="editor.monograph.legend.catalogEntry"}</td>
		</tr>
		<tr>
			<td colspan="2"><a href="javascript:$.noop();" class="sprite more_info">{translate key="informationCenter.bookInfo"}</a> {translate key="editor.monograph.legend.bookInfo"}</td>
		</tr>
		<tr>
			<td colspan="2"><a href="javascript:$.noop();" class="sprite participants">{translate key="editor.monograph.stageParticipants"}</a> {translate key="editor.monograph.legend.participants"}</td>
		</tr>
		<tr>
			<td colspan="2" class="pkp_legend_header pkp_helpers_text_center"><p><strong>{translate key="editor.monograph.legend.sectionActions"}</strong> -
			{translate key="editor.monograph.legend.sectionActionsDescription"}</p></td>
		</tr>
		<tr>
			<td><a class="sprite add"></a></td><td>{translate key="editor.monograph.legend.add"}</td>
		</tr>
		<tr>
			<td><a class="sprite add_user"></a></td><td>{translate key="editor.monograph.legend.add_user"}</td>
		</tr>
		<tr>
			<td colspan="2" class="pkp_legend_header pkp_helpers_text_center"><p><strong>{translate key="editor.monograph.legend.itemActions"}</strong> -
				{translate key="editor.monograph.legend.itemActionsDescription"}</p></td>
		</tr>
		<tr>
			<td><a class="sprite settings"></a></td><td>{translate key="editor.monograph.legend.settings"}</td>
		</tr>
		<tr>
			<td><a class="sprite more_info"></a></td><td>{translate key="editor.monograph.legend.more_info"}</td>
		</tr>
		<tr>
			<td><p><a class="sprite notes_none"></a></td><td>{translate key="editor.monograph.legend.notes_none"}</td>
		</tr>
		<tr>
			<td><a class="sprite notes"></a></td><td>{translate key="editor.monograph.legend.notes"}</td>
		</tr>
		<tr>
			<td><a class="sprite notes_new"></a></td><td>{translate key="editor.monograph.legend.notes_new"}</td>
		</tr>
		<tr>
			<td><a class="sprite delete"></a></td><td>{translate key="editor.monograph.legend.delete"}</td>
		</tr>
		<tr>
			<td><a class="sprite edit"></a></td><td>{translate key="editor.monograph.legend.edit"}</td>
		</tr>
		<tr>
			<td><a class="task new"></a></td><td>{translate key="editor.monograph.legend.in_progress"}</td>
		</tr>
		<tr>
			<td><a class="task completed"></a></td><td>{translate key="editor.monograph.legend.complete"}</td>
		</tr>
		<tr>
			<td><a class="task uploaded"></a></td><td>{translate key="editor.monograph.legend.uploaded"}</td>
		</tr>
	</table>
</div>
