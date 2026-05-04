<?php

namespace Tests\Acceptance\auth;

use App\Models\Admin;
use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;
use App\Models\User;

class LoginCest extends BaseAcceptanceCest
{
    public function _before(AcceptanceTester $page): void
    {
        parent::_before($page);
        $user = new User([
            'name' => 'Usuário de Teste',
            'email' => 'teste@example.com',
            'cpf' => '12345678901',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $user->save();

        $admin = new Admin([
            'user_id' => $user->id,
            'phone' => '11999999999',
        ]);
        $admin->save();
    }

    public function seeLoginScreen(AcceptanceTester $page): void
    {
        $page->amOnPage('/login');
        $page->see('Medical Records');
        $page->see('Acesso ao Sistema Integrado');
        $page->seeElement('#email');
        $page->seeElement('#password');
        $page->see('Entrar');
    }

    public function tryToLoginWithWrongCredentials(AcceptanceTester $page): void
    {
        $page->amOnPage('/login');
        $page->fillField('#email', 'teste@example.com');
        $page->fillField('#password', 'senha_errada');
        $page->click('Entrar');
        $page->seeCurrentUrlEquals('/login');

        $page->see('E-mail e/ou senha inválidos!');
    }

    public function loginSuccessfully(AcceptanceTester $page): void
    {
        $page->amOnPage('/login');
        $page->fillField('#email', 'teste@example.com');
        $page->fillField('#password', '123456');
        $page->click('Entrar');

        $page->seeCurrentUrlEquals('/admin');
        $page->see('Admin');
    }

    public function logoutSuccessfully(AcceptanceTester $page): void
    {
        $page->amOnPage('/login');
        $page->fillField('#email', 'teste@example.com');
        $page->fillField('#password', '123456');
        $page->click('Entrar');
        $page->seeCurrentUrlEquals('/admin');

        $page->click('Sair');

        $page->seeCurrentUrlEquals('/login');
        $page->see('Acesso ao Sistema Integrado');
    }
}
