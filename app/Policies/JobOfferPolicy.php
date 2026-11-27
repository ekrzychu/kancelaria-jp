<?php

namespace App\Policies;

use App\Enums\ContentSection;

final class JobOfferPolicy extends SectionContentPolicy
{
    protected const ContentSection SECTION = ContentSection::Career;
}
