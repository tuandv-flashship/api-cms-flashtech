<?php

namespace App\Containers\AppSection\Member\Enums;

enum MemberActivityAction: string
{
    case REGISTER = 'register';
    case LOGIN = 'login';
    case LOGOUT = 'logout';
    case UPDATE_SETTING = 'update_setting';
    case CHANGED_AVATAR = 'changed_avatar';
    case UPDATE_SECURITY = 'update_security';
    case REQUEST_PASSWORD_RESET = 'request_password_reset';
    case RESET_PASSWORD = 'reset_password';
    case VERIFY_EMAIL = 'verify_email';
    case DELETE = 'delete';
    case ADMIN_CREATE = 'admin_create';
    case ADMIN_UPDATE_EMAIL = 'admin_update_email';
    case ADMIN_UPDATE_USERNAME = 'admin_update_username';
}

