<?php
namespace App\Enum;
enum AnnouncementType: string {
    case WARNING = 'warning';
    case DANGER = 'danger';
    case INFO = 'info';
    case DEFAULT = 'default';
    case PRIMARY = 'primary';
    case SECONDARY = 'secondary';
}
