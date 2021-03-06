<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Test\Api\ApiDefinition\Generator;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Api\ApiDefinition\Generator\OpenApi3Generator;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionRegistry;
use Shopware\Core\Framework\Test\Api\ApiDefinition\EntityDefinition\SimpleDefinition;
use Shopware\Core\Framework\Test\TestCaseBase\AssertArraySubsetBehaviour;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OpenApi3GeneratorTest extends TestCase
{
    use AssertArraySubsetBehaviour;

    /**
     * @var array
     */
    private $schema;

    /**
     * @var string string
     */
    private $entityName;

    public function __construct()
    {
        parent::__construct();

        $containerMock = $this->createMock(ContainerInterface::class);
        $definitionRegistry = new DefinitionRegistry([SimpleDefinition::class => 'simple.repository'], $containerMock);
        $openApiGenerator = new OpenApi3Generator($definitionRegistry);
        $this->schema = $openApiGenerator->getSchema();
        $this->entityName = SimpleDefinition::getEntityName();
    }

    public function testEntityNameConversion(): void
    {
        static::assertArrayHasKey(SimpleDefinition::getEntityName(), $this->schema);
        static::assertEquals($this->entityName, $this->schema[$this->entityName]['name']);
    }

    public function testTypeConversion(): void
    {
        $properties = $this->schema[$this->entityName]['properties'];

        $this->silentAssertArraySubset(['type' => 'string', 'format' => 'uuid'], $properties['id']);
        $this->silentAssertArraySubset(['type' => 'string'], $properties['stringField']);
        $this->silentAssertArraySubset(['type' => 'integer', 'format' => 'int64'], $properties['intField']);
        $this->silentAssertArraySubset(['type' => 'number', 'format' => 'float'], $properties['floatField']);
        $this->silentAssertArraySubset(['type' => 'boolean'], $properties['boolField']);
        $this->silentAssertArraySubset(['type' => 'string'], $properties['stringField']);
        $this->silentAssertArraySubset(['type' => 'integer', 'format' => 'int64'], $properties['childCount']);
    }

    public function testFlagConversion(): void
    {
        $properties = $this->schema[$this->entityName]['properties'];
        $requiredFields = $this->schema[$this->entityName]['required'];

        $this->silentAssertArraySubset(['requiredField'], $requiredFields);
        $this->silentAssertArraySubset(['readOnly' => true], $properties['readOnlyField']);
    }
}
