<?php

declare(strict_types=1);

require_once 'app/bootstrap.php';

$app = App\Libraries\Application::getInstance();
$route = 'main/index';
if (isset($_REQUEST['route']) && is_string($_REQUEST['route'])) {
    $route = (string)$_REQUEST['route'];
}
$app->execute($route);
