<?php

namespace App\Enums\Enums;

enum CourseStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Pending = 'pending';
    case Rejected = 'rejected';
    
}
