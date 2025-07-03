<?php
namespace App\Enum;
enum Models: string {
    case USER = 'user';
    case ORGANIZATION = 'organization';
    case EMAIL = 'email';
    case LOCATION = 'location';
    case PERMISSION = 'permission';
    case ROLE = 'role';
    case PHONE = 'phone';
    case EVENT = 'event';
}
