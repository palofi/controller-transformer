<?php

declare(strict_types=1);

namespace App\Exception;

interface ErrorCodeSerializableInterface
{

	public static function getErrorCode(): string;
}
