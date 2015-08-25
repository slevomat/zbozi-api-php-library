<?php

namespace SlevomatZboziApi\Type;

class TypeValidator
{

	/**
	 * @param mixed $value
	 * @param boolean $allowNull
	 */
	public static function checkString($value, $allowNull = false)
	{
		if (!is_string($value) && (!$allowNull || $value !== null)) {
			self::throwException('String', $value);
		}
	}

	/**
	 * @param mixed $value
	 * @param boolean $allowNull
	 */
	public static function checkInteger($value, $allowNull = false)
	{
		if (!is_integer($value) && (!$allowNull || $value !== null)) {
			self::throwException('Integer', $value);
		}
	}

	/**
	 * @param mixed $value
	 * @param boolean $allowNull
	 */
	public static function checkBoolean($value, $allowNull = false)
	{
		if (!is_bool($value) && (!$allowNull || $value !== null)) {
			self::throwException('Boolean', $value);
		}
	}

	/**
	 * @param mixed $value
	 * @param string|null $className
	 * @param boolean $allowNull
	 */
	public static function checkArray($value, $className = null, $allowNull = false)
	{
		static::checkString($className, true);

		if ($value === null && $allowNull) {
			return;
		}

		if (!is_array($value)) {
			throw new \SlevomatZboziApi\Type\TypeValidationFailedException(sprintf('Array expected, %s given.', gettype($value)));
		}

		if ($className !== null) {
			foreach ($value as $item) {
				if (!$item instanceof $className) {
					throw new \SlevomatZboziApi\Type\TypeValidationFailedException(sprintf('%s[] expected.', $className));
				}
			}
		}
	}

	/**
	 * @param string $type
	 * @param mixed $value
	 */
	private static function throwException($type, $value)
	{
		if (is_scalar($value)) {
			throw new \SlevomatZboziApi\Type\TypeValidationFailedException(sprintf('%s expected, %s (%s) given.', $type, gettype($value), $value));

		} else {
			throw new \SlevomatZboziApi\Type\TypeValidationFailedException(sprintf('%s expected, %s given.', $type, gettype($value)));
		}
	}

}
