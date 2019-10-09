<?php declare(strict_types=1);

namespace Phan\Language\Type;

use Phan\Language\Type;

/**
 * Phan's representation for types such as `non-empty-array` and `non-empty-array<string,MyClass>`
 * @see GenericArrayType for representations of `string[]` and `array<int,bool>`
 * @see ArrayShapeType for representations of `array{key:MyClass}`
 * @see ArrayType for the representation of `array`
 */
final class NonEmptyListType extends ListType
{
    /**
     * @override
     * @return NonEmptyListType
     * @phan-real-return NonEmptyListType
     * (can't change signature type until minimum supported version is php 7.4)
     */
    public static function fromElementType(
        Type $type,
        bool $is_nullable,
        int $unused_key_type = GenericArrayType::KEY_INT
    ) : GenericArrayType {
        // Make sure we only ever create exactly one
        // object for any unique type
        static $map = null;

        if ($map === null) {
            $map = new \SplObjectStorage();
        }

        if (!$map->contains($type)) {
            $map->attach(
                $type,
                new NonEmptyListType($type, $is_nullable)
            );
        }

        return $map->offsetGet($type);
    }

    protected function canCastToNonNullableType(Type $type) : bool
    {
        if (!$type->isPossiblyTruthy()) {
            return false;
        }
        return parent::canCastToNonNullableType($type);
    }

    /** @override */
    public function isPossiblyFalsey() : bool
    {
        return $this->is_nullable;
    }

    /** @override */
    public function isAlwaysTruthy() : bool
    {
        return !$this->is_nullable;
    }

    /** @override */
    public function asNonFalseyType() : Type
    {
        return $this->withIsNullable(false);
    }

    public function __toString() : string
    {
        return ($this->is_nullable ? '?' : '') . 'non-empty-list<' . $this->element_type->__toString() . '>';
    }

    /** @override */
    public function isDefinitelyNonEmptyArray() : bool
    {
        return true;
    }
}