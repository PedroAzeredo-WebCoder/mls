<?php

// LoginController.php
class LoginController
{
    public function login()
    {
        // Nenhuma verificação de acesso necessária para a página de login
        // ...

        // Restante da lógica para a página de login
        // ...
    }
}

// HomeController.php
class HomeController
{
    public function index()
    {
        // Nenhuma verificação de acesso necessária para a página inicial
        // ...

        // Restante da lógica para a página inicial
        // ...
    }
}

// UserController.php
class UserController
{
    public function list()
    {
        // Verifique se o usuário tem permissão para acessar esta página
        if (!checkAccess('usuarioList')) {
            // Exiba uma mensagem de erro ou redirecione para uma página de acesso negado
            // ...
        }

        redirect('usuarioList.php');
    }

    public function create()
    {
        // Verifique se o usuário tem permissão para criar usuários
        if (!checkAccess('usuariosCad')) {
            // Exiba uma mensagem de erro ou redirecione para uma página de acesso negado
            // ...
        }

        // Restante da lógica para a ação 'create' da rota '/usuarios/cadastro'
        // ...
        redirect('usuarioCad.php');
    }
}
