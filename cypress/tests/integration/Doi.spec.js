/**
 * @file cypress/tests/integration/Doi.spec.js
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 */

describe('DOI tests', function() {
	const submissionId = 14;
	const publicationId = 14;
	const chapterId = 54;
	const publicationFormatId = 3;
	const submissionFileId = 113;
	const unpublishedSubmissionId = 4;

	const loginAndGoToDoiPage = () => {
		cy.login('dbarnes', null, 'publicknowledge');
		goToDoiPage();
	};

	const goToDoiPage = () => {
		cy.get('a:contains("DOIs")').click();
		cy.get('button#submission-doi-management-button').click();
	};

	const clearFilter = () => {
		cy.get('#submission-doi-management button:contains("Clear filter")').click();
	};

	it('Check DOI Configuration', function() {
		cy.login('dbarnes', null, 'publicknowledge');
		cy.checkDoiConfig(['publication', 'chapter', 'representation', 'file']);
	});

	it('Check DOI Assignment and Visibility', function() {
		cy.log('Check Submission Assignment');
		loginAndGoToDoiPage();
		cy.assignDois(submissionId);

		cy.get(`#list-item-submission-${submissionId} button.expander`).click();
		cy.checkDoiAssignment(`${submissionId}-monograph-${publicationId}`);
		cy.checkDoiAssignment(`${submissionId}-chapter-${chapterId}`);
		cy.checkDoiAssignment(`${submissionId}-publicationFormat-${publicationFormatId}`);
		cy.checkDoiAssignment(`${submissionId}-submissionFile-${submissionFileId}`);

		cy.log('Check Submission Visibility');
		// Select a monograph
		cy.visit(`/index.php/publicknowledge/catalog/book/${submissionId}`);

		// Monograph DOI
		cy.get('div.item.doi')
			.find('span.value')
			.contains('https://doi.org/10.1234/');
		// Chapter DOI
		cy.get('div.item.chapters ul')
			.find('li:first-child')
			.contains('https://doi.org/10.1234/');
		// PublicationFormat DOI
		cy.get(
			`div.item.publication_format div.sub_item.pubid.${publicationFormatId} div.value`
		)
			.find('a')
			.contains('https://doi.org/10.1234/');
		// SubmissionFile not visible
	});

	it('Check filters and mark registered', function() {
		cy.log('Check Submission Filter Behaviour (pre-deposit)');
		loginAndGoToDoiPage();
		cy.checkDoiFilterResults('Needs DOI', 'Allan — Bomb Canada and Other Unkind Remarks in the American Media', 2);
		cy.checkDoiFilterResults('Unpublished', 'No items found.', 0);
		cy.checkDoiFilterResults('Unregistered', 'Dawson et al. — From Bricks to Brains: The Embodied Cognitive Science of LEGO Robots', 1);
		clearFilter();

		cy.log('Check Submission Marked Registered');
		cy.checkDoiMarkedStatus('Registered', submissionId, true, 'Registered');

		cy.log('Check Submission Filter Behaviour (post-deposit)');
		cy.checkDoiFilterResults('Submitted', 'No items found.', 0);
		cy.checkDoiFilterResults('Registered', 'Dawson et al. — From Bricks to Brains: The Embodied Cognitive Science of LEGO Robots', 1);
	});

	it('Check Marked Status Behaviour', function() {
		loginAndGoToDoiPage();

		cy.log('Check unpublished Submission Marked Registered displays error');
		cy.checkDoiMarkedStatus('Registered', unpublishedSubmissionId, false, 'Unpublished');

		cy.log('Check Submission Marked Stale');
		cy.checkDoiMarkedStatus('Stale', submissionId, true, 'Stale');

		cy.log('Check Submission Marked Unregistered');
		cy.checkDoiMarkedStatus('Unregistered', submissionId, true, 'Unregistered');

		cy.log('Check invalid Submission Marked Stale displays error');
		cy.checkDoiMarkedStatus('Stale', submissionId, false, 'Unregistered');
	});
});
