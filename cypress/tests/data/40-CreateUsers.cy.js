/**
 * @file cypress/tests/data/40-CreateUsers.spec.js
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 */

describe('Data suite tests', function() {
	it('Creates users', function() {
		cy.login('admin', 'admin');
		cy.get('a:contains("admin"):visible').click();
		cy.get('a:contains("Dashboard")').click();
		cy.get('a:contains("Users & Roles")').click();

		var users = [
			{
				'username': 'rvaca',
				'givenName': 'Ramiro',
				'familyName': 'Vaca',
				'country': 'Mexico',
				'affiliation': 'Universidad Nacional Autónoma de México',
				'mustChangePassword': true,
				'roles': ['Press manager']
			},
			{
				'username': 'dbarnes',
				'givenName': 'Daniel',
				'familyName': 'Barnes',
				'country': 'Australia',
				'affiliation': 'University of Melbourne',
				'roles': ['Press editor']
			},
			{
				'username': 'dbuskins',
				'givenName': 'David',
				'familyName': 'Buskins',
				'country': 'United States',
				'affiliation': 'University of Chicago',
				'roles': ['Series editor']
			},
			{
				'username': 'sberardo',
				'givenName': 'Stephanie',
				'familyName': 'Berardo',
				'country': 'Canada',
				'affiliation': 'University of Toronto',
				'roles': ['Series editor']
			},
			{
				'username': 'minoue',
				'givenName': 'Minoti',
				'familyName': 'Inoue',
				'country': 'Japan',
				'affiliation': 'Kyoto University',
				'roles': ['Series editor']
			},
			{
				'username': 'jjanssen',
				'givenName': 'Julie',
				'familyName': 'Janssen',
				'country': 'Netherlands',
				'affiliation': 'Utrecht University',
				'roles': ['Internal Reviewer']
			},
			{
				'username': 'phudson',
				'givenName': 'Paul',
				'familyName': 'Hudson',
				'country': 'Canada',
				'affiliation': 'McGill University',
				'roles': ['Internal Reviewer']
			},
			{
				'username': 'amccrae',
				'givenName': 'Aisla',
				'familyName': 'McCrae',
				'country': 'Canada',
				'affiliation': 'University of Manitoba',
				'roles': ['Internal Reviewer']
			},
			{
				'username': 'agallego',
				'givenName': 'Adela',
				'familyName': 'Gallego',
				'country': 'United States',
				'affiliation': 'State University of New York',
				'roles': ['External Reviewer']
			},
			{
				'username': 'alzacharia',
				'givenName': 'Al',
				'familyName': 'Zacharia',
				'country': 'Ghana',
				'affiliation': 'KNUST',
				'roles': ['External Reviewer']
			},
			{
				'username': 'gfavio',
				'givenName': 'Gonzalo',
				'familyName': 'Favio',
				'country': 'Spain',
				'affiliation': 'Madrid',
				'roles': ['External Reviewer']
			},
			{
				'username': 'mfritz',
				'givenName': 'Maria',
				'familyName': 'Fritz',
				'country': 'Belgium',
				'affiliation': 'Ghent University',
				'roles': ['Copyeditor']
			},
			{
				'username': 'svogt',
				'givenName': 'Sarah',
				'familyName': 'Vogt',
				'country': 'Chile',
				'affiliation': 'Universidad de Chile',
				'roles': ['Copyeditor']
			},
			{
				'username': 'gcox',
				'givenName': 'Graham',
				'familyName': 'Cox',
				'country': 'United States',
				'affiliation': 'Duke University',
				'roles': ['Layout Editor']
			},
			{
				'username': 'shellier',
				'givenName': 'Stephen',
				'familyName': 'Hellier',
				'country': 'South Africa',
				'affiliation': 'University of Cape Town',
				'roles': ['Layout Editor']
			},
			{
				'username': 'cturner',
				'givenName': 'Catherine',
				'familyName': 'Turner',
				'country': 'United Kingdom',
				'affiliation': 'Imperial College London',
				'roles': ['Proofreader']
			},
			{
				'username': 'skumar',
				'givenName': 'Sabine',
				'familyName': 'Kumar',
				'country': 'Singapore',
				'affiliation': 'National University of Singapore',
				'roles': ['Proofreader']
			}
		]
		users.forEach(user => {
			cy.createUser(user);
		});
		cy.logout();
		var user = users[0];
		if (!('email' in user)) user.email = user.username + '@mailinator.com';
		if (!('password' in user)) user.password = user.username + user.username;

		cy.login(user.username);
		cy.resetPassword(user.username, user.password);
		cy.logout();
	});
})
