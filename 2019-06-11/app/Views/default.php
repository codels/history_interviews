<?php $this->display('header'); ?>

    <div class="container">
        <div class="row">
            <div class="col">
                <?php
                $pageCurrent = $this->getParam('page_current');
                $sort = $this->getParam('sort');
                ?>
                <table class="table table-hover">
                    <thead class="thead-dark">
                    <tr>
                        <th scope="col"><a href="?page=<?php echo $pageCurrent; ?>&amp;sort=id">#</a></th>
                        <th scope="col"><a href="?page=<?php echo $pageCurrent; ?>&amp;sort=user_name">User name</a></th>
                        <th scope="col"><a href="?page=<?php echo $pageCurrent; ?>&amp;sort=email">Email</a></th>
                        <th scope="col"><a href="?page=<?php echo $pageCurrent; ?>&amp;sort=status">Status</a></th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php
                    $tasks = $this->getParam('tasks');
                    foreach ($tasks as $task):
                        ?>

                        <tr>
                            <th scope="row"><a href="?route=task/info&amp;task=<?php echo $task->id; ?>"><?php echo $task->id; ?></a></th>
                            <td><?php echo self::protectValue($task->user_name); ?></td>
                            <td><?php echo self::protectValue($task->email); ?></td>
                            <td>
                                <?php if ((int)$task->status === 0): ?>
                                    NEW
                                <?php else: ?>
                                    COMPLETED
                                <?php endif; ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                    </tbody>
                </table>

                <?php if ($this->getParam('pages') > 1): ?>
                    <nav aria-label="...">
                        <ul class="pagination pagination-lg">
                            <?php
                            $pages = $this->getParam('pages');
                            for ($i = 1; $i <= $pages; $i++) {
                                if ($i === $pageCurrent) {
                                    echo '<li class="page-item active" aria-current="page"><span class="page-link">'.$i.'<span class="sr-only">(current)</span></span></li>';
                                } else {
                                    echo '<li class="page-item"><a class="page-link" href="?page='.$i.'&amp;sort='.$sort.'">'.$i.'</a></li>';
                                }
                            }
                            ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>

            <div class="col-md-auto">
                <form method="post" action="?route=task/create">
                    <div class="form-group">
                        <label for="exampleInputPassword1">User name</label>
                        <input type="text" class="form-control" name="user_name" placeholder="User name">
                    </div>
                    <div class="form-group">
                        <label for="exampleFormControlInput1">Email address</label>
                        <input type="email" class="form-control" name="email" placeholder="name@example.com">
                    </div>
                    <div class="form-group">
                        <label for="exampleFormControlTextarea1">Text</label>
                        <textarea class="form-control" id="exampleFormControlTextarea1" name="text" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>

<?php $this->display('footer'); ?>