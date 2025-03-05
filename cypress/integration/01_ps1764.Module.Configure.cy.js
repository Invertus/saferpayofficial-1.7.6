/**
 *NOTICE OF LICENSE
 *
 *This source file is subject to the Open Software License (OSL 3.0)
 *that is bundled with this package in the file LICENSE.txt.
 *It is also available through the world-wide-web at this URL:
 *http://opensource.org/licenses/osl-3.0.php
 *If you did not receive a copy of the license and are unable to
 *obtain it through the world-wide-web, please send an email
 *to license@prestashop.com so we can send you a copy immediately.
 *
 *DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 *versions in the future. If you wish to customize PrestaShop for your
 *needs please refer to http://www.prestashop.com for more information.
 *
 *@author INVERTUS UAB www.invertus.eu  <support@invertus.eu>
 *@copyright SIX Payment Services
 *@license   SIX Payment Services
 */
/// <reference types="Cypress" />
///<reference types="cypress-iframe" />
function prepareCookie()
      {
            const name = 'PrestaShop-';

                   cy.request(
            {
                url: '/'
            }
        ).then((res) => {

            const cookies = res.requestHeaders.cookie.split(/; */);

            cookies.forEach(cookie => {

                const parts = cookie.split('=');
                const key = parts[0]
                const value = parts[1];

                if (key.startsWith(name)) {
                    cy.setCookie(
                        key,
                        value,
                        {
                            sameSite: 'None',
                            secure: true
                        }
                    );
                }
            });

        });
      }
      //Caching the BO and FO session
      const login = (SaferpayBOFOLoggingIn) => {
      cy.session(SaferpayBOFOLoggingIn,() => {
      cy.visit('https://sp1764.eu.ngrok.io/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.PSBOlogin()
      cy.visit('https://sp1764.eu.ngrok.io/index.php?controller=my-account')
      cy.PSFOlogin()
      cy.get('#history-link > .link-item').click()
      })
      }
describe('PS1764 Module Configuration', () => {
  beforeEach(() => {
      cy.viewport(1920,1080)
      login('SaferpayBOFOLoggingIn')
  })
it('01 Connecting the Test API information to module', () => {
      cy.visit('https://sp1764.eu.ngrok.io/admin1/')
      cy.get('#subtab-AdminParentModulesSf > :nth-child(1)').click()
      cy.get('#subtab-AdminModulesSf > .link').click()
      cy.get('.pstaggerAddTagInput').type('saferpay')
      cy.get('#module-search-button').click()
      cy.get('.btn-group > .btn-primary-reverse').click()  //clicking the Congifure
      cy.get('[name="SAFERPAY_USERNAME_TEST"]').type((Cypress.env('SAFERPAY_USERNAME_TEST')),{delay: 0, log: false})
      cy.get('[name="SAFERPAY_PASSWORD_TEST"]').type((Cypress.env('SAFERPAY_PASSWORD_TEST')),{delay: 0, log: false})
      cy.get('[name="SAFERPAY_CUSTOMER_ID_TEST"]').type((Cypress.env('SAFERPAY_CUSTOMER_ID_TEST')),{delay: 0, log: false})
      cy.get('[name="SAFERPAY_TERMINAL_ID_TEST"]').type((Cypress.env('SAFERPAY_TERMINAL_ID_TEST')),{delay: 0, log: false})
      cy.get('[name="SAFERPAY_MERCHANT_EMAILS_TEST"]').type((Cypress.env('SAFERPAY_MERCHANT_EMAILS_TEST')),{delay: 0, log: false})
      cy.get('[name="SAFERPAY_FIELDS_ACCESS_TOKEN_TEST"]').type((Cypress.env('SAFERPAY_FIELDS_ACCESS_TOKEN_TEST')),{delay: 0, log: false})
      cy.get('#configuration_fieldset_1 > .panel-footer > .btn').click()
      cy.get(':nth-child(4) > .alert').should('exist')
})
it('02 Enabling Saferpay carriers and countries successfully', () => {
      cy.visit('https://sp1764.eu.ngrok.io/admin1/')
      cy.get('[id="subtab-AdminPaymentPreferences"]').find('[href]').eq(0).click({force:true})
      cy.get('[class="js-multiple-choice-table-select-column"]').eq(7).click()
      cy.get('[class="btn btn-primary"]').eq(3).click()
})
it('03 Enabling All payments in Module BO', () => {
      cy.visit('https://sp1764.eu.ngrok.io/admin1/')
      cy.get('#subtab-AdminParentModulesSf > :nth-child(1)').click()
      cy.get('#subtab-AdminModulesSf > .link').click()
      cy.get('.pstaggerAddTagInput').type('saferpay')
      cy.get('#module-search-button').click()
      cy.get('.btn-group > .btn-primary-reverse').click()  //clicking the Congifure
      cy.get('#subtab-AdminSaferPayOfficialPayment').click()
      //todo update selectors
      cy.get('.saferpay-group.all-payments > .col-lg-9 > .form-group > :nth-child(1) > .checkbox > .container-checkbox > .checkmark').click()
      cy.get('.saferpay-group.all-payments > .col-lg-9 > .form-group > :nth-child(2) > .checkbox > .container-checkbox > .checkmark').click()
      cy.get('.saferpay-group.all-payments > .col-lg-9 > .form-group > :nth-child(3) > .checkbox > .container-checkbox > .checkmark').click()
      cy.get('#all_countries_chosen > .chosen-choices > .search-field > .default').click()
      cy.get('.highlighted').click()
      cy.get('#all_currencies_chosen > .chosen-choices > .search-field > .default').click()
      cy.get('.highlighted').click()
      cy.get('#configuration_form_submit_btn').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('04 Fields and Logs tabs are shown OK', () => {
    cy.visit('https://sp1764.eu.ngrok.io/admin1/')
    cy.get('#subtab-AdminParentModulesSf > :nth-child(1)').click()
    cy.get('#subtab-AdminModulesSf > .link').click()
    cy.get('.pstaggerAddTagInput').type('saferpay')
    cy.get('#module-search-button').click()
    cy.get('.btn-group > .btn-primary-reverse').click()  //clicking the Congifure
    cy.get('#subtab-AdminSaferPayOfficialFields').click()
    cy.get('[id="configuration_form"]').should('be.visible')
    cy.get('.field-container > :nth-child(1) > img').click()
    cy.get(':nth-child(2) > img').click()
    cy.get(':nth-child(3) > img').click()
    cy.get('[class="alert alert-info"]').should('be.visible')
    cy.get('[name="submitOptionsconfiguration"]').click()
    cy.get('[class="alert alert-success"]').should('be.visible')
    cy.get('#subtab-AdminSaferPayOfficialLogs').click()
    cy.get('[id="form-saferpay_log"]').should('be.visible')
})
})
