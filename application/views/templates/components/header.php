<nav ng-controller='header' class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/">Online Housie</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <?php if(checkLogin(false)) { ?>
            <ul class="navbar-nav mr-auto">
                <li class="nav-item <?php echo (checkActiveMenu("/",true)) ? "active" : "" ?>">
                    <a class="nav-link" href="/">Home</a>
                </li>
            </ul>
            <form class="form-inline">
                <div class="dropdown">
                    <a class="form-control mr-sm-2 dropdown-toggle btn btn-default" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php echo $_SESSION['name'];?>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href='/logout' >Sign out</a>
                    </div>
                </div>
            </form>
            <?php } else { ?>
                <ul class="navbar-nav mr-auto">
                </ul>
                <form class="form-inline">
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item <?php echo (checkActiveMenu("/login")) ? "active" : "" ?>">
                            <a class="nav-link" href="/login">Login</a>
                        </li>
                    </ul>
                </form>
            <?php }  ?>
        </div>
    </div>
</nav>

