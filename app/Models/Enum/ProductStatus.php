<?php

namespace App\Models\Enum;

use App\Traits\EnumHelper;

enum ProductStatus: string
{
    use EnumHelper;

    case Draft = 'draft';
    case Trash = 'trash';
    case Published = 'published';
}
