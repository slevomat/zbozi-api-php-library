<?php

namespace SlevomatZboziApi\Request;

use SlevomatZboziApi\Type\TypeValidator;

class CancelOrderItem implements \JsonSerializable
{

	/** @var string */
	private $slevomatId;

	/** @var integer */
	private $amount;

	/**
	 * @param string $slevomatId
	 * @param integer $amount
	 */
	public function __construct($slevomatId, $amount)
	{
		$this->setSlevomatId($slevomatId);
		$this->setAmount($amount);
	}

	/**
	 * @return string
	 */
	public function getSlevomatId()
	{
		return $this->slevomatId;
	}

	/**
	 * @return integer
	 */
	public function getAmount()
	{
		return $this->amount;
	}

	/**
	 * @return mixed[]
	 */
	public function jsonSerialize()
	{
		return [
			'slevomatId' => $this->getSlevomatId(),
			'amount' => $this->getAmount(),
		];
	}

	/**
	 * @param string $slevomatId
	 */
	private function setSlevomatId($slevomatId)
	{
		TypeValidator::checkString($slevomatId);

		$this->slevomatId = $slevomatId;
	}

	/**
	 * @param integer $amount
	 */
	private function setAmount($amount)
	{
		TypeValidator::checkInteger($amount);

		$this->amount = $amount;
	}

}
