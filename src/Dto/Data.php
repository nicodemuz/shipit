<?php

declare(strict_types=1);

namespace Cline\Shipit\Dto;

use InvalidArgumentException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

/**
 * Lightweight framework-agnostic DTO base (Spatie Laravel Data compatible API surface).
 */
abstract class Data
{
    /**
     * @param array<string, mixed>|self $payload
     */
    public static function from(mixed $payload): static
    {
        if ($payload instanceof static) {
            return $payload;
        }

        if (!is_array($payload)) {
            throw new InvalidArgumentException(sprintf(
                'Unable to create %s from %s.',
                static::class,
                get_debug_type($payload),
            ));
        }

        $reflection = new ReflectionClass(static::class);
        $constructor = $reflection->getConstructor();
        if (null === $constructor) {
            /** @var static */
            return $reflection->newInstance();
        }

        $arguments = [];
        foreach ($constructor->getParameters() as $parameter) {
            $arguments[] = self::resolveArgument($parameter, $payload);
        }

        /** @var static */
        return $reflection->newInstanceArgs($arguments);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $result = [];
        $reflection = new ReflectionClass($this);

        foreach ($reflection->getProperties() as $property) {
            if (!$property->isInitialized($this)) {
                continue;
            }

            $value = $property->getValue($this);
            if ($value instanceof Optional) {
                continue;
            }

            $result[$property->getName()] = self::normalizeValue($value);
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function resolveArgument(ReflectionParameter $parameter, array $payload): mixed
    {
        $name = $parameter->getName();
        $hasKey = array_key_exists($name, $payload);

        if (!$hasKey) {
            if (self::acceptsOptional($parameter)) {
                return Optional::create();
            }

            if ($parameter->allowsNull()) {
                return null;
            }

            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            throw new InvalidArgumentException(sprintf(
                'Missing required property "%s" for %s.',
                $name,
                $parameter->getDeclaringClass()?->getName() ?? 'DTO',
            ));
        }

        return self::castValue($parameter, $payload[$name]);
    }

    private static function castValue(ReflectionParameter $parameter, mixed $value): mixed
    {
        if ($value instanceof Optional) {
            return $value;
        }

        if (null === $value) {
            if (self::acceptsOptional($parameter)) {
                return Optional::create();
            }

            if ($parameter->allowsNull()) {
                return null;
            }

            throw new InvalidArgumentException(sprintf(
                'Property "%s" cannot be null for %s.',
                $parameter->getName(),
                $parameter->getDeclaringClass()?->getName() ?? 'DTO',
            ));
        }

        $collectionOf = self::collectionItemClass($parameter);
        if (null !== $collectionOf) {
            if ($value instanceof DataCollection) {
                return $value;
            }

            if (!is_array($value)) {
                throw new InvalidArgumentException(sprintf(
                    'Property "%s" expects an array for DataCollection.',
                    $parameter->getName(),
                ));
            }

            return DataCollection::create($value, $collectionOf);
        }

        $dataClass = self::dataClass($parameter);
        if (null !== $dataClass) {
            if ($value instanceof $dataClass) {
                return $value;
            }

            if (!is_array($value)) {
                throw new InvalidArgumentException(sprintf(
                    'Property "%s" expects an array or %s.',
                    $parameter->getName(),
                    $dataClass,
                ));
            }

            return $dataClass::from($value);
        }

        $type = $parameter->getType();
        if ($type instanceof ReflectionNamedType && 'array' === $type->getName() && is_array($value)) {
            return $value;
        }

        return $value;
    }

    private static function acceptsOptional(ReflectionParameter $parameter): bool
    {
        $type = $parameter->getType();
        if ($type instanceof ReflectionNamedType) {
            return Optional::class === $type->getName();
        }

        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $inner) {
                if ($inner instanceof ReflectionNamedType && Optional::class === $inner->getName()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return class-string|null
     */
    private static function collectionItemClass(ReflectionParameter $parameter): ?string
    {
        $attributes = $parameter->getAttributes(DataCollectionOf::class, ReflectionAttribute::IS_INSTANCEOF);
        if ([] === $attributes) {
            $class = $parameter->getDeclaringClass();
            if (null !== $class && $class->hasProperty($parameter->getName())) {
                $attributes = $class
                    ->getProperty($parameter->getName())
                    ->getAttributes(DataCollectionOf::class, ReflectionAttribute::IS_INSTANCEOF);
            }
        }

        if ([] === $attributes) {
            return null;
        }

        /** @var DataCollectionOf $attribute */
        $attribute = $attributes[0]->newInstance();

        return $attribute->class;
    }

    /**
     * @return class-string<Data>|null
     */
    private static function dataClass(ReflectionParameter $parameter): ?string
    {
        $type = $parameter->getType();
        $candidates = [];

        if ($type instanceof ReflectionNamedType) {
            $candidates[] = $type;
        } elseif ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $inner) {
                if ($inner instanceof ReflectionNamedType) {
                    $candidates[] = $inner;
                }
            }
        }

        foreach ($candidates as $candidate) {
            if ($candidate->isBuiltin()) {
                continue;
            }

            $name = $candidate->getName();
            if (is_subclass_of($name, Data::class) || Data::class === $name) {
                /** @var class-string<Data> $name */
                return $name;
            }
        }

        return null;
    }

    private static function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof Data) {
            return $value->toArray();
        }

        if ($value instanceof DataCollection) {
            return $value->toArray();
        }

        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $key => $item) {
                $normalized[$key] = self::normalizeValue($item);
            }

            return $normalized;
        }

        return $value;
    }
}
