describe('Login Test', () => {

    //refresh database before the whole test suite
    before(() => {
        cy.refreshDatabase();
    });

    //tests if we can switch to the login page
    it('switches to the login page', () => {
        cy.visit('/');
        cy.get('#loginlink').click();   
        cy.location('pathname').should('eq', '/login');

    });

    //logs the base administrator in and out
    it('logs the base administrator in and out', () => {
        cy.visit('/');
        cy.get('#loginlink').click();   
        cy.get('#username').type('administrator');
        cy.get('#password').type('welcome#01');
        cy.get('.btn').click();
        cy.contains('Login successful. Welcome back!');
        cy.contains('Logout');
        cy.get('#logoutlink').click();   
        cy.location('pathname').should('eq', '/');
        cy.contains('Logout successful. Hope to see you again soon.');
        cy.contains('Login');
    });

    
});
