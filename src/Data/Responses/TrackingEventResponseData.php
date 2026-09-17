<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shipit\Data\Responses;

use Cline\Shipit\Dto\Data;
use Cline\Shipit\Dto\DataCollection;
use Cline\Shipit\Dto\DataCollectionOf;
use Cline\Shipit\Dto\Optional;

/**
 * Response from `/v1/query-tracking-events`.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class TrackingEventResponseData extends Data
{
    /**
     * @param DataCollection<int, TrackingEventItemData> $data Tracking events for the queried shipment
     * @param Optional|string                            $trackingNumber Optional tracking number echo
     * @param Optional|string                            $status         Optional high-level status
     * @param Optional|string                            $estimatedDelivery Optional ETA
     */
    public function __construct(
        #[DataCollectionOf(TrackingEventItemData::class)]
        public readonly DataCollection $data,
        public readonly string|Optional $trackingNumber,
        public readonly string|Optional $status,
        public readonly string|Optional $estimatedDelivery,
    ) {}

    /**
     * @param array<string, mixed> $payload
     */
    public static function from(mixed $payload): static
    {
        if (!is_array($payload)) {
            return parent::from($payload);
        }

        // Legacy / events endpoint shape: { trackingNumber, events: [...] }
        if (!isset($payload['data']) && isset($payload['events']) && is_array($payload['events'])) {
            $payload['data'] = $payload['events'];
        }

        if (!isset($payload['data']) || !is_array($payload['data'])) {
            $payload['data'] = [];
        }

        return parent::from($payload);
    }
}
