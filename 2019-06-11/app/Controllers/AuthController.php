<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Request;

class AuthController extends DefaultController
{
    public function loginAction()
    {
        $login = strtolower(Request::getString('login'));
        $password = strtolower(Request::getString('password'));

        if ($login === 'admin' && $password === '123') {
            $this->app->setAdmin(true);
            $this->redirect('?');
        } else {
            $this->redirect('?error='.urlencode('Login or password incorrect'));
        }
    }

    public function logoutAction()
    {
        $this->app->setAdmin(false);
        $this->redirect('?');
    }
}