<?php
namespace App\Enum;
enum Access: string {
    case SHOW = 'show';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case ADMIN = 'admin';
    case CREATE = 'create';
    case INDEX = 'index';
}
