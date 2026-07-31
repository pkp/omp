// @ts-check
/**
 * @file playwright/tests/review-stage-and-rounds.spec.js
 *
 * U26 — Review stage & rounds, OMP suite (spec:
 * lib/pkp/docs/product/specs/review-stage-and-rounds.md). One test per
 * canonical scenario the spec runs on a press: common scenarios 1–12 in OMP
 * vocabulary (press, monograph, External Review — glossary substitution)
 * plus OMP-specific scenario 13 (skip-internal entry). Scenario 14 is
 * OPS-only. On a press the entry into External Review used throughout is the
 * Submission-stage "Send to External Review" decision (skip-internal,
 * OMP1); the Internal Review STAGE itself is out of scope by charter and no
 * test here touches its machinery.
 *
 * Deliberate omissions (register IDs from the spec's Findings register):
 * - A1 (bug): after the author's first upload on the resubmit path, the
 *   bottom "Upload revisions" button vanishes and the resubmit task
 *   lingers — neither is asserted. Scenario 5 asserts the documented
 *   working path instead: the Revisions Uploaded panel's own "Upload"
 *   control still opens the wizard.
 * - OJS1 (bug, OJS-only): on the press the read-review window's shared
 *   remarks DO render — scenario 12 asserts that text as the working path.
 * - A2 (open): round-status sentences are asserted on the EDITORIAL view
 *   only; no test asserts what wording the author's status box carries.
 * - A3 (open, tests-must-not-assert): nothing is asserted about the
 *   read-review window's attachments section.
 * - A4 (open): no test drives a round whose only reviewers declined into a
 *   status assertion (scenario 11's declined reviewer is a round RECORD,
 *   asserted via the recommendation sentences, not the reviewer ones).
 * - A5/A6/A7 (open): assistant access paths, the restored round's status
 *   after a cancel, and review-file checkbox mirroring are not asserted
 *   either way.
 * - OMP2 (open): no assertion on reviewer-recommendation contents anywhere
 *   on the press — the read-review window is asserted without any
 *   recommendation-line claim.
 * - Round-status sentences quoted here are the editor wording of Rule 5;
 *   the "highlighted" styling of Accept Submission (Rule 11) is not
 *   asserted.
 *
 * Seeding: scenario endpoints only; scratch submissions ride the read-only
 * `publicknowledge` press (series `monographs` auto-assigns the seeded
 * deciding editors on submit); scenario 4 uses a scratch press with
 * throwaway users because its mail assertion needs a unique throwaway
 * recipient (Mailpit is shared across fleets — never cleared, every mail
 * claim scoped by recipient address naming app + test).
 */
const {test, expect} = require('../support/fixtures.js');
const {
    STATUS,
    DECISIONS,
    workflowModal,
    topModal,
    primaryRegion,
    actionsRegion,
    secondaryRegion,
    decisionButton,
    openEditorial,
    openAuthorView,
    expectRoundStatus,
    expectPlainStatus,
    awaitComposerReady,
    walkDecisionWizard,
    requestRevisions,
    completeReviewAsReviewer,
    confirmReviewAsEditor,
    completeUploadWizard,
    assignParticipant,
    openTasksPanel,
} = require('../pages/ReviewStagePages.js');

const PK = 'publicknowledge';

/** Parallel-safe unique tag: single alphanumeric token, ≤32 chars. */
function makeTag(testInfo, scenarioKey) {
    const rand = Math.random().toString(36).replace(/[^a-z0-9]/g, '').slice(0, 6);
    return `${scenarioKey}ompw${testInfo.parallelIndex}${rand}`;
}

/**
 * Seed a monograph on publicknowledge in series `monographs` (submit-time
 * auto-assignment enrols the seeded deciding editors — spec footnote s
 * caveat holds only on scratch presses).
 */
async function seedMonograph(ompApi, tag, {decisions = [], rounds = null, submitter = 'author.alex'} = {}) {
    const spec = {
        tag,
        context: PK,
        submitter,
        series: 'monographs',
    };
    if (decisions.length) {
        spec.decisions = decisions;
    }
    if (rounds) {
        spec.reviewRounds = rounds;
    }
    return ompApi.createSubmission(spec);
}

/** Seed straight into External Review round 1 (skip-internal entry). */
async function seedInExternalReview(ompApi, tag, {reviewers = [], extraRounds = [], extraDecisions = []} = {}) {
    return seedMonograph(ompApi, tag, {
        decisions: ['skipInternalReview', ...extraDecisions],
        rounds: [{stage: 'external', reviewers}, ...extraRounds],
    });
}

test.describe('Review stage & rounds (U26)', () => {
    test.beforeEach(async ({}, testInfo) => testInfo.setTimeout(300_000));

    test('S1: Round 1 opens with the submission', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u26s1');
        const fileName = `ms-${tag}.txt`;
        const seeded = await seedMonograph(ompApi, tag);

        const page = await (await asUser('manager.maya')).newPage();
        const modal = await openEditorial(page, PK, seeded.submissionId);

        // Give the wizard a submission file to carry (the seed carries none).
        await primaryRegion(modal)
            .getByRole('button', {name: 'Upload', exact: true})
            .first()
            .click();
        await completeUploadWizard(page, fileName);

        // Record the decision that sends the monograph to (external) review.
        await decisionButton(modal, 'Send to External Review').click();
        await expect(
            page.getByRole('heading', {name: /Send to External Review: Notify Authors/})
        ).toBeVisible({timeout: 15_000});
        await awaitComposerReady(page);
        await page.getByRole('button', {name: 'Continue', exact: true}).click();
        await expect(
            page.getByRole('heading', {name: 'Select Files', exact: true})
        ).toBeVisible({timeout: 15_000});
        // Choose the file reviewers will see.
        const fileCheckbox = page.getByRole('checkbox', {name: new RegExp(fileName)});
        if (!(await fileCheckbox.isChecked())) {
            await fileCheckbox.check();
        }
        await page.getByRole('button', {name: /Record (Editorial )?Decision/}).click();
        await expect(page.getByText('View Submission Summary')).toBeVisible({
            timeout: 30_000,
        });

        // The stage opens on Review Round 1 with the round furniture.
        const modal2 = await openEditorial(page, PK, seeded.submissionId);
        await expect(
            modal2.getByRole('heading', {name: 'Workflow: External Review (Round 1)'})
        ).toBeVisible();
        await expect(modal2.getByText('Review Round 1', {exact: true}).first()).toBeVisible();
        await expectRoundStatus(modal2, 1, STATUS.waiting);
        // The Files for Review panel lists the file chosen when sending.
        await expect(
            primaryRegion(modal2).getByText(fileName).first()
        ).toBeVisible();
    });

    test('S2: the status line follows the reviewers', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u26s2');
        const seeded = await seedInExternalReview(ompApi, tag, {
            reviewers: [{username: 'reviewer.julia', status: 'invited'}],
        });

        // With a reviewer on the round, the box awaits their responses.
        const page = await (await asUser('manager.maya')).newPage();
        let modal = await openEditorial(page, PK, seeded.submissionId);
        await expectRoundStatus(modal, 1, STATUS.awaitingResponses);

        // The reviewer accepts and submits their review.
        const juliaPage = await (await asUser('reviewer.julia')).newPage();
        await completeReviewAsReviewer(
            juliaPage,
            PK,
            seeded.submissionId,
            `Review remarks ${tag}.`
        );

        modal = await openEditorial(page, PK, seeded.submissionId);
        await expectRoundStatus(modal, 1, STATUS.newReviews);

        // The editor confirms the review from the Reviewers panel.
        await confirmReviewAsEditor(page, modal, 'Julia Reviewer');
        modal = await openEditorial(page, PK, seeded.submissionId);
        await expectRoundStatus(modal, 1, STATUS.reviewsConfirmed);
    });

    test('S3: request revisions within the round', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u26s3');
        const seeded = await seedInExternalReview(ompApi, tag);

        const page = await (await asUser('manager.maya')).newPage();
        const modal = await openEditorial(page, PK, seeded.submissionId);
        await requestRevisions(page, modal, {newRound: false});

        const modal2 = await openEditorial(page, PK, seeded.submissionId);
        await expectRoundStatus(modal2, 1, STATUS.revisionsRequested);

        // The author's task list holds a revisions task for this submission…
        const authorPage = await (await asUser('author.alex')).newPage();
        await authorPage.goto(`/index.php/${PK}/en/dashboard/mySubmissions`);
        const tasks = await openTasksPanel(authorPage);
        await expect(tasks.getByText(`Submission ${tag}`)).toBeVisible();
        await authorPage.keyboard.press('Escape');

        // …and their review stage offers the bottom "Upload revisions" button.
        const authorModal = await openAuthorView(authorPage, PK, seeded.submissionId);
        await expect(
            authorModal.getByRole('button', {name: 'Upload revisions'})
        ).toBeVisible();
    });

    test('S4: author uploads a revision', async ({ompApi, asUser, pkpMail}, testInfo) => {
        const tag = makeTag(testInfo, 'u26s4');
        const fileName = `rev-${tag}.txt`;
        const manager = `mgr${tag}`;
        const editor = `ed${tag}`;
        const author = `au${tag}`;
        const editorEmail = `${tag}ed@mail.test`;

        // Scratch press: the revised-version notice must land in a unique
        // throwaway mailbox (the roster's addresses are shared).
        await ompApi.createContext({
            tag,
            users: [
                {username: manager, roles: ['manager'], givenName: `Mgr${tag}`, familyName: 'Manager'},
                {username: editor, roles: ['sectionEditor'], givenName: `Ed${tag}`, familyName: 'Editor', email: editorEmail},
                {username: author, roles: ['author'], givenName: `Au${tag}`, familyName: 'Author', email: `${tag}au@mail.test`},
            ],
        });
        const seeded = await ompApi.createSubmission({
            tag,
            context: tag,
            submitter: author,
            decisions: ['skipInternalReview', 'requestRevisions'],
            reviewRounds: [{stage: 'external'}],
        });

        // Scratch presses auto-assign no editor on submit (spec footnote s):
        // assign the throwaway editor to the stage through the screens.
        const mgrPage = await (await asUser(manager)).newPage();
        const mgrModal = await openEditorial(mgrPage, tag, seeded.submissionId);
        await assignParticipant(mgrPage, mgrModal, {
            group: 'Series editor',
            query: `Ed${tag}`,
            resultName: `Ed${tag} Editor`,
        });

        // The author holds a revisions task (control for its later removal).
        const authorPage = await (await asUser(author)).newPage();
        await authorPage.goto(`/index.php/${tag}/en/dashboard/mySubmissions`);
        const tasksBefore = await openTasksPanel(authorPage);
        await expect(tasksBefore.getByText(`Submission ${tag}`)).toBeVisible();
        await authorPage.keyboard.press('Escape');

        // Upload the revision through the bottom button.
        const authorModal = await openAuthorView(authorPage, tag, seeded.submissionId);
        await authorModal.getByRole('button', {name: 'Upload revisions'}).click();
        await completeUploadWizard(authorPage, fileName);

        // The Revisions Uploaded panel lists the file.
        await expect(
            primaryRegion(authorModal).getByText(fileName).first()
        ).toBeVisible({timeout: 15_000});

        // Editor view: the round status flipped to its submitted partner.
        const mgrModal2 = await openEditorial(mgrPage, tag, seeded.submissionId);
        await expectRoundStatus(mgrModal2, 1, STATUS.revisionsSubmitted);

        // The author's task is gone (the panel's own answer bounds the read).
        await authorPage.goto(`/index.php/${tag}/en/dashboard/mySubmissions`);
        const tasksAfter = await openTasksPanel(authorPage);
        await expect(tasksAfter.getByText(`Submission ${tag}`)).toHaveCount(0);

        // The assigned editor's mailbox holds the revised-version notice.
        const notice = await pkpMail.find({to: editorEmail, contains: tag});
        expect(notice.Subject).toMatch(/Revised Version Uploaded/);
    });

    test('S5: request revisions toward a new round', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u26s5');
        const fileName = `rev-${tag}.txt`;
        const seeded = await seedInExternalReview(ompApi, tag);

        const page = await (await asUser('manager.maya')).newPage();
        const modal = await openEditorial(page, PK, seeded.submissionId);
        await requestRevisions(page, modal, {newRound: true});

        const modal2 = await openEditorial(page, PK, seeded.submissionId);
        await expectRoundStatus(modal2, 1, STATUS.resubmitRequested);

        // The author uploads one revised file.
        const authorPage = await (await asUser('author.alex')).newPage();
        const authorModal = await openAuthorView(authorPage, PK, seeded.submissionId);
        await authorModal.getByRole('button', {name: 'Upload revisions'}).click();
        await completeUploadWizard(authorPage, fileName);
        await expect(
            primaryRegion(authorModal).getByText(fileName).first()
        ).toBeVisible({timeout: 15_000});

        // Editor view: a new round is now needed.
        const modal3 = await openEditorial(page, PK, seeded.submissionId);
        await expectRoundStatus(modal3, 1, STATUS.resubmitSubmitted);

        // Working path after the first upload (A1's register entry owns the
        // vanished bottom button): the Revisions Uploaded panel's own
        // "Upload" control still opens the upload wizard.
        await primaryRegion(authorModal)
            .getByRole('button', {name: 'Upload', exact: true})
            .first()
            .click();
        const wizard = topModal(authorPage);
        await expect(wizard.getByText(/^Upload .* File$/).first()).toBeVisible({
            timeout: 15_000,
        });
        await wizard.getByRole('link', {name: 'Cancel', exact: true}).click();
    });

    test('S6: a new round', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u26s6');
        const fileName = `rev-${tag}.txt`;
        const seeded = await seedInExternalReview(ompApi, tag, {
            extraDecisions: ['resubmit'],
        });

        // The author's revised file is what the new-round wizard will offer.
        const authorPage = await (await asUser('author.alex')).newPage();
        const authorModal = await openAuthorView(authorPage, PK, seeded.submissionId);
        await authorModal.getByRole('button', {name: 'Upload revisions'}).click();
        await completeUploadWizard(authorPage, fileName);

        const page = await (await asUser('manager.maya')).newPage();
        const modal = await openEditorial(page, PK, seeded.submissionId);
        await decisionButton(modal, DECISIONS.newRound).click();
        await expect(
            page.getByRole('heading', {level: 1, name: /New Review Round/})
        ).toBeVisible({timeout: 15_000});
        // Walk to the file step: the revised file arrives already ticked.
        for (let i = 0; i < 4; i++) {
            if (
                await page
                    .getByRole('heading', {name: 'Select Files', exact: true})
                    .count()
            ) {
                break;
            }
            await awaitComposerReady(page);
            await page.getByRole('button', {name: 'Continue', exact: true}).click();
            await page.waitForTimeout(400);
        }
        const fileCheckbox = page.getByRole('checkbox', {name: new RegExp(fileName)});
        await expect(fileCheckbox).toBeChecked();
        await page.getByRole('button', {name: /Record (Editorial )?Decision/}).click();
        await expect(page.getByText('View Submission Summary')).toBeVisible({
            timeout: 30_000,
        });

        // Round 2 opens selected, waiting, with the carried file.
        const modal2 = await openEditorial(page, PK, seeded.submissionId);
        await expect(
            modal2.getByRole('heading', {name: 'Workflow: External Review (Round 2)'})
        ).toBeVisible();
        await expect(modal2.getByText('Review Round 2', {exact: true}).first()).toBeVisible();
        await expectRoundStatus(modal2, 2, STATUS.waiting);
        await expect(
            primaryRegion(modal2).getByText(fileName).first()
        ).toBeVisible();
        // Round 2 (current) offers the decision buttons — the control for
        // their absence on the past round below.
        await expect(decisionButton(modal2, DECISIONS.requestRevisions)).toBeVisible();

        // The past round shows its panels, no decision buttons, and the note.
        await modal2.getByText('Review Round 1', {exact: true}).first().click();
        await expect(
            modal2.getByRole('heading', {name: 'Workflow: External Review (Round 1)'})
        ).toBeVisible();
        await expect(modal2.getByText(STATUS.advancedToNextRound)).toBeVisible();
        await expect(
            primaryRegion(modal2).getByRole('heading', {name: 'Reviewers'})
        ).toBeVisible();
        await expect(decisionButton(modal2, DECISIONS.requestRevisions)).toHaveCount(0);
        await expect(decisionButton(modal2, DECISIONS.accept)).toHaveCount(0);
    });

    test('S7: cancel a round', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u26s7');
        const seeded = await seedInExternalReview(ompApi, tag, {
            reviewers: [],
            extraRounds: [
                {stage: 'external', reviewers: [{username: 'reviewer.paul', status: 'invited'}]},
            ],
        });

        // Control: the invited reviewer sees the assignment in their lists.
        const paulPage = await (await asUser('reviewer.paul')).newPage();
        await paulPage.goto(`/index.php/${PK}/en/dashboard/reviewAssignments`);
        await paulPage.getByText('All assignments', {exact: true}).first().click();
        await expect(paulPage.getByText(/Showing|No Items/).first()).toBeVisible({
            timeout: 20_000,
        });
        await expect(paulPage.getByText(`Submission ${tag}`).first()).toBeVisible();

        // Cancel Round 2 (its only reviewer has not accepted).
        const page = await (await asUser('manager.maya')).newPage();
        const modal = await openEditorial(page, PK, seeded.submissionId);
        await expect(
            modal.getByRole('heading', {name: 'Workflow: External Review (Round 2)'})
        ).toBeVisible();
        await decisionButton(modal, DECISIONS.cancelRound).click();
        await expect(
            page.getByRole('heading', {level: 1, name: /Cancel Review Round/})
        ).toBeVisible({timeout: 15_000});
        await walkDecisionWizard(page);

        // Round 2 is gone; the submission stands on Round 1.
        const modal2 = await openEditorial(page, PK, seeded.submissionId);
        await expect(
            modal2.getByRole('heading', {name: 'Workflow: External Review (Round 1)'})
        ).toBeVisible();
        await expect(modal2.getByText('Review Round 2', {exact: true})).toHaveCount(0);

        // The withdrawn invitation left the reviewer's lists entirely.
        await paulPage.goto(`/index.php/${PK}/en/dashboard/reviewAssignments`);
        await paulPage.getByText('All assignments', {exact: true}).first().click();
        await expect(paulPage.getByText(/Showing|No Items/).first()).toBeVisible({
            timeout: 20_000,
        });
        await expect(paulPage.getByText(`Submission ${tag}`)).toHaveCount(0);

        // Cancelling Round 1 returns the submission to the Submission stage.
        const tagB = `${tag}b`;
        const seededB = await seedInExternalReview(ompApi, tagB);
        const modalB = await openEditorial(page, PK, seededB.submissionId);
        await decisionButton(modalB, DECISIONS.cancelRound).click();
        await expect(
            page.getByRole('heading', {level: 1, name: /Cancel Review Round/})
        ).toBeVisible({timeout: 15_000});
        await walkDecisionWizard(page);
        const modalB2 = await openEditorial(page, PK, seededB.submissionId);
        await expect(
            modalB2.getByRole('heading', {name: 'Workflow: Submission'})
        ).toBeVisible();
    });

    test('S8: cancelling is blocked once a review is in', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u26s8');
        const seeded = await seedInExternalReview(ompApi, tag, {
            reviewers: [{username: 'reviewer.julia', status: 'accepted'}],
        });

        const juliaPage = await (await asUser('reviewer.julia')).newPage();
        await completeReviewAsReviewer(
            juliaPage,
            PK,
            seeded.submissionId,
            `Review remarks ${tag}.`
        );

        const page = await (await asUser('manager.maya')).newPage();
        const modal = await openEditorial(page, PK, seeded.submissionId);
        // Positive control: the other decision buttons render…
        await expect(decisionButton(modal, DECISIONS.requestRevisions)).toBeVisible();
        await expect(decisionButton(modal, DECISIONS.accept)).toBeVisible();
        await expect(decisionButton(modal, DECISIONS.newRound)).toBeVisible();
        await expect(decisionButton(modal, DECISIONS.decline)).toBeVisible();
        // …while Cancel Review Round is simply absent.
        await expect(decisionButton(modal, DECISIONS.cancelRound)).toHaveCount(0);
    });

    test('S9: accept out of review', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u26s9');
        const seeded = await seedInExternalReview(ompApi, tag);

        const page = await (await asUser('manager.maya')).newPage();
        const modal = await openEditorial(page, PK, seeded.submissionId);
        await decisionButton(modal, DECISIONS.accept).click();
        await expect(
            page.getByRole('heading', {level: 1, name: /Accept Submission/})
        ).toBeVisible({timeout: 15_000});
        await walkDecisionWizard(page);

        // The submission moved to Copyediting.
        const modal2 = await openEditorial(page, PK, seeded.submissionId);
        await expect(
            modal2.getByRole('heading', {name: 'Workflow: Copyediting'})
        ).toBeVisible();

        // Selecting the review stage still shows the round, its box now
        // reporting the submission's onward stage.
        await modal2.getByText('Review Round 1', {exact: true}).first().click();
        await expect(
            modal2.getByRole('heading', {name: 'Workflow: External Review (Round 1)'})
        ).toBeVisible();
        await expectPlainStatus(modal2, STATUS.inCopyediting);
    });

    test('S10: decline, revert, delete', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u26s10');
        const seeded = await seedInExternalReview(ompApi, tag);

        // The assigned Series Editor records the decline.
        const anaPage = await (await asUser('sectioneditor.ana')).newPage();
        const anaModal = await openEditorial(anaPage, PK, seeded.submissionId);
        await decisionButton(anaModal, DECISIONS.decline).click();
        await expect(
            anaPage.getByRole('heading', {level: 1, name: /Decline Submission/})
        ).toBeVisible({timeout: 15_000});
        await walkDecisionWizard(anaPage);

        // While declined: the Series Editor gets Revert Decline, no Delete.
        const anaModal2 = await openEditorial(anaPage, PK, seeded.submissionId);
        await expectRoundStatus(anaModal2, 1, STATUS.declined);
        await expect(decisionButton(anaModal2, DECISIONS.revertDecline)).toBeVisible();
        await expect(decisionButton(anaModal2, DECISIONS.delete)).toHaveCount(0);
        await expect(decisionButton(anaModal2, DECISIONS.requestRevisions)).toHaveCount(0);

        // A Press Manager additionally sees Delete.
        const mayaPage = await (await asUser('manager.maya')).newPage();
        const mayaModal = await openEditorial(mayaPage, PK, seeded.submissionId);
        await expect(decisionButton(mayaModal, DECISIONS.revertDecline)).toBeVisible();
        await expect(decisionButton(mayaModal, DECISIONS.delete)).toBeVisible();

        // Revert Decline puts the submission back in review, the status
        // again reflecting the round's reviewer state.
        await decisionButton(mayaModal, DECISIONS.revertDecline).click();
        await expect(
            mayaPage.getByRole('heading', {level: 1, name: /Revert Decline/})
        ).toBeVisible({timeout: 15_000});
        await walkDecisionWizard(mayaPage);
        const mayaModal2 = await openEditorial(mayaPage, PK, seeded.submissionId);
        await expectRoundStatus(mayaModal2, 1, STATUS.waiting);
        await expect(decisionButton(mayaModal2, DECISIONS.requestRevisions)).toBeVisible();
    });

    test('S11: recommend-only round', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u26s11');
        // A declined reviewer is record enough for the recommendation
        // sentences (Rule 6).
        const seeded = await seedInExternalReview(ompApi, tag, {
            reviewers: [{username: 'reviewer.julia', status: 'declined'}],
        });

        // Assign a Series Editor limited to recommendations.
        const mayaPage = await (await asUser('manager.maya')).newPage();
        const mayaModal = await openEditorial(mayaPage, PK, seeded.submissionId);
        await assignParticipant(mayaPage, mayaModal, {
            group: 'Series editor',
            query: 'ravi',
            resultName: 'Ravi Section Editor',
            recommendOnly: true,
        });

        // The recommending editor sees recommendation controls, no decisions.
        const raviPage = await (await asUser('sectioneditor.ravi')).newPage();
        const raviModal = await openEditorial(raviPage, PK, seeded.submissionId);
        await expectRoundStatus(raviModal, 1, STATUS.awaitingRecommendations);
        await expect(
            actionsRegion(raviModal).getByRole('button', {name: 'Recommend Revisions'})
        ).toBeVisible();
        await expect(
            actionsRegion(raviModal).getByRole('button', {name: 'Recommend Accept'})
        ).toBeVisible();
        await expect(
            actionsRegion(raviModal).getByRole('button', {name: 'Recommend Decline'})
        ).toBeVisible();
        await expect(decisionButton(raviModal, DECISIONS.requestRevisions)).toHaveCount(0);
        await expect(decisionButton(raviModal, DECISIONS.accept)).toHaveCount(0);
        await expect(decisionButton(raviModal, DECISIONS.decline)).toHaveCount(0);

        // They record "Accept Submission" as a recommendation.
        await actionsRegion(raviModal).getByRole('button', {name: 'Recommend Accept'}).click();
        await expect(
            raviPage.getByRole('heading', {level: 1, name: /Recommend Accept/})
        ).toBeVisible({timeout: 15_000});
        await walkDecisionWizard(raviPage);

        // The deciding editor sees the Recommendation box and the closing
        // recommendation sentence.
        const mayaModal2 = await openEditorial(mayaPage, PK, seeded.submissionId);
        await expectRoundStatus(mayaModal2, 1, STATUS.recommendationsIn);
        const recommendationBox = secondaryRegion(mayaModal2).filter({
            hasText: 'Recommendation',
        });
        await expect(
            secondaryRegion(mayaModal2).getByRole('heading', {name: 'Recommendation'})
        ).toBeVisible();
        await expect(recommendationBox.getByText('Accept Submission')).toBeVisible();
    });

    test('S12: author reads an open review', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u26s12');
        const remark = `Shared remarks ${tag} for the author.`;
        const seeded = await seedInExternalReview(ompApi, tag, {
            reviewers: [{username: 'reviewer.julia', status: 'accepted'}],
        });

        // Make the review OPEN (per-assignment review type — the seeded
        // default is anonymous).
        const page = await (await asUser('manager.maya')).newPage();
        const modal = await openEditorial(page, PK, seeded.submissionId);
        const row = modal
            .locator('[data-cy="reviewer-manager"]')
            .getByRole('row')
            .filter({hasText: 'Julia Reviewer'});
        await row.getByRole('button', {name: 'More Actions'}).click();
        await page.getByRole('menuitem', {name: 'Edit', exact: true}).click();
        const editModal = topModal(page);
        await expect(editModal.getByText('Review Type')).toBeVisible({timeout: 20_000});
        await editModal.getByLabel('Open', {exact: true}).check();
        await editModal.getByRole('button', {name: 'OK', exact: true}).click();
        await expect(row.getByText('Open', {exact: true})).toBeVisible({timeout: 20_000});

        // The reviewer completes the open review with remarks shared with
        // the author.
        const juliaPage = await (await asUser('reviewer.julia')).newPage();
        await completeReviewAsReviewer(juliaPage, PK, seeded.submissionId, remark);

        // A decision letter for the Notifications list (Rule 16).
        const modal2 = await openEditorial(page, PK, seeded.submissionId);
        await requestRevisions(page, modal2, {newRound: false});

        // The author's view lists the open review and offers Read Review.
        const authorPage = await (await asUser('author.alex')).newPage();
        const authorModal = await openAuthorView(authorPage, PK, seeded.submissionId);
        await expect(authorModal.getByText('Julia Reviewer')).toBeVisible({timeout: 15_000});
        await authorModal.getByRole('button', {name: 'Read Review'}).click();
        const readModal = topModal(authorPage);
        // Reviewer name, completion date, and the shared remarks (the press
        // shows the text — OJS1 is journal-only). No recommendation-line
        // claim (OMP2 ❓) and nothing about attachments (A3 ❓).
        await expect(readModal.getByText('Julia Reviewer').first()).toBeVisible({
            timeout: 20_000,
        });
        await expect(readModal.getByText(/Completed/).first()).toBeVisible();
        await expect(readModal.getByText(remark)).toBeVisible();
        await authorPage.keyboard.press('Escape');

        // The decision letter sits under "Notifications", read-only.
        await expect(
            authorModal.getByRole('heading', {name: 'Notifications'})
        ).toBeVisible();
        await authorModal
            .getByText('Your submission has been reviewed and we encourage you to submit revisions')
            .first()
            .click();
        const letterModal = topModal(authorPage);
        await expect(letterModal.getByText(remark)).toBeVisible({timeout: 20_000});
        await expect(letterModal.getByRole('textbox')).toHaveCount(0);
        await authorPage.keyboard.press('Escape');

        // Control: an anonymous completed review on another submission
        // renders no reviewers list at all — not an empty one.
        const tagB = `${tag}b`;
        const seededB = await seedInExternalReview(ompApi, tagB, {
            reviewers: [{username: 'reviewer.paul', status: 'accepted'}],
        });
        const paulPage = await (await asUser('reviewer.paul')).newPage();
        await completeReviewAsReviewer(paulPage, PK, seededB.submissionId, `Anon remarks ${tagB}.`);

        const authorModalB = await openAuthorView(authorPage, PK, seededB.submissionId);
        // Positive controls on the same screen: the status box and the
        // revisions panel render…
        await expect(
            primaryRegion(authorModalB).getByRole('heading', {name: 'Round 1 Status'})
        ).toBeVisible();
        await expect(
            primaryRegion(authorModalB).getByRole('heading', {name: 'Revisions Uploaded'})
        ).toBeVisible();
        // …while no reviewers surface exists anywhere in the view.
        await expect(authorModalB.locator('[data-cy="reviewer-manager"]')).toHaveCount(0);
        await expect(authorModalB.getByRole('button', {name: 'Read Review'})).toHaveCount(0);
        await expect(authorModalB.getByText('Paul Reviewer')).toHaveCount(0);
    });

    test('S13: straight to External Review (skip-internal entry)', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u26s13');
        const seeded = await seedMonograph(ompApi, tag);

        const page = await (await asUser('manager.maya')).newPage();
        const modal = await openEditorial(page, PK, seeded.submissionId);
        // The Submission stage offers both review entries; External skips
        // the internal stage.
        await expect(decisionButton(modal, 'Send to Internal Review')).toBeVisible();
        await decisionButton(modal, 'Send to External Review').click();
        await expect(
            page.getByRole('heading', {name: /Send to External Review: Notify Authors/})
        ).toBeVisible({timeout: 15_000});
        await awaitComposerReady(page);
        await page.getByRole('button', {name: 'Continue', exact: true}).click();
        await expect(
            page.getByRole('heading', {name: 'Select Files', exact: true})
        ).toBeVisible({timeout: 15_000});
        await page.getByRole('button', {name: /Record (Editorial )?Decision/}).click();
        await expect(page.getByText('View Submission Summary')).toBeVisible({
            timeout: 30_000,
        });

        // External Review Round 1 opens exactly as in scenario 1, and the
        // menu still carries the separate Internal Review stage entry.
        const modal2 = await openEditorial(page, PK, seeded.submissionId);
        await expect(
            modal2.getByRole('heading', {name: 'Workflow: External Review (Round 1)'})
        ).toBeVisible();
        await expect(modal2.getByText('Review Round 1', {exact: true}).first()).toBeVisible();
        await expectRoundStatus(modal2, 1, STATUS.waiting);
        await expect(
            modal2.getByText('Internal Review', {exact: true}).first()
        ).toBeVisible();
    });
});
