<?php declare(strict_types = 1);

namespace SlevomatZboziApi\Request;

use JsonSerializable;
use SlevomatZboziApi\Type\TypeValidator;

class CancelOrderItem implements JsonSerializable
{

	private string $slevomatId;

	private int $amount;

	public function __construct(string $slevomatId, int $amount)
	{
		$this->setSlevomatId($slevomatId);
		$this->setAmount($amount);
	}

	public function getSlevomatId(): string
	{
		return $this->slevomatId;
	}

	public function getAmount(): int
	{
		return $this->amount;
	}

	/**
	 * @return mixed[]
	 */
	public function jsonSerialize(): array
	{
		return [
			'slevomatId' => $this->getSlevomatId(),
			'amount' => $this->getAmount(),
		];
	}

	private function setSlevomatId(string $slevomatId): void
	{
		TypeValidator::checkString($slevomatId);

		$this->slevomatId = $slevomatId;
	}

	private function setAmount(int $amount): void
	{
		TypeValidator::checkInteger($amount);

		$this->amount = $amount;
	}

}
