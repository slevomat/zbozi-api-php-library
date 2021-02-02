<?php declare(strict_types = 1);

namespace SlevomatZboziApi\Type;

use Exception;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use StdClass;

class TypeValidatorTest extends TestCase
{

	/**
	 * @return mixed[][]
	 */
	public function invalidStrings(): array
	{
		return [
			[true],
			[0.5],
			[1212],
			[null],
			[[]],
		];
	}

	/**
	 * @return mixed[][]
	 */
	public function validStrings(): array
	{
		return [
			['sfdsfsdfsd'],
			['444545'],
			['true'],
			[null, true],
		];
	}

	/**
	 * @return mixed[][]
	 */
	public function invalidIntegers(): array
	{
		return [
			['1212'],
			[true],
			[0.5],
			[null],
			[[]],
			[new StdClass()],
		];
	}

	/**
	 * @return mixed[][]
	 */
	public function validIntegers(): array
	{
		return [
			[12122],
			[-7878787],
			[null, true],
		];
	}

	/**
	 * @return mixed[][]
	 */
	public function validBooleans(): array
	{
		return [
			[true],
			[false],
			[null, true],
		];
	}

	/**
	 * @return mixed[][]
	 */
	public function invalidBooleans(): array
	{
		return [
			[1],
			['true'],
			['1212'],
			[0.5],
			[null],
			[[]],
			[new StdClass()],
		];
	}

	/**
	 * @return mixed[][]
	 */
	public function invalidArrays(): array
	{
		return [
			[1212],
			['1212'],
			[true],
			[0.5],
			[null],
			[[new StdClass(), new Exception('x')], 'StdClass'],
			[[new StdClass(), 121212], 'StdClass'],
		];
	}

	/**
	 * @return mixed[][]
	 */
	public function validArrays(): array
	{
		return [
			[[new StdClass(), new Exception('x')]],
			[[new StdClass(), new StdClass()], 'StdClass'],
			[[]],
			[[], 'StdClass'],
			[null, null, true],
		];
	}

	/**
	 * @dataProvider validStrings
	 *
	 * @param mixed $value
	 * @param bool $allowNull
	 */
	public function testCheckString($value, bool $allowNull = false): void
	{
		TypeValidator::checkString($value, $allowNull);
		Assert::assertTrue(true);
	}

	/**
	 * @dataProvider invalidStrings
	 *
	 * @param mixed $value
	 */
	public function testCheckStringThrowsExceptionForInvalidStrings($value): void
	{
		$this->expectException(TypeValidationFailedException::class);
		$this->expectExceptionMessage('String expected');
		TypeValidator::checkString($value, false);
	}

	/**
	 * @dataProvider validIntegers
	 *
	 * @param mixed $value
	 * @param bool $allowNull
	 */
	public function testCheckInteger($value, bool $allowNull = false): void
	{
		TypeValidator::checkInteger($value, $allowNull);
		Assert::assertTrue(true);
	}

	/**
	 * @dataProvider invalidIntegers
	 *
	 * @param mixed $value
	 */
	public function testCheckIntegerThrowsExceptionForInvalidIntegers($value): void
	{
		$this->expectException(TypeValidationFailedException::class);
		$this->expectExceptionMessage('Integer expected');
		TypeValidator::checkInteger($value, false);
	}

	/**
	 * @dataProvider validBooleans
	 *
	 * @param mixed $value
	 * @param bool $allowNull
	 */
	public function testCheckBoolean($value, bool $allowNull = false): void
	{
		TypeValidator::checkBoolean($value, $allowNull);
		Assert::assertTrue(true);
	}

	/**
	 * @dataProvider invalidBooleans
	 *
	 * @param mixed $value
	 */
	public function testCheckBooleanThrowsExceptionForInvalidBooleans($value): void
	{
		$this->expectException(TypeValidationFailedException::class);
		$this->expectExceptionMessage('Boolean expected');
		TypeValidator::checkBoolean($value, false);
	}

	/**
	 * @dataProvider validArrays
	 *
	 * @param mixed $value
	 * @param string|null $className
	 * @param bool $allowNull
	 */
	public function testCheckArray($value, ?string $className = null, bool $allowNull = false): void
	{
		TypeValidator::checkArray($value, $className, $allowNull);
		Assert::assertTrue(true);
	}

	/**
	 * @dataProvider invalidArrays
	 *
	 * @param mixed $value
	 * @param string|null $className
	 * @param bool $allowNull
	 */
	public function testCheckArrayThrowsExceptionForInvalidArrays($value, ?string $className = null, bool $allowNull = false): void
	{
		$this->expectException(TypeValidationFailedException::class);
		TypeValidator::checkArray($value, $className, $allowNull);
	}

}
