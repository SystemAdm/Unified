<?php

namespace App\Enum;

enum Equipment: string
{
    case PC = 'PC';
    case XBOX = 'Xbox';
    case XBOX_ONE = 'Xbox One';
    case XBOX_SERIES_X = 'Xbox Series X';
    case XBOX_SERIES_S = 'Xbox Series S';
    case PS = 'PlayStation';
    case PS2 = 'PlayStation 2';
    case PS3 = 'PlayStation 3';
    case PS4 = 'PlayStation 4';
    case PS5 = 'PlayStation 5';
    case NS = 'Nintendo Switch';
    case NS2 = 'Nintendo Switch 2';
    case VR = 'VR';
    case CARD = 'Card';
    case TABLETOP = 'Tabletop';
    case OTHER = 'Other';
}
