<?php

declare(strict_types=1);

namespace App\Shared\Normalizer;

use ApiPlatform\State\Pagination\PaginatorInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PaginatedCollectionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const string PAGINATED_COLLECTION = 'paginated_collection';
    private const string ALREADY_CALLED = 'PAGINATED_COLLECTION_NORMALIZER_ALREADY_CALLED';

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if ($format !== 'json') {
            return false;
        }

        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        if (empty($context[self::PAGINATED_COLLECTION])) {
            return false;
        }

        return $data instanceof PaginatorInterface;
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array{
     * data: array<int, mixed>,
     * meta: array{
     * totalItems: int,
     * currentPage: int,
     * itemsPerPage: int
     * }
     * }
     *
     * @throws ExceptionInterface
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;

        $items = [];
        foreach ($data as $item) {
            $items[] = $this->normalizer->normalize($item, $format, $context);
        }

        return [
            'data' => $items,
            'meta' => [
                'totalItems' => (int) $data->getTotalItems(),
                'currentPage' => (int) $data->getCurrentPage(),
                'itemsPerPage' => (int) $data->getItemsPerPage(),
            ]
        ];
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            PaginatorInterface::class => true,
        ];
    }
}
