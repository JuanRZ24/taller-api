<?php

namespace App\Enums;

enum ContentType: string
{
    case Text = 'text';
    case File = 'file';
    case Link = 'link';
    case Video = 'video';
}


