<?php

namespace App\Models\Enums;

enum PartnerType: string
{
    case Corporate = 'corporate';
    case Church = 'church';
    case Foundation = 'foundation';
}
