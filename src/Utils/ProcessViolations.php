<?php declare(strict_types=1);

namespace App\Utils;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ProcessViolations
{

	public function processViolations(ConstraintViolationListInterface $violations): void
	{
		if ($violations->count()) {
			throw new ValidationException($violations);
		}
	}


	public function processPartialViolations(PartialDenormalizationException $e): ConstraintViolationList
	{
		$violations = new ConstraintViolationList();
		/** @var NotNormalizableValueException $exception */
		foreach ($e->getErrors() as $exception) {
			$message = sprintf('The type must be one of "%s" ("%s" given).', implode(', ', $exception->getExpectedTypes()), $exception->getCurrentType());
			$parameters = [];
			if ($exception->canUseMessageForUser()) {
				$parameters['hint'] = $exception->getMessage();
			}
			$violations->add(new ConstraintViolation($message, '', $parameters, null, $exception->getPath(), null));
		}

		return $violations;
	}
}
