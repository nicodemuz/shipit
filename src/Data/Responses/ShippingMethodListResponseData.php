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
use Throwable;

/**
 * Collection of catalog shipping methods from `GET /v1/list-methods`.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ShippingMethodListResponseData extends Data
{
    /**
     * @param DataCollection<int, ListedShippingMethodData> $data
     */
    public function __construct(
        #[DataCollectionOf(ListedShippingMethodData::class)]
        public readonly DataCollection $data,
    ) {}

    /**
     * @param array<string, mixed>|list<mixed> $payload
     */
    public static function from(mixed $payload): static
    {
        if (!is_array($payload)) {
            return parent::from(['data' => []]);
        }

        $items = [];
        if (array_is_list($payload)) {
            $items = $payload;
        } elseif (isset($payload['data']) && is_array($payload['data'])) {
            $items = $payload['data'];
        }

        $parsed = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $serviceId = isset($item['serviceId']) && is_string($item['serviceId']) ? $item['serviceId'] : '';
            $name = isset($item['name']) && is_string($item['name']) ? $item['name'] : null;
            $carrier = isset($item['carrier']) && is_string($item['carrier']) ? $item['carrier'] : null;

            if (
                '' === $serviceId
                || null === $name
                || null === $carrier
                || !array_key_exists('domesticDeliveries', $item)
                || !array_key_exists('homeDelivery', $item)
                || !array_key_exists('pickUpPoints', $item)
            ) {
                // Incomplete catalog rows (common on partial/test payloads) are skipped.
                continue;
            }

            try {
                $parsed[] = ListedShippingMethodData::from([
                    'serviceId' => $serviceId,
                    'name' => $name,
                    'carrier' => $carrier,
                    'domesticDeliveries' => (bool) $item['domesticDeliveries'],
                    'homeDelivery' => (bool) $item['homeDelivery'],
                    'pickUpPoints' => (bool) $item['pickUpPoints'],
                    'logo' => isset($item['logo']) && is_string($item['logo']) ? $item['logo'] : Optional::create(),
                ]);
            } catch (Throwable) {
                continue;
            }
        }

        return new self(DataCollection::create($parsed, ListedShippingMethodData::class));
    }
}
