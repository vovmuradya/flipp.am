@php
    /** @var \App\Models\Listing $listing */
    $vehicle = $listing->vehicleDetail;
    $price = $listing->price;
    $currency = $listing->currency ?: 'USD';
    $mileage = $vehicle?->mileage;
    $year = $vehicle?->year;
    $make = $vehicle?->make;
    $model = $vehicle?->model;
    $odometerUnit = 'MI';

    $structured = [
        '@context' => 'https://schema.org',
        '@type' => 'Vehicle',
        'name' => $listing->title,
        'description' => $listing->description,
        'brand' => $make ?: null,
        'model' => $model ?: null,
        'vehicleConfiguration' => trim(($year ? $year . ' ' : '') . ($make ?: '') . ' ' . ($model ?: '')),
        'vehicleEngine' => [
            '@type' => 'EngineSpecification',
            'engineDisplacement' => $vehicle?->engine_displacement_cc ? $vehicle->engine_displacement_cc . ' cc' : null,
        ],
        'vehicleTransmission' => $vehicle?->transmission ?: null,
        'bodyType' => $vehicle?->body_type ?: null,
        'fuelType' => $vehicle?->fuel_type ?: null,
        'color' => $vehicle?->exterior_color ?: null,
        'productionDate' => $year,
        'offers' => [
            '@type' => 'Offer',
            'price' => $price,
            'priceCurrency' => $currency,
            'availability' => 'https://schema.org/InStock',
            'url' => route('listings.show', $listing),
        ],
    ];

    if ($mileage !== null) {
        $structured['mileageFromOdometer'] = [
            '@type' => 'QuantitativeValue',
            'value' => $mileage,
            'unitCode' => $odometerUnit,
        ];
    }

    $photos = [];
    if ($listing->relationLoaded('media')) {
        $mediaItems = $listing->media->whereIn('collection_name', ['images', 'auction_photos']);
        foreach ($mediaItems as $media) {
            $photos[] = route('media.show', ['media' => $media->id]);
        }
    }
    if (empty($photos) && !empty($listing->auction_photo_urls)) {
        $urls = is_array($listing->auction_photo_urls)
            ? $listing->auction_photo_urls
            : json_decode($listing->auction_photo_urls, true);
        if (is_array($urls)) {
            $photos = array_slice(array_filter($urls, fn ($u) => is_string($u) && trim($u) !== ''), 0, 10);
        }
    }
    if (!empty($photos)) {
        $structured['image'] = $photos;
    }

    $structured = array_filter($structured, fn ($v) => $v !== null && $v !== '');
@endphp

<script type="application/ld+json">
{!! json_encode($structured, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) !!}
</script>
