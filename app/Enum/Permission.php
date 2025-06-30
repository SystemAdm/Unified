<?php

namespace App\Enum;

enum Permission: string
{
    case INDEX_ORGANIZATION = 'index_organization';
    case CREATE_ORGANIZATION = 'create_organization';
    case UPDATE_ORGANIZATION = 'update_organization';
    case DELETE_ORGANIZATION = 'delete_organization';
    case ADMIN_ORGANIZATION = 'admin_organization';

    case INDEX_USER = 'index_user';
    case CREATE_USER = 'create_user';
    case UPDATE_USER = 'update_user';
    case DELETE_USER = 'delete_user';
    case ADMIN_USER = 'admin_user';


}
