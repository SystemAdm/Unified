<?php
namespace App\Enum;
enum RelationGuarded: string {
    case SON ='son';
    case DAUGHTER = 'daughter';
    case STEP_SON = 'step son';
    case STEP_DAUGHTER = 'step daughter';
    case GRAND_SON = 'grand son';
    case GRAND_DAUGHTER = 'grand daughter';
}
