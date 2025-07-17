<?php
namespace App\Enum;
enum RelationGuardian: string {
    case FATHER = 'father';
    case MOTHER = 'mother';
    case UNCLE = 'uncle';
    case AUNT = 'aunt';
    case STEP_FATHER = 'step father';
    case STEP_MOTHER = 'step mother';
    case GRAND_FATHER = 'grand father';
    case GRAND_MOTHER = 'grand mother';
}
