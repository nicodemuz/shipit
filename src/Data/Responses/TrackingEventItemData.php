<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shipit\Data\Responses;

use Cline\Shipit\Dto\Data;
use Cline\Shipit\Dto\Optional;

/**
 * A single tracking event from `/v1/query-tracking-events`.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class TrackingEventItemData extends Data
{
    /**
     * @param string                        $status      Carrier event status code (e.g. DELIVERED)
     * @param string                        $happenedAt  Event timestamp (API field `happened_at`)
     * @param array<string, string>|Optional $description Localized description (`fi` / `en` keys)
     * @param array<string, string>|Optional $information Localized information (`fi` / `en` keys)
     */
    public function __construct(
        public readonly string $status,
        public readonly string $happenedAt,
        public readonly array|Optional $description,
        public readonly array|Optional $information,
    ) {}

    /**
     * @param array<string, mixed> $payload
     */
    public static function from(mixed $payload): static
    {
        if (!is_array($payload)) {
            return parent::from($payload);
        }

        if (isset($payload['happened_at']) && !isset($payload['happenedAt'])) {
            $payload['happenedAt'] = $payload['happened_at'];
        }

        return parent::from($payload);
    }
}
