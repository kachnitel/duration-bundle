<?php

namespace Kachnitel\DurationBundle\Twig\Extension;

use Kachnitel\DurationBundle\Service\DurationUtil;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Twig extension providing duration-related filters.
 *
 * Available filters:
 * - duration: Format seconds as human-readable duration
 * - toSeconds: Parse duration string to seconds
 * - hhmm: Format seconds as HH:MM
 *
 * Usage examples:
 *   {{ task.duration|duration }}              {# Output: "2h 30m" #}
 *   {{ task.duration|duration(false) }}       {# Output: "2 hours 30 minutes" #}
 *   {{ task.duration|hhmm }}                  {# Output: "02:30" #}
 *   {{ "2h 30m"|toSeconds }}                  {# Output: 9000 #}
 */
class DurationExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('duration', [DurationUtil::class, 'toString'], ['is_safe' => ['html']]),
            new TwigFilter('toSeconds', [DurationUtil::class, 'toSeconds'], ['is_safe' => ['html']]),
            new TwigFilter('hhmm', [DurationUtil::class, 'toHhMm'], ['is_safe' => ['html']])
        ];
    }
}
