<?php

namespace SlevomatZboziApi\Type;

class TypeValidatorTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @return mixed[][]
	 */
	public function invalidStrings()
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
	public function validStrings()
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
	public function invalidIntegers()
	{
		return [
			['1212'],
			[true],
			[0.5],
			[null],
			[[]],
			[new \StdClass()],
		];
	}

	/**
	 * @return mixed[][]
	 */
	public function validIntegers()
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
	public function validBooleans()
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
	public function invalidBooleans()
	{
		return [
			[1],
			['true'],
			['1212'],
			[0.5],
			[null],
			[[]],
			[new \StdClass()],
		];
	}

	/**
	 * @return mixed[][]
	 */
	public function invalidArrays()
	{
		return [
			[1212],
			['1212'],
			[true],
			[0.5],
			[null],
			[[new \StdClass(), new \Exception('x')], 'StdClass'],
			[[new \StdClass(), 121212], 'StdClass'],
		];
	}

	/**
	 * @return mixed[][]
	 */
	public function validArrays()
	{
		return [
			[[new \StdClass(), new \Exception('x')]],
			[[new \StdClass(), new \StdClass()], 'StdClass'],
			[[]],
			[[], 'StdClass'],
			[null, null, true],
		];
	}

	/**
	 * @dataProvider validStrings
	 * @param mixed $value
	 * @param boolean $allowNull
	 */
	public function testCheckString($value, $allowNull = false)
	{
		TypeValidator::checkString($value, $allowNull);
		$this->assertTrue(true);
	}

	/**
	 * @dataProvider invalidStrings
	 * @param mixed $value
	 * @expectedException \SlevomatZboziApi\Type\TypeValidationFailedException
	 * @expectedExceptionMessage String expected
	 */
	public function testCheckStringThrowsExceptionForInvalidStrings($value)
	{
		TypeValidator::checkString($value, false);
	}

	/**
	 * @dataProvider validIntegers
	 * @param mixed $value
	 * @param boolean $allowNull
	 */
	public function testCheckInteger($value, $allowNull = false)
	{
		TypeValidator::checkInteger($value, $allowNull);
		$this->assertTrue(true);
	}

	/**
	 * @dataProvider invalidIntegers
	 * @param mixed $value
	 * @expectedException \SlevomatZboziApi\Type\TypeValidationFailedException
	 * @expectedExceptionMessage Integer expected
	 */
	public function testCheckIntegerThrowsExceptionForInvalidIntegers($value)
	{
		TypeValidator::checkInteger($value, false);
	}

	/**
	 * @dataProvider validBooleans
	 * @param mixed $value
	 * @param boolean $allowNull
	 */
	public function testCheckBoolean($value, $allowNull = false)
	{
		TypeValidator::checkBoolean($value, $allowNull);
		$this->assertTrue(true);
	}

	/**
	 * @dataProvider invalidBooleans
	 * @param mixed $value
	 * @expectedException \SlevomatZboziApi\Type\TypeValidationFailedException
	 * @expectedExceptionMessage Boolean expected
	 */
	public function testCheckBooleanThrowsExceptionForInvalidBooleans($value)
	{
		TypeValidator::checkBoolean($value, false);
	}

	/**
	 * @dataProvider validArrays
	 * @param mixed $value
	 * @param string|null $className
	 * @param boolean $allowNull
	 */
	public function testCheckArray($value, $className = null, $allowNull = false)
	{
		TypeValidator::checkArray($value, $className, $allowNull);
		$this->assertTrue(true);
	}

	/**
	 * @dataProvider invalidArrays
	 * @param mixed $value
	 * @param string|null $className
	 * @param boolean $allowNull
	 * @expectedException \SlevomatZboziApi\Type\TypeValidationFailedException
	 */
	public function testCheckArrayThrowsExceptionForInvalidArrays($value, $className = null, $allowNull = false)
	{
		TypeValidator::checkArray($value, $className, $allowNull);
	}

}
