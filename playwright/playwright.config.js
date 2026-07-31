/**
 * @file playwright/playwright.config.js
 *
 * OMP fleet: port 8100 (+parallelIndex per worker). All harness mechanics live
 * in the shared factory.
 */
const path = require('path');
const {definePkpConfig} = require('../lib/pkp/playwright/config-factory.js');

module.exports = definePkpConfig({
    appName: 'omp',
    appRoot: path.resolve(__dirname, '..'),
    basePort: 8100,
});
