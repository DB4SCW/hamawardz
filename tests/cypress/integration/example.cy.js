describe('Example Test', () => {

    before(() => {
        cy.refreshDatabase();
    });

    it('shows a homepage', () => {
        cy.visit('/');

        cy.contains('Hamawardz');
    });
});
