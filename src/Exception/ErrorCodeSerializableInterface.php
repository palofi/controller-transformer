<?php

declare(strict_types=1);

namespace Pafi\Exception;

interface ErrorCodeSerializableInterface
{

	public static function getErrorCode(): string;
}
