<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Request;
use App\Models\Task;

class MainController extends DefaultController
{
    public function indexAction()
    {
        $sort = Request::getString('sort', 'id');
        $limit = Request::getInt('limit', 3);
        $page = Request::getInt('page',1);

        $this->setParam('pages', Task::pagesCount($limit));
        $this->setParam('page_current', $page);
        $this->setParam('sort', $sort);
        $this->setParam('tasks', Task::getList($sort, $limit, $page));
        $this->display();
    }
}