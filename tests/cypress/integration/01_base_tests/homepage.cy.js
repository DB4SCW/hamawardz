describe('Homepage Test', () => {

    before(() => {
        cy.php('touch(database_path(env("DB_DATABASE", "database.sqlite")));');
        cy.refreshDatabase();
    });

    it('shows the homepage', () => {
        cy.visit('/');
        cy.contains('Hamawardz');
    });
});
