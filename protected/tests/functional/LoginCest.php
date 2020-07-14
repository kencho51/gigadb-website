<?php 

class LoginCest
{
    public function _before(FunctionalTester $I)
    {
    }

    // tests
    public function tryLogin($I)
    {
        $I->amOnPage('/');
        $I->click('Login');
    }
}
