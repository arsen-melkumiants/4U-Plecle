<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Ion Auth Lang - Russian (UTF-8)
*
* Author: Ben Edmunds
* 		  ben.edmunds@gmail.com
*         @benedmunds
* Translation:  Petrosyan R.
*             for@petrosyan.rv.ua
*
* Location: http://github.com/benedmunds/ion_auth/
*
* Created:  03.26.2010
*
* Description:  Russian language file for Ion Auth messages and errors
*
*/

// Account Creation
$lang['account_creation_successful']         = 'Учетная запись успешно создана';
$lang['account_creation_unsuccessful']       = 'Невозможно создать учетную запись';
$lang['account_creation_duplicate_email']    = 'Электронная почта используется или некорректна';
$lang['account_creation_duplicate_username'] = 'Имя пользователя существует или некорректно';

// Password
$lang['password_change_successful']          = 'Пароль успешно изменен';
$lang['password_change_unsuccessful']        = 'Пароль невозможно изменить';
$lang['forgot_password_successful']          = 'Пароль сброшен. На электронную почту отправлено сообщение';
$lang['forgot_password_unsuccessful']        = 'Невозможен сброс пароля';

// Activation
$lang['activate_successful']                 = 'Учетная запись активирована';
$lang['activate_unsuccessful']               = 'Не удалось активировать учетную запись';
$lang['deactivate_successful']               = '';//'Учетная запись деактивирована';
$lang['deactivate_unsuccessful']             = 'Невозможно деактивировать учетную запись';
$lang['activation_email_successful']         = 'Сообщение об активации отправлено';
$lang['activation_email_unsuccessful']       = 'Сообщение об активации невозможно отправить';

// Login / Logout
$lang['login_successful']                    = 'Авторизация прошла успешно';
$lang['login_unsuccessful']                  = 'Логин или пароль неверены';
$lang['login_unsuccessful_not_active'] 		 = 'Учетная записть неактивирована';
$lang['logout_successful']                   = 'Успешный выход';

// Account Changes
$lang['update_successful']                   = 'Учетная запись успешно обновлена';
$lang['update_unsuccessful']                 = 'Невозможно обновить учетную запись';
$lang['delete_successful']                   = 'Учетная запись удалена';
$lang['delete_unsuccessful']                 = 'Невозможно удалить учетную запись';

// Activation Email
$lang['email_activation_subject']            = 'Активация учетной записи';
$lang['email_activate_heading']              = 'Активация аккаунт для %s';
$lang['email_activate_subheading']           = 'Пожалуйста, нажмите на эту ссылку, чтобы %s.';
$lang['email_activate_link']                 = 'Активировать аккаунт';

// Forgot Password Email
$lang['email_forgotten_password_subject']    = 'Забыли пароль';
$lang['email_forgot_password_heading']       = 'Сбросить пароль для %s';
$lang['email_forgot_password_subheading']    = 'Нажмите пожалуйста на ссылке %s.';
$lang['email_forgot_password_link']          = 'Сбросить пароль';

// New Password Email
$lang['email_new_password_subject']          = 'Новый пароль';
$lang['email_new_password_heading']          = 'Новый пароль для %s';
$lang['email_new_password_subheading']       = 'Ваш пароль был сброшен на: %s';
