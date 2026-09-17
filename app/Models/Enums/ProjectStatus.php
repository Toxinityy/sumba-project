<?php

namespace App\Models\Enums;

enum ProjectStatus: string
{
    case Planned = 'planned';
    case Underway = 'underway';
    case Complete = 'complete';
}
