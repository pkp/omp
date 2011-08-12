{**
 * reviewRoundStatus.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display the review round status.
 *
 * Parameters:
 *  round: The review round.
 *  roundStatus: A translation key representing the
 *   review round status.
 *}
<div id="roundStatus" class="pkp_common_reviewRoundStatusContainer">
	<p>{translate key="editor.monograph.roundStatus" round=$round}: {translate key="$roundStatus"}</p>
</div>