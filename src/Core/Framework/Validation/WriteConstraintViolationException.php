<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Validation;

use Shopware\Core\Framework\DataAbstractionLayer\Write\FieldException\WriteFieldException;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class WriteConstraintViolationException extends WriteFieldException implements ConstraintViolationExceptionInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var ConstraintViolationListInterface
     */
    private $constraintViolationList;

    /**
     * @var string
     */
    private $concern;

    public function __construct(ConstraintViolationListInterface $constraintViolationList, string $path = '', string $concern = '')
    {
        parent::__construct(
            'Caught {{ count }} constraint violation errors.',
            ['count' => $constraintViolationList->count()]
        );

        $this->path = $path;
        $this->constraintViolationList = $constraintViolationList;
        $this->concern = $concern;
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->constraintViolationList;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function toArray(): array
    {
        $result = [];

        /** @var ConstraintViolationInterface $violation */
        foreach ($this->constraintViolationList as $violation) {
            $result[] = [
                'message' => $violation->getMessage(),
                'messageTemplate' => $violation->getMessageTemplate(),
                'parameters' => $violation->getParameters(),
                'propertyPath' => $violation->getPropertyPath(),
            ];
        }

        return $result;
    }

    public function getConcern(): string
    {
        return ($this->concern ? ($this->concern . '-') : '') . 'violation-error';
    }
}
