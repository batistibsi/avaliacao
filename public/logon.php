<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>avaliacao</title>
    <!-- CPS Front-end CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-sweetalert/1.0.1/sweetalert.min.css">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>

    <style>
        :root {
            --dt-primary: #c02f5a;
            --dt-active: color-mix(in srgb, var(--dt-primary) 80%, black 40%);
        }
    </style>

    <link rel="stylesheet" href="./styles/login.css">
    <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
</head>

<body class="bg-light">
    <!-- Login 9 - Bootstrap Brain Component -->
    <section class="mt-5">
        <div class="container mt-5">
            <div class="row gy-4 align-items-center">
                <div class="col-12 col-md-6 col-xl-7">
                    <div class="d-flex justify-content-center">
                        <div class="col-12 col-xl-9">
                            <h2 class="h1 mb-4 text-center">GESTÃO DE AVALIAÇÕES</h2>
                            <hr class="border-primary-subtle mb-4">
                            <img class="img-fluid mb-4" loading="lazy" src="./images/logo.png" width="700" height="457" alt="Logo do SISTEMA">
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-5">
                    <div class="card border-0 shadow rounded">
                        <div class="card-body p-3 p-md-4 p-xl-5">
                            <div class="row">
                                <div class="col-12">
                                    <div class="h1 mb-1">
                                        <h3>Iniciar sessão</h3>
                                    </div>
                                </div>
                            </div>
                            <form method="POST" action="../../login.php">
                                <div class="row gy-3 overflow-hidden">
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" name="login" id="login" placeholder="Nome de Usuário" required>
                                            <label for="login" class="form-label">Nome de Usuário</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="password" class="form-control" name="senha" id="senha" value="" placeholder="Password" required>
                                            <label for="senha" class="form-label">Senha</label>
                                        </div>
                                    </div>

                                    <div class="text-center h-captcha" data-sitekey="5a68b526-352a-4b9e-b65e-0974da067b7a"></div>

                                    <div class="col-12">
                                        <div class="d-grid">
                                            <button class="btn btn-success btn-lg" type="submit">Entrar</button>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <a href='./esqueci.php' class="text-secondary">
                                            Esqueci minha senha
                                        </a>
                                    </div>
                                </div>
                            </form>
                            <div class="row">
                                <div class="col-12">
                                    <div class="text-danger d-flex gap-2 gap-md-4 flex-column flex-md-row justify-content-center mt-4">
                                        <span><?= isset($_REQUEST['msg']) ? $_REQUEST['msg'] : '' ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="d-flex flex-wrap justify-content-between align-items-center py-3 mt-5 border-top">
                <div class="col-md-4 d-flex align-items-center">
                    <span class="mb-md-0 text-muted">&nbsp;&nbsp;&copy; <?= date('Y') ?> BemFeito Sistemas</span>
                </div>

                <!--ul class="nav col-md-4 justify-content-end list-unstyled d-flex">
                    <li class="ms-3">
                        <a class="text-muted" href="#">
                            <svg class="bi" width="24" height="24">
                                <use xlink:href="#twitter" />
                            </svg>
                        </a>
                    </li>
                    <li class="ms-3">
                        <a class="text-muted" href="#">
                            <svg class="bi" width="24" height="24">
                                <use xlink:href="#instagram" />
                            </svg>
                        </a>
                    </li>
                    <li class="ms-3">
                        <a class="text-muted" href="#">
                            <svg class="bi" width="24" height="24">
                                <use xlink:href="#facebook" />
                            </svg>
                        </a>
                    </li>
                </ul-->
            </footer>
        </div>
    </section>
</body>

</html>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-sweetalert/1.0.1/sweetalert.min.js"></script>
<script>
    $(document).ready(function() {
        $("form").on('submit', function(e) {
            const captcha = $("[name='h-captcha-response']").val();
            if (captcha === "") {
                e.preventDefault();
                swal({
                    title: "Atenção!",
                    text: "Por favor, complete o hCaptcha.",
                    type: "error"
                });
                return;
            }
        });
    });
</script>