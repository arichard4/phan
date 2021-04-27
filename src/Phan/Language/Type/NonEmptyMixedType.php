<?php

declare(strict_types=1);

namespace Phan\Language\Type;

use Phan\CodeBase;
use Phan\Language\Context;
use Phan\Language\Type;

/**
 * Represents the PHPDoc `non-empty-mixed` type, which can cast to/from any non-empty type and is truthy.
 *
 * For purposes of analysis, there's usually no difference between mixed and nullable mixed.
 * @phan-pure
 */
final class NonEmptyMixedType extends MixedType
{
    use NativeTypeTrait;

    /** @phan-override */
    public const NAME = 'non-empty-mixed';

    public function canCastToType(Type $type): bool
    {
        return $type->isPossiblyTruthy() || ($this->is_nullable && $type->is_nullable);
    }

    /**
     * @override
     */
    public function canCastToTypeWithoutConfig(Type $type): bool
    {
        return $this->canCastToType($type);
    }

    /**
     * @param Type[] $target_type_set 1 or more types @phan-unused-param
     * @override
     */
    public function canCastToAnyTypeInSet(array $target_type_set): bool
    {
        foreach ($target_type_set as $t) {
            if ($this->canCastToType($t)) {
                return true;
            }
        }
        return (bool)$target_type_set;
    }

    protected function canCastToNonNullableType(Type $type): bool
    {
        return $type->isPossiblyTruthy();
    }

    protected function canCastToNonNullableTypeWithoutConfig(Type $type): bool
    {
        return $type->isPossiblyTruthy();
    }

    public function asGenericArrayType(int $key_type): Type
    {
        return GenericArrayType::fromElementType($this, false, $key_type);
    }

    /**
     * @unused-param $code_base
     * @unused-param $context
     */
    public function canCastToDeclaredType(CodeBase $code_base, Context $context, Type $other): bool
    {
        return $other->isPossiblyTruthy();
    }

    public function isPossiblyFalsey(): bool
    {
        return $this->is_nullable;
    }

    public function isPossiblyFalse(): bool
    {
        return false;
    }

    public function isAlwaysTruthy(): bool
    {
        return !$this->is_nullable;
    }

    public function asObjectType(): ?Type
    {
        return ObjectType::instance(false);
    }

    public function asArrayType(): ?Type
    {
        return NonEmptyGenericArrayType::fromElementType(
            MixedType::instance(false),
            false,
            GenericArrayType::KEY_MIXED
        );
    }

    public function asNonFalseyType(): Type
    {
        return $this->is_nullable ? $this->withIsNullable(false) : $this;
    }

    /** @override */
    public function isNullable(): bool
    {
        return $this->is_nullable;
    }

    /** @override */
    public function __toString(): string
    {
        return $this->is_nullable ? '?non-empty-mixed' : 'non-empty-mixed';
    }

    public function weaklyOverlaps(Type $other): bool
    {
        return $other->isPossiblyTruthy();
    }

    public function withIsNullable(bool $is_nullable): Type
    {
        if ($is_nullable) {
            if ($this->is_nullable) {
                return $this;
            }
            return static::instance(false);
        }
        return NonNullMixedType::instance(false);
    }
}
