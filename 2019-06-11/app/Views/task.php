<?php $this->display('header'); ?>

<?php
$task = $this->getParam('task');
if ($task instanceof \App\Models\Task) :
    ?>
    <div class="container">

        <form method="post" action="?route=task/update">
            <input type="hidden" name="id" value="<?php echo $task->id; ?>">
            <div class="form-group">
                <label for="exampleInputPassword1">User name</label>
                <input type="text" class="form-control" name="user_name" placeholder="User name"
                       value="<?php echo self::protectValue($task->user_name); ?>">
            </div>
            <div class="form-group">
                <label for="exampleFormControlInput1">Email address</label>
                <input type="email" class="form-control" name="email" placeholder="name@example.com"
                       value="<?php echo self::protectValue($task->email); ?>">
            </div>
            <div class="form-group">
                <label for="exampleFormControlTextarea1">Text</label>
                <textarea class="form-control" id="exampleFormControlTextarea1" name="text"
                          rows="3"><?php echo self::protectValue($task->text); ?></textarea>
            </div>

            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                <label class="btn btn-secondary<?php if ((int)$task->status === 0) { echo ' active'; } ?>">
                    <input type="radio" name="status" value="0" id="option1" autocomplete="off"<?php if ((int)$task->status === 0) { echo ' checked'; } ?>> NEW
                </label>
                <label class="btn btn-secondary<?php if ((int)$task->status === 1) { echo ' active'; } ?>">
                    <input type="radio" name="status" value="1" id="option2" autocomplete="off"<?php if ((int)$task->status === 1) { echo ' checked'; } ?>> COMPLETED
                </label>
            </div>
            <br>
            <br>
            <button type="submit" class="btn btn-primary" <?php if(!$this->getParam('is_admin')) { echo ' disabled'; } ?>>Save</button>
        </form>
    </div>

<?php else: ?>
    <div class="alert alert-danger" role="alert">
        Задача не найдена
    </div>
<?php endif; ?>

<?php $this->display('footer'); ?>