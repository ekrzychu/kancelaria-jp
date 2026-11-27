<?php

namespace App\Policies;

use App\Enums\ContentSection;

final class TeamMemberPolicy extends SectionContentPolicy
{
    protected const ContentSection SECTION = ContentSection::Team;
}
