<?php

APP::route()->
        addCAction('AdminLoginAuthorize', 'pl', 'autoryzacja')->
        addCAction('AdminLogOut', 'pl', 'wyloguj-sie');

class AdminAuthController extends Controller {

    public function AdminLoginAuthorize() {
        if (!Model::validateToken()) {
            Help::redirect('Admin', 'AdminAuth', null, 'error,token');
        }
        $password = Help::serverVar('post', 'password');
        $email = Help::serverVar('post', 'email');
        $salt = APP::db()->
                create('SELECT salt FROM user WHERE email = :email AND `active` = 1')->
                bind($email, 'email')->
                execute();

        if (!empty($salt)) {
            $password = Help::saltPassword($password, $salt[0]['salt']);
            $user = APP::db()->
                    create('SELECT * FROM user WHERE password = :password')->
                    bind($password, 'password')->
                    execute();
        }

        if (!empty($user)) {
            $user = $user[0];
            User::logIn();
            User::setLevel($user['level']);
            User::setUserField('id', $user['id']);
            User::setUserField('first_name', $user['first_name']);
            User::setUserField('last_name', $user['last_name']);
            User::setUserField('email', $user['email']);

            Help::redirect('Admin');
        } else {
            $_SESSION['wrongEmail'] = $email;
            Help::redirect('Admin', 'AdminAuth', null, 'error');
        }
    }

    public function AdminLogOut() {
        User::logOut();
        Help::redirect('Admin', 'AdminAuth');
    }

}
