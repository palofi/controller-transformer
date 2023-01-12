<?php declare(strict_types=1);

namespace Pafi\EventSubscriber;

use Pafi\Controller\TransformableControllerInterface;
use Pafi\Utils\ProcessViolations;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestTransformerSubscriber implements EventSubscriberInterface
{

	public function __construct(
		private SerializerInterface $serializer,
		private ValidatorInterface $validator,
		private ProcessViolations $utils,

	) {
	}


	public static function getSubscribedEvents(): array
	{
		return [KernelEvents::CONTROLLER_ARGUMENTS => ['transform']];
	}


	public function transform(ControllerArgumentsEvent $event): void
	{
		$inputController = '';
		foreach ($event->getController() as $controller) {
			if ($controller instanceof TransformableControllerInterface) {
				$inputController = $controller;
				break;
			}
		}

		$eventArguments = $event->getArguments();
		$argumentKey = '';
		$inputClass = '';
		foreach ($eventArguments as $key => $argument) {
			if ($argument instanceof TransformableDtoInputInterface) {
				$inputClass = $argument;
				$argumentKey = $key;
				break;
			}
		}

		if (!$inputClass && !$inputController) {
			return;
		}

		try {
			$inputDto = $this->serializer->deserialize(
				$event->getRequest()->getContent(),
				get_class($inputClass),
				'json',
				[DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS => true,]
			);

			$this->utils->processViolations($this->validator->validate($inputDto));

			$eventArguments[$argumentKey] = $inputDto;

			$event->setArguments($eventArguments);
		} catch (PartialDenormalizationException $exception) {
			$this->utils->processViolations($this->utils->processPartialViolations($exception));
		}
	}
}
