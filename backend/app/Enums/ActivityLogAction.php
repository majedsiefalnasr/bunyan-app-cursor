<?php

namespace App\Enums;

enum ActivityLogAction: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case Viewed = 'viewed';
    case Exported = 'exported';
}
