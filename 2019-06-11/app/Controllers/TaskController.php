<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Request;
use App\Models\Task;

class TaskController extends DefaultController
{
    public function createAction()
    {
        $task = new Task();
        $task->text = Request::getString('text');
        $task->user_name = Request::getString('user_name');
        $task->email = Request::getString('email');
        $task->save();

        $this->redirect('?');
    }

    public function infoAction()
    {
        $id = Request::getInt('task');
        $this->setParam('task', Task::getById($id));
        $this->display('task');
    }

    public function updateAction()
    {
        $id = Request::getInt('id');
        if (!$this->app->isAdmin()) {
            $this->redirect('?route=task/info&task=' . $id . '&error='.urlencode('Access denied'));
            return;
        }
        $userName = Request::getString('user_name');
        $email = Request::getString('email');
        $text = Request::getString('text');
        $status = Request::getString('status');
        $task = Task::getById($id);
        if (!($task instanceof Task)) {
            $this->redirect('?route=task/info&task=' . $id . '&error='.urlencode('Task not found'));
        }
        $task->user_name = $userName;
        $task->email = $email;
        $task->text = $text;
        $task->status = $status;
        $task->save();

        $this->redirect('?route=task/info&task=' . $id);
    }
}