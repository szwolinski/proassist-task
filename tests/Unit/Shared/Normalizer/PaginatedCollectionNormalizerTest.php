<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Normalizer;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Shared\Normalizer\PaginatedCollectionNormalizer;
use App\Tests\Fake\FakePaginator;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PaginatedCollectionNormalizerTest extends TestCase
{
    private PaginatedCollectionNormalizer $normalizer;
    private NormalizerInterface $innerNormalizerMock;

    protected function setUp(): void
    {
        $this->innerNormalizerMock = $this->createMock(NormalizerInterface::class);
        $this->normalizer = new PaginatedCollectionNormalizer();
        $this->normalizer->setNormalizer($this->innerNormalizerMock);
    }

    public function test_it_supports_only_json_format(): void
    {
        $paginator = self::createStub(PaginatorInterface::class);
        $context = [PaginatedCollectionNormalizer::PAGINATED_COLLECTION => true];

        $this->assertFalse($this->normalizer->supportsNormalization($paginator, 'xml', $context));
    }

    public function test_does_not_support_if_already_called(): void
    {
        $paginator = self::createStub(PaginatorInterface::class);
        $context = [
            PaginatedCollectionNormalizer::PAGINATED_COLLECTION => true,
            'PAGINATED_COLLECTION_NORMALIZER_ALREADY_CALLED' => true
        ];

        $this->assertFalse($this->normalizer->supportsNormalization($paginator, 'json', $context));
    }

    public function test_does_not_support_without_paginated_collection_flag(): void
    {
        $paginator = self::createStub(PaginatorInterface::class);

        $this->assertFalse($this->normalizer->supportsNormalization($paginator, 'json', []));
    }

    public function test_does_not_support_non_paginator_data(): void
    {
        $context = [PaginatedCollectionNormalizer::PAGINATED_COLLECTION => true];

        $this->assertFalse($this->normalizer->supportsNormalization(new stdClass(), 'json', $context));
    }

    public function test_supports_valid_json_paginator_request(): void
    {
        $paginator = self::createStub(PaginatorInterface::class);
        $context = [PaginatedCollectionNormalizer::PAGINATED_COLLECTION => true];

        $this->assertTrue($this->normalizer->supportsNormalization($paginator, 'json', $context));
    }

    public function test_normalize_formats_data_and_meta_correctly(): void
    {
        $fakePaginator = new FakePaginator(
            items: [new stdClass(), new stdClass()],
            totalItems: 50.0,
            currentPage: 1.0,
            itemsPerPage: 10.0
        );

        $this->innerNormalizerMock->expects($this->exactly(2))
            ->method('normalize')
            ->willReturnOnConsecutiveCalls(
                ['id' => 1, 'name' => 'Tech 1'],
                ['id' => 2, 'name' => 'Tech 2']
            );

        $context = [PaginatedCollectionNormalizer::PAGINATED_COLLECTION => true];

        $result = $this->normalizer->normalize($fakePaginator, 'json', $context);

        $this->assertSame([
            'data' => [
                ['id' => 1, 'name' => 'Tech 1'],
                ['id' => 2, 'name' => 'Tech 2'],
            ],
            'meta' => [
                'totalItems' => 50,
                'currentPage' => 1,
                'itemsPerPage' => 10,
            ]
        ], $result);
    }
}
