// @ts-check
/**
 * @file playwright/tests/review-stage-and-rounds.spec.js
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * OMP suite for the feature spec `docs/product/specs/review-stage-and-rounds.md`
 * (U26 — Review stage & rounds): one test per canonical scenario OMP runs, in
 * OMP's own vocabulary — a PRESS, a MONOGRAPH, and the EXTERNAL REVIEW stage —
 * on the seeded `publicknowledge` press with its own monographs. The spec is
 * written in OJS terms and read through `APP-GLOSSARY.md`; nothing here is a
 * transplant of the OJS suite's wording.
 *
 * Three OMP facts shape every test below:
 *
 * 1. **Two review stages.** The side menu carries a "Review Round 1" under
 *    Internal Review AND under External Review, so a round is selected through
 *    its STAGE (see `PressWorkflowPage`) — the shared POM's stage-blind
 *    `openRound()` is ambiguous here.
 * 2. **Two doors into External Review** (register [OMP2]) — from the Submission
 *    stage and from Internal Review, both labelled "Send to External Review".
 *    Scenario 12 is about exactly that.
 * 3. **Reviewers are split per stage**: `reviewer.julia` / `reviewer.paul` are
 *    External Reviewers and are the only ones assignable here;
 *    `reviewer.amara` / `reviewer.adam` reach Internal Review only.
 *
 * Monographs are filed under the `textbooks` series, whose seeded series editors
 * are `editor.diana` and `sectioneditor.ravi` — which leaves `sectioneditor.omar`
 * unassigned by the submit-time auto-assignment and therefore free to carry
 * scenario 8's "Recommend only" flag. The base press and the seeded accounts are
 * read-only throughout: the suite creates only its own monographs and their
 * files and changes no press setting, so it needs no scratch press.
 *
 * ## What this suite deliberately does NOT cover
 *
 * - **Scenario 13 (OPS)** — a preprint server does not install this feature; the
 *   absence check belongs to the OPS suite.
 * - **The register's 🐞 findings are never asserted as contract** (A1 the
 *   author's upload button vanishing after a resubmit upload, A5 the unrendered
 *   round-status notices, A6 the revisions panel's always-offered Upload, A7 the
 *   keyboard-inaccessible Notifications subjects, A8 released and withheld review
 *   files looking alike). Where a scenario walks through one of those states
 *   (scenario 4's resubmit upload, scenario 11's release checkbox) the test
 *   asserts the rule around it and says nothing about the defect.
 * - **The open ❓ questions get no assertion either way** — including the two
 *   that are OMP's own. [OMP3] (the Internal Review door carrying no files into
 *   the external round) is walked through in scenario 12 without asserting the
 *   empty file list, and [OMP4] (presses shipping no reviewer recommendation
 *   options) is why scenario 6 seeds no `recommendation` and asserts nothing
 *   about a recommendation line in the author's read-review sheet. Both are
 *   parked questions, not coverage gaps: an assertion either way would freeze an
 *   unsettled answer.
 * - **Internal Review as a stage.** It is out of the campaign's scope; it appears
 *   here only as scenario 12's second door and as scenario 9's cancel landing.
 * - **Rules with no canonical scenario**: Rule 9's minimum-confirmed-reviews
 *   prompt (setting owned by U29), Rule 3's "Reviewers Suggested by Author"
 *   panel (U31), Rule 18's return from Copyediting (U32/U34), and the rows of
 *   Rule 6's status table no scenario walks (overdue, pending recommendations,
 *   sent-for-external-review).
 * - **Neighbouring features' mechanics**: the decision wizard itself (U34), the
 *   Reviewers panel's own actions (U27), the reviewer's review (U28), file
 *   mechanics (U36), discussions (U37), stage access (U24). This suite drives
 *   them only as far as a round's own behaviour needs. OMP mounts no "Author
 *   Response" panel at all ([OMP5], U30's feature).
 */

const fs = require('fs');
const os = require('os');
const path = require('path');
const {test, expect} = require('../support/fixtures.js');
const {WorkflowPage} = require('../../lib/pkp/playwright/pages/WorkflowPage.js');

test.use({user: 'editor.diana'});

const PRESS = 'publicknowledge';

/** The series whose seeded editors are editor.diana and sectioneditor.ravi. */
const SERIES = 'textbooks';

/** OMP's counterpart to OJS's "Article Text" genre, as the file wizard labels it. */
const MANUSCRIPT = 'Book Manuscript';

/**
 * The workflow screen, with OMP's two review stages taken into account.
 *
 * The shared POM selects a round by its side-menu label alone, which is
 * unambiguous only where there is one review stage. On OMP a monograph that has
 * been through internal review carries a "Review Round 1" under EACH stage, so
 * every round here is addressed as (stage, round) and the wait keys on the
 * stage's own heading — "Workflow: External Review (Round 1)".
 */
class PressWorkflowPage extends WorkflowPage {
	/**
	 * A workflow stage's side-menu entry. Its accessible name is the stage name
	 * alone even though the node contains its rounds.
	 *
	 * @param {string} stage
	 */
	stageItem(stage) {
		return this.activeModal.getByRole('treeitem', {name: stage, exact: true});
	}

	/**
	 * A round entry nested under its stage.
	 *
	 * @param {number} round
	 * @param {string} [stage]
	 */
	roundItem(round, stage = 'External Review') {
		return this.stageItem(stage).getByRole('treeitem', {
			name: `Review Round ${round}`,
			exact: true,
		});
	}

	/**
	 * @param {number} round
	 * @param {string} [stage]
	 */
	async openRound(round, stage = 'External Review') {
		await this.roundItem(round, stage).click();
		await expect(this.workflowHeading).toHaveText(
			new RegExp(`${stage} \\(Round ${round}\\)`),
		);
	}
}

/** Per-app, per-worker, per-run tag: one hyphenless alphanumeric token. */
function tagFor(name, testInfo) {
	return `u26omp${name}w${testInfo.parallelIndex}${Math.random().toString(36).slice(2, 7)}`;
}

/** The seeded roster's addresses follow the username. */
function emailFor(username) {
	return `${username}@example.org`;
}

/** A throwaway file for an upload, named after the test's tag so the row is attributable. */
function revisionFile(tag) {
	const file = path.join(os.tmpdir(), `${tag}.pdf`);

	fs.writeFileSync(
		file,
		'%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF\n',
	);

	return file;
}

/**
 * A wizard-step check for a decision's file step: it lists exactly the files
 * expected and offers every one of them already ticked. Steps carrying no file
 * list (the email steps) are passed over, so the returned checker counts the file
 * steps it saw — the caller asserts on that count rather than trusting a check
 * that may never have run.
 *
 * @param {number} expected
 */
function offeredFilesKept(expected) {
	const seen = {fileSteps: 0};

	seen.check = async (decisionPage) => {
		const offered = decisionPage.locator('input[name^="promoteFile"]:visible');

		if (!(await offered.count())) {
			return;
		}

		seen.fileSteps++;
		await expect(offered).toHaveCount(expected);

		for (const box of await offered.all()) {
			await expect(box).toBeChecked();
		}
	};

	return seen;
}

/** A monograph of the seeded author in the series scenario 8's recommender is free of. */
function monographSpec(tag, extra = {}) {
	return {
		tag,
		context: PRESS,
		series: SERIES,
		submitter: 'author.alex',
		...extra,
	};
}

test.describe('External Review stage & rounds', () => {
	test('scenario 1 — round 1 opens with the chosen files', async ({page, ompApi}, testInfo) => {
		const tag = tagFor('s1', testInfo);
		const monograph = await ompApi.createSubmission(monographSpec(tag));

		const workflow = new PressWorkflowPage(page, PRESS);
		await workflow.gotoEditorial(monograph.submissionId);

		// The Submission stage's door into External Review; its file step offers
		// the monograph's files pre-ticked, which the editor keeps as offered.
		const offered = offeredFilesKept(1);
		await workflow.recordDecision('Send to External Review', {onStep: offered.check});
		expect(offered.fileSteps).toBe(1);

		await expect(workflow.roundItem(1)).toBeVisible();

		await workflow.openRound(1);
		await expect(workflow.workflowHeading).toHaveText(
			/Workflow:\s+External Review\s+\(Round 1\)/,
		);
		await expect(workflow.statusHeading('Round 1 Status')).toBeVisible();
		await expect(workflow.statusLines('Round 1 Status')).toHaveText([
			'Waiting for reviewers to be assigned.',
		]);

		await expect(
			workflow.panelTable('Files for Review').getByRole('link', {name: 'article.pdf'}),
		).toBeVisible();
		await expect(workflow.panelTable('Revisions Uploaded')).toContainText('No Items');
	});

	test('scenario 2 — the round status follows reviewer activity', async ({
		page,
		ompApi,
	}, testInfo) => {
		const tag = tagFor('s2', testInfo);

		// One round per state the scenario walks: the status line is computed live
		// from the round's reviewer activity, so each state is its own round.
		const [awaiting, submitted, confirmed] = await Promise.all(
			['accepted', 'completed', 'confirmed'].map((status, index) =>
				ompApi.createSubmission(
					monographSpec(`${tag}r${index}`, {
						decisions: [{decision: 'skipInternalReview'}],
						reviewRounds: [{reviewers: [{user: 'reviewer.julia', status}]}],
					}),
				),
			),
		);

		const workflow = new PressWorkflowPage(page, PRESS);

		for (const [monograph, expected] of [
			[awaiting, 'Awaiting responses from reviewers.'],
			[submitted, 'New reviews have been submitted.'],
			[confirmed, 'All reviews are confirmed and a decision is needed.'],
		]) {
			await workflow.gotoEditorial(monograph.submissionId);
			await workflow.openRound(1);
			await expect(workflow.statusLines('Round 1 Status')).toHaveText([expected]);
		}
	});

	test('scenario 3 — revisions round-trip inside the round', async ({
		page,
		ompApi,
		asUser,
		pkpMail,
	}, testInfo) => {
		const tag = tagFor('s3', testInfo);
		const monograph = await ompApi.createSubmission(
			monographSpec(tag, {
				decisions: [{decision: 'skipInternalReview'}, {decision: 'requestRevisions'}],
			}),
		);

		const editorView = new PressWorkflowPage(page, PRESS);
		await editorView.gotoEditorial(monograph.submissionId);
		await editorView.openRound(1);
		await expect(editorView.statusLines('Round 1 Status')).toHaveText([
			'Revisions have been requested.',
		]);

		const authorPage = await (await asUser('author.alex')).newPage();
		const authorView = new PressWorkflowPage(authorPage, PRESS);
		await authorView.gotoAuthor(monograph.submissionId);
		await authorView.openRound(1);
		await expect(authorView.statusLines('Round 1 Status')).toHaveText([
			'Revisions have been requested.',
		]);

		await expect(authorView.action('Upload revisions')).toBeVisible();
		await authorView.action('Upload revisions').click();
		await authorView.completeFileWizard(revisionFile(tag), {component: MANUSCRIPT});

		await expect(authorView.statusLines('Round 1 Status')).toHaveText([
			'Revisions have been submitted and a decision is needed.',
		]);
		await expect(
			authorView.panelTable('Revisions Uploaded').getByRole('link', {name: `${tag}.pdf`}),
		).toBeVisible();

		// The same round, read by the editor: one status, both views.
		await editorView.gotoEditorial(monograph.submissionId);
		await editorView.openRound(1);
		await expect(editorView.statusLines('Round 1 Status')).toHaveText([
			'Revisions have been submitted and a decision is needed.',
		]);
		await expect(
			editorView.panelTable('Revisions Uploaded').getByRole('link', {name: `${tag}.pdf`}),
		).toBeVisible();

		// One "Revised Version Uploaded" email, addressed to the stage's editors
		// together rather than one message each.
		const [message] = await pkpMail.find({
			to: emailFor('editor.diana'),
			contains: tag,
			subject: 'Revised Version Uploaded',
		});
		const recipients = (message.To ?? []).map((entry) => entry.Address);

		expect(recipients).toContain(emailFor('editor.diana'));
		expect(recipients).toContain(emailFor('sectioneditor.ravi'));
	});

	test('scenario 4 — a resubmit request leads to a new review round', async ({
		page,
		ompApi,
		asUser,
	}, testInfo) => {
		const tag = tagFor('s4', testInfo);
		const monograph = await ompApi.createSubmission(
			monographSpec(tag, {
				decisions: [{decision: 'skipInternalReview'}, {decision: 'resubmit'}],
			}),
		);

		const editorView = new PressWorkflowPage(page, PRESS);
		await editorView.gotoEditorial(monograph.submissionId);
		await editorView.openRound(1);
		await expect(editorView.statusLines('Round 1 Status')).toHaveText([
			'Revisions requested from the author to be taken to a new review round.',
		]);

		const authorPage = await (await asUser('author.alex')).newPage();
		const authorView = new PressWorkflowPage(authorPage, PRESS);
		await authorView.gotoAuthor(monograph.submissionId);
		await authorView.openRound(1);
		await authorView.action('Upload revisions').click();
		await authorView.completeFileWizard(revisionFile(tag), {component: MANUSCRIPT});

		await expect(authorView.statusLines('Round 1 Status')).toHaveText([
			'Revisions submitted. A new review round needs to be created.',
		]);

		// The new round carries the uploaded revision over as its review file.
		await editorView.gotoEditorial(monograph.submissionId);
		await editorView.openRound(1);

		const carried = offeredFilesKept(1);
		await editorView.recordDecision('Create New Review Round', {onStep: carried.check});
		expect(carried.fileSteps).toBe(1);

		await expect(editorView.roundItem(2)).toBeVisible();
		await expect(editorView.workflowHeading).toHaveText(
			/Workflow:\s+External Review\s+\(Round 2\)/,
		);
		await expect(editorView.statusLines('Round 2 Status')).toHaveText([
			'Waiting for reviewers to be assigned.',
		]);
		await expect(
			editorView.panelTable('Files for Review').getByRole('link', {name: `${tag}.pdf`}),
		).toBeVisible();
	});

	test('scenario 5 — an earlier round is read-only', async ({page, ompApi}, testInfo) => {
		const tag = tagFor('s5', testInfo);
		const monograph = await ompApi.createSubmission(
			monographSpec(tag, {
				decisions: [
					{decision: 'skipInternalReview'},
					{decision: 'newExternalReviewRound'},
				],
				reviewRounds: [
					{reviewers: [{user: 'reviewer.julia'}]},
					{reviewers: [{user: 'reviewer.paul'}]},
				],
			}),
		);

		const workflow = new PressWorkflowPage(page, PRESS);
		await workflow.gotoEditorial(monograph.submissionId);

		// Positive control: the latest round does offer the decisions.
		await workflow.openRound(2);
		await expect(workflow.action('Request Revisions')).toBeVisible();
		await expect(workflow.action('Accept Submission')).toBeVisible();
		await expect(workflow.action('Create New Review Round')).toBeVisible();

		await workflow.openRound(1);
		await expect(workflow.statusHeading('Status')).toBeVisible();
		await expect(workflow.statusLines('Status')).toHaveText([
			'The submission has been advanced to the next round of review',
		]);
		await expect(workflow.statusHeading('Round 1 Status')).toHaveCount(0);
		await expect(workflow.actionItems.getByRole('button')).toHaveCount(0);

		// The round still shows its own files and reviewers, with their controls.
		await expect(workflow.panelTable('Reviewers')).toContainText('Julia Reviewer');
		await expect(workflow.panelTable('Reviewers')).not.toContainText('Paul Reviewer');
		await expect(
			workflow.activeModal.getByRole('button', {name: 'Upload/Select Files', exact: true}),
		).toBeVisible();
		await expect(
			workflow.activeModal.getByRole('button', {name: 'Add Reviewer', exact: true}),
		).toBeVisible();
	});

	test('scenario 6 — the author reads an open review', async ({
		page,
		ompApi,
		asUser,
	}, testInfo) => {
		const tag = tagFor('s6', testInfo);
		const shared = `Shared with the author ${tag}`;
		const editorOnly = `For the editor alone ${tag}`;

		// No `recommendation` is seeded: a press ships no reviewer recommendation
		// options at all ([OMP4], an open question), so the key throws here and the
		// sheet's recommendation line is not this suite's to assert either way.
		const [open, anonymous] = await Promise.all([
			ompApi.createSubmission(
				monographSpec(`${tag}o`, {
					decisions: [{decision: 'skipInternalReview'}],
					reviewRounds: [
						{
							reviewers: [
								{
									user: 'reviewer.julia',
									status: 'completed',
									method: 'open',
									commentsForAuthor: shared,
									commentsForEditor: editorOnly,
									attachment: true,
								},
							],
						},
					],
				}),
			),
			ompApi.createSubmission(
				monographSpec(`${tag}a`, {
					decisions: [{decision: 'skipInternalReview'}],
					reviewRounds: [
						{
							reviewers: [
								{
									user: 'reviewer.julia',
									status: 'completed',
									method: 'doubleAnonymous',
									commentsForAuthor: shared,
								},
							],
						},
					],
				}),
			),
		]);

		const authorPage = await (await asUser('author.alex')).newPage();
		const authorView = new PressWorkflowPage(authorPage, PRESS);

		await authorView.gotoAuthor(open.submissionId);
		await authorView.openRound(1);
		await expect(authorView.panel('Reviewers')).toBeVisible();
		await expect(authorView.panelTable('Reviewers')).toContainText('Julia Reviewer');

		await authorView.panelTable('Reviewers').getByRole('button', {name: 'Read Review'}).click();

		const review = authorView.activeModal;
		await expect(review.getByRole('heading', {name: /^Review:/})).toBeVisible({timeout: 30_000});
		await expect(review).toContainText('Julia Reviewer');
		await expect(review).toContainText('Completed:');
		await expect(review).toContainText(shared);
		await expect(review).toContainText('reviewer-attachment.pdf');
		await expect(review).not.toContainText(editorOnly);

		// The same review run anonymously never reaches the author's round at all;
		// the round's other panels bound the absence.
		await authorView.gotoAuthor(anonymous.submissionId);
		await authorView.openRound(1);
		await expect(authorView.statusLines('Round 1 Status')).toHaveText([
			'New reviews have been submitted.',
		]);
		await expect(authorView.panel('Revisions Uploaded')).toBeVisible();
		await expect(authorView.panel('Reviewers')).toHaveCount(0);
	});

	test("scenario 7 — the author follows the editor's messages", async ({
		page,
		ompApi,
		asUser,
	}, testInfo) => {
		const tag = tagFor('s7', testInfo);
		const monograph = await ompApi.createSubmission(
			monographSpec(tag, {decisions: [{decision: 'skipInternalReview'}]}),
		);

		const authorPage = await (await asUser('author.alex')).newPage();
		const authorView = new PressWorkflowPage(authorPage, PRESS);
		await authorView.gotoAuthor(monograph.submissionId);
		await authorView.openRound(1);

		// Before any decision email: no panel. Bounded by the round's own render.
		await expect(authorView.statusHeading('Round 1 Status')).toBeVisible();
		await expect(authorView.panel('Notifications')).toHaveCount(0);

		const editorView = new PressWorkflowPage(page, PRESS);
		await editorView.gotoEditorial(monograph.submissionId);
		await editorView.openRound(1);
		await editorView.recordDecision('Request Revisions', {revisions: 'stayInRound'});

		await authorView.gotoAuthor(monograph.submissionId);
		await authorView.openRound(1);
		await expect(authorView.panel('Notifications')).toBeVisible();

		// Each message is one row: its subject, and the date it was sent.
		const messages = authorView.panel('Notifications').locator('xpath=following-sibling::ul/li');
		await expect(messages).toHaveCount(1);

		const subject = (await messages.first().locator('a').innerText()).trim();
		expect(subject.length).toBeGreaterThan(0);
		await expect(messages.first()).toContainText(new Date().getFullYear().toString());

		await messages.first().locator('a').click();
		await expect(authorPage.locator('[data-cy="active-modal"]').last()).toContainText(subject, {
			timeout: 30_000,
		});
	});

	test('scenario 8 — recommendations reach the deciding editor', async ({
		ompApi,
		asUser,
	}, testInfo) => {
		const tag = tagFor('s8', testInfo);
		const monograph = await ompApi.createSubmission(
			monographSpec(tag, {
				// A Series Editor of another series, so the submit-time
				// auto-assignment leaves him to this test.
				participants: [
					{user: 'sectioneditor.omar', role: 'sectionEditor', recommendOnly: true},
				],
				decisions: [{decision: 'skipInternalReview'}],
			}),
		);

		const recommenderPage = await (await asUser('sectioneditor.omar')).newPage();
		const recommender = new PressWorkflowPage(recommenderPage, PRESS);
		await recommender.gotoEditorial(monograph.submissionId);
		await recommender.openRound(1);

		await expect(recommender.action('Recommend Revisions')).toBeVisible();
		await expect(recommender.action('Recommend Accept')).toBeVisible();
		await expect(recommender.action('Recommend Decline')).toBeVisible();
		await expect(recommender.action('Accept Submission')).toHaveCount(0);

		// No side-column listing for the recommender; the side column is there.
		await expect(
			recommender.secondaryItems.getByRole('heading', {name: 'Participants'}),
		).toBeVisible();
		await expect(
			recommender.secondaryItems.getByRole('heading', {name: 'Recommendation'}),
		).toHaveCount(0);

		await recommender.recordDecision('Recommend Revisions', {revisions: 'stayInRound'});
		await recommender.openRound(1);

		await expect(
			recommender.actionItems.getByRole('heading', {name: 'Recommendation'}),
		).toBeVisible();
		await expect(recommender.action('Change decision')).toBeVisible();
		await expect(
			recommender.secondaryItems.getByRole('heading', {name: 'Recommendation'}),
		).toHaveCount(0);

		const managerPage = await (await asUser('manager.maya')).newPage();
		const manager = new PressWorkflowPage(managerPage, PRESS);
		await manager.gotoEditorial(monograph.submissionId);
		await manager.openRound(1);

		await expect(
			manager.secondaryItems.getByRole('heading', {name: 'Recommendation'}),
		).toBeVisible();
		await expect(manager.secondaryItems).toContainText('Request Revisions');
		await expect(manager.statusLines('Round 1 Status')).toHaveText([
			'All recommendations are in and a decision is needed.',
		]);
	});

	test('scenario 9 — a fresh round can be cancelled', async ({page, ompApi}, testInfo) => {
		const tag = tagFor('s9', testInfo);

		const [fresh, answered, afterInternal] = await Promise.all([
			ompApi.createSubmission(
				monographSpec(`${tag}f`, {
					decisions: [
						{decision: 'skipInternalReview'},
						{decision: 'newExternalReviewRound'},
					],
					reviewRounds: [
						{reviewers: [{user: 'reviewer.julia'}]},
						{reviewers: [{user: 'reviewer.paul', status: 'invited'}]},
					],
				}),
			),
			ompApi.createSubmission(
				monographSpec(`${tag}a`, {
					decisions: [{decision: 'skipInternalReview'}],
					reviewRounds: [{reviewers: [{user: 'reviewer.paul', status: 'accepted'}]}],
				}),
			),
			// The same cancel, on the external Round 1 of a monograph that came
			// through Internal Review — where the cascade lands it ([OMP1]).
			ompApi.createSubmission(
				monographSpec(`${tag}i`, {
					reviewRounds: [
						{stage: 'internal', reviewers: [{user: 'reviewer.amara'}]},
						{stage: 'external', reviewers: [{user: 'reviewer.julia'}]},
					],
				}),
			),
		]);

		const workflow = new PressWorkflowPage(page, PRESS);
		await workflow.gotoEditorial(fresh.submissionId);
		await workflow.openRound(2);
		await expect(workflow.action('Cancel Review Round')).toBeVisible();

		await workflow.recordDecision('Cancel Review Round');

		await expect(workflow.roundItem(2)).toHaveCount(0);
		await expect(workflow.roundItem(1)).toBeVisible();
		await expect(workflow.workflowHeading).toHaveText(
			/Workflow:\s+External Review\s+\(Round 1\)/,
		);
		await expect(workflow.statusHeading('Round 1 Status')).toBeVisible();

		// Once the invitation has been answered the decision is no longer offered;
		// the round's other decisions bound the absence.
		await workflow.gotoEditorial(answered.submissionId);
		await workflow.openRound(1);
		await expect(workflow.action('Accept Submission')).toBeVisible();
		await expect(workflow.action('Cancel Review Round')).toHaveCount(0);

		// Cancelling external Round 1 hands the monograph back to Internal Review
		// when internal rounds exist — OMP's own landing, where OJS has none.
		await workflow.gotoEditorial(afterInternal.submissionId);
		await workflow.openRound(1);
		await workflow.recordDecision('Cancel Review Round');

		await expect(workflow.roundItem(1)).toHaveCount(0);
		await expect(workflow.roundItem(1, 'Internal Review')).toBeVisible();
		await expect(workflow.workflowHeading).toHaveText(
			/Workflow:\s+Internal Review\s+\(Round 1\)/,
		);
	});

	test('scenario 10 — decline and revert in review', async ({ompApi, asUser}, testInfo) => {
		const tag = tagFor('s10', testInfo);
		const monograph = await ompApi.createSubmission(
			monographSpec(tag, {decisions: [{decision: 'skipInternalReview'}]}),
		);

		const seriesEditorPage = await (await asUser('sectioneditor.ravi')).newPage();
		const seriesEditor = new PressWorkflowPage(seriesEditorPage, PRESS);
		await seriesEditor.gotoEditorial(monograph.submissionId);
		await seriesEditor.openRound(1);
		await seriesEditor.recordDecision('Decline Submission');
		await seriesEditor.openRound(1);

		await expect(seriesEditor.statusLines('Round 1 Status')).toHaveText([
			'Submission declined.',
		]);
		await expect(seriesEditor.action('Revert Decline')).toBeVisible();
		await expect(seriesEditor.action('Accept Submission')).toHaveCount(0);
		await expect(seriesEditor.action('Request Revisions')).toHaveCount(0);
		// A Series Editor is never offered Delete; Revert Decline is the control.
		await expect(seriesEditor.action('Delete')).toHaveCount(0);

		const managerPage = await (await asUser('manager.maya')).newPage();
		const manager = new PressWorkflowPage(managerPage, PRESS);
		await manager.gotoEditorial(monograph.submissionId);
		await manager.openRound(1);
		await expect(manager.action('Revert Decline')).toBeVisible();
		await expect(manager.action('Delete')).toBeVisible();

		await seriesEditor.recordDecision('Revert Decline');
		await seriesEditor.openRound(1);
		await expect(seriesEditor.statusLines('Round 1 Status')).toHaveText([
			'Waiting for reviewers to be assigned.',
		]);
		await expect(seriesEditor.action('Accept Submission')).toBeVisible();
	});

	test('scenario 11 — curating Files for Review mid-round', async ({
		page,
		ompApi,
	}, testInfo) => {
		const tag = tagFor('s11', testInfo);
		const monograph = await ompApi.createSubmission(monographSpec(tag));

		// Opened through the real decision so the round starts with a review file.
		const workflow = new PressWorkflowPage(page, PRESS);
		await workflow.gotoEditorial(monograph.submissionId);
		await workflow.recordDecision('Send to External Review');
		await workflow.openRound(1);

		const openSelector = async () => {
			await workflow.activeModal
				.getByRole('button', {name: 'Upload/Select Files', exact: true})
				.click();
			const selector = workflow.activeModal;
			await expect(
				selector.getByRole('heading', {name: 'Current Review Files For Round 1'}),
			).toBeVisible({timeout: 30_000});
			await expect(selector.locator('input[name="selectedFiles[]"]').first()).toBeAttached({
				timeout: 30_000,
			});

			return selector;
		};
		const fileBoxes = (selector) => selector.locator('input[name="selectedFiles[]"]');
		const fileIds = (selector) =>
			fileBoxes(selector).evaluateAll((boxes) => boxes.map((box) => box.id));
		const save = async (selector) => {
			await selector.getByRole('button', {name: 'OK', exact: true}).click();
			await expect(
				workflow.page.getByRole('heading', {name: 'Current Review Files For Round 1'}),
			).toHaveCount(0, {timeout: 30_000});
			await workflow.waitForOpen();
		};

		// By default the modal lists the round's own review files only.
		let selector = await openSelector();
		await expect(fileBoxes(selector)).toHaveCount(1);
		const roundFiles = await fileIds(selector);

		// The toggle reaches the monograph's other files; ticking one copies it in.
		await selector.locator('input[name="allStages"]').check();
		await expect(fileBoxes(selector)).toHaveCount(2, {timeout: 30_000});

		const [otherStageFile] = (await fileIds(selector)).filter(
			(id) => !roundFiles.includes(id),
		);
		await selector.locator(`#${otherStageFile}`).check();
		await save(selector);

		await expect(workflow.panelTable('Files for Review').locator('tbody tr')).toHaveCount(2);

		// The panel now lists a COPY of it — a review file of its own, which
		// arrives not yet released to reviewers.
		selector = await openSelector();
		await expect(fileBoxes(selector)).toHaveCount(2);

		const [copy] = (await fileIds(selector)).filter((id) => !roundFiles.includes(id));
		expect(copy).not.toBe(otherStageFile);
		await expect(selector.locator(`#${copy}`)).not.toBeChecked();

		// Ticking it releases it to the reviewers.
		await selector.locator(`#${copy}`).check();
		await save(selector);

		selector = await openSelector();
		await expect(selector.locator(`#${copy}`)).toBeChecked();

		// Unticking withdraws it from the reviewers without deleting anything:
		// the panel and the modal both still list the file.
		await selector.locator(`#${copy}`).uncheck();
		await save(selector);

		await expect(workflow.panelTable('Files for Review').locator('tbody tr')).toHaveCount(2);
		selector = await openSelector();
		await expect(fileBoxes(selector)).toHaveCount(2);
		await expect(selector.locator(`#${copy}`)).not.toBeChecked();
	});

	test('scenario 12 — the two doors into External Review', async ({page, ompApi}, testInfo) => {
		const tag = tagFor('s12', testInfo);

		const [fromSubmission, fromInternal] = await Promise.all([
			ompApi.createSubmission(monographSpec(`${tag}s`)),
			ompApi.createSubmission(
				monographSpec(`${tag}i`, {
					reviewRounds: [{stage: 'internal', reviewers: [{user: 'reviewer.amara'}]}],
				}),
			),
		]);

		const workflow = new PressWorkflowPage(page, PRESS);

		// Door 1 — straight from the Submission stage, where it stands beside the
		// separate decision that goes to Internal Review instead.
		await workflow.gotoEditorial(fromSubmission.submissionId);
		await expect(workflow.action('Send to External Review')).toBeVisible();
		await expect(workflow.action('Send to Internal Review')).toBeVisible();
		await expect(workflow.roundItem(1)).toHaveCount(0);

		// This door carries the monograph's own files, offered pre-ticked.
		const offered = offeredFilesKept(1);
		await workflow.recordDecision('Send to External Review', {onStep: offered.check});
		expect(offered.fileSteps).toBe(1);

		await workflow.openRound(1);
		await expect(workflow.statusLines('Round 1 Status')).toHaveText([
			'Waiting for reviewers to be assigned.',
		]);
		await expect(
			workflow.panelTable('Files for Review').getByRole('link', {name: 'article.pdf'}),
		).toBeVisible();

		// Door 2 — the same label, offered on the Internal Review stage instead;
		// the two doors are told apart only by the stage they appear on. (What
		// this door does about files is register question [OMP3] and is not
		// asserted here.)
		await workflow.gotoEditorial(fromInternal.submissionId);
		await workflow.openRound(1, 'Internal Review');
		await expect(workflow.action('Send to External Review')).toBeVisible();
		await expect(workflow.roundItem(1)).toHaveCount(0);

		await workflow.recordDecision('Send to External Review');

		// Either way, External Review Round 1 opens the same: same heading, same
		// starting status, same panel roster and the same decisions on offer, so
		// every common scenario above runs from here unchanged.
		await workflow.openRound(1);
		await expect(workflow.workflowHeading).toHaveText(
			/Workflow:\s+External Review\s+\(Round 1\)/,
		);
		await expect(workflow.statusLines('Round 1 Status')).toHaveText([
			'Waiting for reviewers to be assigned.',
		]);
		await expect(workflow.panel('Files for Review')).toBeVisible();
		await expect(workflow.panel('Revisions Uploaded')).toBeVisible();
		await expect(workflow.panel('Reviewers')).toBeVisible();
		await expect(workflow.action('Request Revisions')).toBeVisible();
		await expect(workflow.action('Accept Submission')).toBeVisible();
		await expect(workflow.action('Create New Review Round')).toBeVisible();
		await expect(workflow.action('Cancel Review Round')).toBeVisible();
		await expect(workflow.action('Decline Submission')).toBeVisible();
	});
});
