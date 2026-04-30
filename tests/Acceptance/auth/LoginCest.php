<?php

namespace Tests\Acceptance\auth;

use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;

class LoginCest extends BaseAcceptanceCest
{
    public function seeLoginScreen(AcceptanceTester $page): void
    {
        $page->amOnPage('/login');

        $page->see('Medical Records');
        $page->see('Acesso seguro ao sistema integrado.');
        $page->seeElement('#email');
        $page->seeElement('#password');
        $page->see('Esqueci minha senha');
        $page->see('Entrar');
    }
}
