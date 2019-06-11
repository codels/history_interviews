<?php

declare(strict_types=1);

namespace App\Controllers;

class TempController extends DefaultController
{
    public function installAction()
    {
        $this->app->install();
        $this->display('install');
    }
}