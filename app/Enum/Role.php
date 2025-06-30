<?php

namespace App\Enum;

enum Role: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case MEMBER = 'member';
    case MODERATOR = 'moderator';
    case GUARDIAN = 'guardian';
    case GUEST = 'guest';

}
