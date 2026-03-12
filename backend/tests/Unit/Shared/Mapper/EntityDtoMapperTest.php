<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Mapper;

use App\Shared\Infrastructure\Mapper\EntityDtoMapper;
use App\Shared\Infrastructure\Mapper\MappingConfigLoaderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

enum FakeStatus: string
{
    case Active = 'active';
    case Archived = 'archived';
}

class EntityDtoMapperTest extends TestCase
{
    private MappingConfigLoaderInterface&MockObject $loader;
    private EntityDtoMapper $mapper;

    protected function setUp(): void
    {
        $this->loader = $this->createMock(MappingConfigLoaderInterface::class);
        $this->mapper = new EntityDtoMapper($this->loader);
    }

    // -------------------------------------------------------------------------
    // toDto — champs scalaires
    // -------------------------------------------------------------------------

    public function testToDtoThrowsWhenNoConfig(): void
    {
        $this->loader->method('getConfigForClass')->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->mapper->toDto(new \stdClass(), \stdClass::class);
    }

    public function testToDtoMapsStringViaGetGetter(): void
    {
        $entity = new class {
            public function getTitle(): string
            {
                return 'Hello world';
            }
        };
        $dto = new class {
            public string $title = '';
        };

        $this->givenConfig($dto::class, $entity::class, [
            'title' => ['operations' => ['read']],
        ]);

        $result = $this->mapper->toDto($entity, $dto::class);

        $this->assertSame('Hello world', $result->title);
    }

    public function testToDtoMapsBoolViaIsGetter(): void
    {
        $entity = new class {
            public function isCompleted(): bool
            {
                return true;
            }
        };
        $dto = new class {
            public bool $isCompleted = false;
        };

        $this->givenConfig($dto::class, $entity::class, [
            'isCompleted' => ['operations' => ['read']],
        ]);

        $result = $this->mapper->toDto($entity, $dto::class);

        $this->assertTrue($result->isCompleted);
    }

    public function testToDtoMapsUuidViaDirectMethodCall(): void
    {
        $entity = new class {
            public function id(): string
            {
                return 'abc-123';
            }
        };
        $dto = new class {
            public string $id = '';
        };

        $this->givenConfig($dto::class, $entity::class, [
            'id' => ['operations' => ['read']],
        ]);

        $result = $this->mapper->toDto($entity, $dto::class);

        $this->assertSame('abc-123', $result->id);
    }

    // -------------------------------------------------------------------------
    // toDto — transforms
    // -------------------------------------------------------------------------

    public function testToDtoMapsDatetimeWithArrayTransform(): void
    {
        $date = new \DateTimeImmutable('2024-01-15T10:00:00+00:00');

        $entity = new class($date) {
            public function __construct(private readonly \DateTimeImmutable $createdAt)
            {
            }

            public function getCreatedAt(): \DateTimeImmutable
            {
                return $this->createdAt;
            }
        };
        $dto = new class {
            public string $createdAt = '';
        };

        $this->givenConfig($dto::class, $entity::class, [
            'createdAt' => [
                'operations' => ['read'],
                'transform' => ['type' => 'datetime', 'format' => \DateTimeInterface::ATOM],
            ],
        ]);

        $result = $this->mapper->toDto($entity, $dto::class);

        $this->assertSame($date->format(\DateTimeInterface::ATOM), $result->createdAt);
    }

    public function testToDtoMapsDatetimeWithStringTransform(): void
    {
        $date = new \DateTimeImmutable('2024-06-01T12:00:00+00:00');

        $entity = new class($date) {
            public function __construct(private readonly \DateTimeImmutable $dueDate)
            {
            }

            public function getDueDate(): \DateTimeImmutable
            {
                return $this->dueDate;
            }
        };
        $dto = new class {
            public string $dueDate = '';
        };

        $this->givenConfig($dto::class, $entity::class, [
            'dueDate' => [
                'operations' => ['read'],
                'transform' => 'datetime:ATOM',
            ],
        ]);

        $result = $this->mapper->toDto($entity, $dto::class);

        $this->assertSame($date->format(\DateTimeInterface::ATOM), $result->dueDate);
    }

    public function testToDtoMapsEnumToScalarValue(): void
    {
        $entity = new class {
            public function getStatus(): FakeStatus
            {
                return FakeStatus::Active;
            }
        };
        $dto = new class {
            public string $status = '';
        };

        $this->givenConfig($dto::class, $entity::class, [
            'status' => [
                'operations' => ['read'],
                'transform' => ['type' => 'enum', 'class' => FakeStatus::class],
            ],
        ]);

        $result = $this->mapper->toDto($entity, $dto::class);

        $this->assertSame('active', $result->status);
    }

    // -------------------------------------------------------------------------
    // toDto — comportements de skip
    // -------------------------------------------------------------------------

    public function testToDtoSkipsFieldAndPreservesDefaultWhenGetterNotFound(): void
    {
        $entity = new class {};
        $dto = new class {
            public bool $isCompleted = false;
        };

        $this->givenConfig($dto::class, $entity::class, [
            'isCompleted' => ['operations' => ['read']],
        ]);

        $result = $this->mapper->toDto($entity, $dto::class);

        $this->assertFalse($result->isCompleted);
    }

    public function testToDtoSkipsWriteOnlyField(): void
    {
        $entity = new class {
            public function getTitle(): string
            {
                return 'should not appear';
            }
        };
        $dto = new class {
            public string $title = 'default';
        };

        $this->givenConfig($dto::class, $entity::class, [
            'title' => ['operations' => ['write']],
        ]);

        $result = $this->mapper->toDto($entity, $dto::class);

        $this->assertSame('default', $result->title);
    }

    public function testToDtoSkipsNullableFieldWhenGetterNotFoundWithoutOverwritingDefault(): void
    {
        $entity = new class {};
        $dto = new class {
            public ?string $dueDate = null;
        };

        $this->givenConfig($dto::class, $entity::class, [
            'dueDate' => ['operations' => ['read']],
        ]);

        $result = $this->mapper->toDto($entity, $dto::class);

        $this->assertNull($result->dueDate);
    }

    // -------------------------------------------------------------------------
    // toEntity
    // -------------------------------------------------------------------------

    public function testToEntityThrowsWhenNoConfig(): void
    {
        $this->loader->method('getConfigForClass')->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->mapper->toEntity(new \stdClass(), new \stdClass());
    }

    public function testToEntityMapsScalarViaSetter(): void
    {
        $dto = new class {
            public string $title = 'My task';
        };

        $entity = new class {
            public string $title = '';

            public function setTitle(string $t): void
            {
                $this->title = $t;
            }
        };

        $this->givenConfig($dto::class, $entity::class, [
            'title' => ['operations' => ['write']],
        ]);

        $this->mapper->toEntity($dto, $entity);

        $this->assertSame('My task', $entity->title);
    }

    public function testToEntitySkipsReadOnlyField(): void
    {
        $dto = new class {
            public string $id = 'some-uuid';
        };

        $entity = new class {
            public string $id = 'original';

            public function setId(string $id): void
            {
                $this->id = $id;
            }
        };

        $this->givenConfig($dto::class, $entity::class, [
            'id' => ['operations' => ['read']],
        ]);

        $this->mapper->toEntity($dto, $entity);

        $this->assertSame('original', $entity->id);
    }

    public function testToEntityAppliesReverseEnumTransform(): void
    {
        $dto = new class {
            public string $status = 'archived';
        };

        $entity = new class {
            public ?FakeStatus $status = null;

            public function setStatus(FakeStatus $s): void
            {
                $this->status = $s;
            }
        };

        $this->givenConfig($dto::class, $entity::class, [
            'status' => [
                'operations' => ['write'],
                'transform' => ['type' => 'enum', 'class' => FakeStatus::class],
            ],
        ]);

        $this->mapper->toEntity($dto, $entity);

        $this->assertSame(FakeStatus::Archived, $entity->status);
    }

    public function testToEntityPassesNullThroughToNullableSetter(): void
    {
        $dto = new class {
            public ?string $status = null;
        };

        $entity = new class {
            public ?FakeStatus $status = FakeStatus::Active;

            public function setStatus(?FakeStatus $s): void
            {
                $this->status = $s;
            }
        };

        $this->givenConfig($dto::class, $entity::class, [
            'status' => [
                'operations' => ['write'],
                'transform' => ['type' => 'enum', 'class' => FakeStatus::class],
            ],
        ]);

        $this->mapper->toEntity($dto, $entity);

        $this->assertNull($entity->status);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * @param array<string, array<string, mixed>> $fields
     */
    private function givenConfig(string $dtoClass, string $entityClass, array $fields): void
    {
        $this->loader
            ->method('getConfigForClass')
            ->with($dtoClass)
            ->willReturn(['entity' => $entityClass, 'fields' => $fields]);
    }
}
