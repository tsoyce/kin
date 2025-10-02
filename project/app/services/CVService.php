<?php

declare(strict_types=1);

namespace Project\Services;

use Project\Models\CV;

final class CVService
{
    public function get(int $profileId): ?array
    {
        return CV::findByProfile($profileId);
    }
}
