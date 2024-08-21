describe('Homepage Test', () => {

    before(() => {
        cy.refreshDatabase();
    });

    it('shows the homepage', () => {
        cy.visit('/');
        cy.contains('Hamawardz');
    });
});
