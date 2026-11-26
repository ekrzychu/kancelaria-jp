<?php

namespace App\Content;

enum JobStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
    case Draft = 'draft';
}
