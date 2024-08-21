describe('Login Test', () => {
    
    //refresh database before the whole test suite
    before(function () {
        cy.refreshDatabase();
    });

    //load fixture data
    beforeEach(function () {
        cy.fixture('fixed_data').then((fixed_data) => {
          this.fixed_data = fixed_data
        })
    });

    //tests if we can switch to the login page
    it('switches to the login page', function () {
        cy.visit('/');
        cy.get('#loginlink').click();   
        cy.location('pathname').should('eq', '/login');

    });

    //logs the base administrator in and out
    it('logs the base administrator in and out', function() {
        cy.visit('/');
        cy.get('#loginlink').click();   
        cy.get('#username').type(this.fixed_data.admin_username);
        cy.get('#password').type(this.fixed_data.adminpassword);
        cy.get('.btn').click();
        cy.contains('Login successful. Welcome back!');
        cy.contains('Logout');
        cy.get('#logoutlink').click();   
        cy.location('pathname').should('eq', '/');
        cy.contains('Logout successful. Hope to see you again soon.');
        cy.contains('Login');
    });

    
});
