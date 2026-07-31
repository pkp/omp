/**
 * @file playwright/support/fixtures.js
 *
 * The OMP test extension — layers OMP fixtures on top of the shared base.
 */
const base = require('../../lib/pkp/playwright/support/base-test.js');

const test = base.test.extend({
    // OMP alias for the shared test-API client.
    ompApi: async ({pkpApi}, use) => {
        await use(pkpApi);
    },
});

module.exports = {test, expect: base.expect};
