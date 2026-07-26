<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/auth.php';

sessao_iniciar();
auth_logout();

redirecionar('login.php');
