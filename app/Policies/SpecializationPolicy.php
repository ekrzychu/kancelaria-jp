<?php

namespace App\Policies;

use App\Enums\ContentSection;

final class SpecializationPolicy extends SectionContentPolicy
{
    protected const ContentSection SECTION = ContentSection::Specializations;
}
