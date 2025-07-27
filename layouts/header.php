<?php
  if (!isset($_SESSION)) { 
    session_start(); 
  }
  $current_page = basename($_SERVER['PHP_SELF']);
  $is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
  $user_name = $_SESSION['user_name'] ?? 'Minha Conta'; 
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Xain</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css"
    integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous">
  </link>
</head>

<body>
<header>
  <div class=" mt-0 bg-black text-white fixed-top">
    <div class="container">
      <div class="d-flex">
        <div class="mb-2 d-flex w-100 m-auto">
          <div>
            <ul class="nav my-md-0 text-small">
              <li class="nav-item">
                <a href="index.php" class="nav-link <?php if ($current_page == 'index.php') echo 'active'; ?> link-light">
                  Home
                </a>
              </li>
              <li class="nav-item">
                <a href="products.php" class="nav-link <?php if ($current_page == 'products.php') echo 'active'; ?> link-light">
                  Produtos
                </a>
              </li>
              <li class="nav-item">
                <a href="blog.php" class="nav-link <?php if ($current_page == 'blog.php') echo 'active'; ?> link-light">
                  Blog
                </a>
              </li>
              <li class="nav-item">
                <a href="contact.php" class="nav-link <?php if ($current_page == 'contact.php') echo 'active'; ?> link-light">
                  Fale Conosco
                </a>
              </li>
            </ul>
          </div>
          <div class="ms-auto">
            <ul class="nav my-md-0 text-small">
                <?php if ($is_logged_in): ?>
                  <li class="nav-item dropdown">
                      <a class="nav-link dropdown-toggle <?php if ($current_page == 'account.php') echo 'active'; ?> link-light fw-bold" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                          <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($user_name); ?>
                      </a>
                      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserDropdown">
                          <li><a class="dropdown-item" href="account.php">Minha Conta</a></li>
                          <li><hr class="dropdown-divider"></li>
                          <li><a class="dropdown-item" href="account.php?logout=1">Sair</a></li>
                      </ul>
                  </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="register.php" class="nav-link <?php if ($current_page == 'register.php') echo 'active'; ?> link-light fw-bold">
                            Cadastrar
                        </a>
                    </li>
                    <li class="d-flex">
                        <div class="border-end h-50 m-auto"></div>
                    </li>
                    <li class="nav-item">
                        <a href="login.php" class="nav-link <?php if ($current_page == 'login.php') echo 'active'; ?> link-light fw-bold">
                            Entrar
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            
          </div>
        </div>
      </div>
      <div class="w-100 mb-3">
        <div class="d-flex m-auto d-flex align-items-center">
        <div class="me-5">
          <a href="index.php" class="d-flex align-items-center my-2 my-lg-0 me-lg-auto text-white text-decoration-none">
            <div class="d-flex align-items-center">
              <div><img src="assets/imgs/cartLogo.png" width="40" class="ms-2 me-2" /></div>
              <div class="text-center">
                <p class="fs-1 fw-bolder m-auto ms-2 luckiest-guy-regular ">XAIN</p>
              </div>
            </div>
          </a>
        </div>
        <div class="m-auto w-100 p-2 d-flex">
            <div class="input-group w-50 mx-auto position-relative"> 
                <form action="products.php" method="GET" class="d-flex w-100">
                    <input type="text" class="form-control" name="search_query" id="live-search-input" placeholder="Buscar..." aria-label="Buscar..." autocomplete="off"> 
                    <button class="btn btn-outline-secondary text-white" type="submit" id="button-addon2">
                      <i class="fa fa-search"></i>
                    </button>
                </form>
                <div id="live-search-results" class="list-group position-absolute w-100"></div>
            </div>
            <a href="cart.php" type="button" class="btn btn-outline-light ms-auto"><i class="fa fa-shopping-cart"></i>
                <span class="badge bg-danger ms-1">
                    <?php echo isset($_SESSION['total_cart_quantity']) ? $_SESSION['total_cart_quantity'] : 0; ?>
                </span>
            </a>
        </div>

      </div>
      </div>
    </div>
  </div>

</header>
