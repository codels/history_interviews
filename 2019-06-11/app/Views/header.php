<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
          integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

    <title>Application</title>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="?">Application</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item active">
                <a class="nav-link" href="?">Home <span class="sr-only">(current)</span></a>
            </li>
        </ul>
        <?php if (!$this->getParam('is_admin')): ?>
            <form class="form-inline my-2 my-lg-0" action="?route=auth/login" method="post">
                <input class="form-control mr-sm-2" name="login" type="text" placeholder="Login" aria-label="Login">
                <input class="form-control mr-sm-2" name="password" type="text" placeholder="Password"
                       aria-label="Password">
                <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Log in</button>
            </form>
        <?php else: ?>
            <form class="form-inline my-2 my-lg-0" action="?route=auth/logout" method="post">
                <span class="sr-only">Admin</span>
                <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Log out</button>
            </form>
        <?php endif; ?>
    </div>
</nav>

<?php if ($this->isExistsParam('error')): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo $this->getParam('error'); ?>
    </div>
<?php endif; ?>
