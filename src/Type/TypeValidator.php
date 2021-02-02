<?php declare(strict_types = 1);

namespace SlevomatZboziApi\Type;

use function gettype;
use function is_array;
use function is_bool;
use function is_integer;
use function is_scalar;
use function is_string;
use function sprintf;

class TypeValidator
{

	/**
	 * @param mixed $value
	 * @param bool $allowNull
	 */
	public static function checkString($value, bool $allowNull = false): void
	{
		if (!is_string($value) && (!$allowNull || $value !== null)) {
			self::throwException('String', $value);
		}
	}

	/**
	 * @param mixed $value
	 * @param bool $allowNull
	 */
	public static function checkInteger($value, bool $allowNull = false): void
	{
		if (!is_integer($value) && (!$allowNull || $value !== null)) {
			self::throwException('Integer', $value);
		}
	}

	/**
	 * @param mixed $value
	 * @param bool $allowNull
	 */
	public static function checkBoolean($value, bool $allowNull = false): void
	{
		if (!is_bool($value) && (!$allowNull || $value !== null)) {
			self::throwException('Boolean', $value);
		}
	}

	/**
	 * @param mixed $value
	 * @param string|null $className
	 * @param bool $allowNull
	 */
	public static function checkArray($value, ?string $className = null, bool $allowNull = false): void
	{
		static::checkString($className, true);

		if ($value === null && $allowNull) {
			return;
		}

		if (!is_array($value)) {
			throw new TypeValidationFailedException(sprintf('Array expected, %s given.', gettype($value)));
		}

		if ($className === null) {
			return;
		}

		foreach ($value as $item) {
			if (!$item instanceof $className) {
				throw new TypeValidationFailedException(sprintf('%s[] expected.', $className));
			}
		}
	}

	/**
	 * @param string $type
	 * @param mixed $value
	 */
	private static function throwException(string $type, $value): void
	{
		if (is_scalar($value)) {
			throw new TypeValidationFailedException(sprintf('%s expected, %s (%s) given.', $type, gettype($value), $value));
		}

		throw new TypeValidationFailedException(sprintf('%s expected, %s given.', $type, gettype($value)));
	}

}
