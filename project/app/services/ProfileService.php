<?php

declare(strict_types=1);

namespace Project\Services;

use Project\Cache;
use Project\Models\CV;
use Project\Models\Profile;
use function Project\paginate;

final class ProfileService
{
    private Cache $cache;

    public function __construct()
    {
        $this->cache = new Cache();
    }

    public function paginated(array $filters, int $perPage, int $page): array
    {
        $total = Profile::count($filters);
        $pagination = paginate($total, $perPage, $page);
        $profiles = Profile::search($filters, $perPage, $pagination['offset']);
        return [$profiles, $pagination];
    }

    public function featured(int $limit = 6): array
    {
        return $this->cache->get('featured_profiles_' . $limit, fn () => Profile::allFeatured($limit), 120);
    }

    public function get(int $id): ?array
    {
        return Profile::find($id);
    }

    public function save(array $profileData, array $cvData): int
    {
        $profileId = Profile::save($profileData);
        CV::save($profileId, $cvData);
        $this->cache->forget('featured_profiles_6');
        return $profileId;
    }
}
