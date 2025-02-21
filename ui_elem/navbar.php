<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="/">Artifact Vault</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Artifact
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/artifact/search.php">Search</a></li>
                        <li><a class="dropdown-item" href="/artifact/new.php">New</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Base system
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/base/search.php">Search</a></li>
                        <li><a class="dropdown-item" href="/base/new.php">New</a></li>
                    </ul>
                </li>
            </ul>
            <form class="d-flex" role="search" action="/artifact/search.php" method="get">
                <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search" name="q">
                <button class="btn btn-outline-success" type="submit">Search</button>
            </form>
        </div>
    </div>
</nav>