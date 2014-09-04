<?php

namespace Zeliard91\Bundle\DynamoDBConnectorBundle\Schema;

use Aws\DynamoDb\Enum\Type;
use Aws\DynamoDb\Enum\KeyType;

class SchemaManager
{
    /**
     * @var Aws\DynamoDb\DynamoDbClient
     */
    private $dynamoDb;

    /**
     * @var array
     */
    private $entityNamespaces;

    /**
     * @var array
     */
    private $entityClasses;

    /**
     * [$default_read_capacity description]
     * @var integer
     */
    private $default_read_capacity = 10;

    /**
     * [$default_write_capacity description]
     * @var integer
     */
    private $default_write_capacity = 10;

    /**
     * 
     * @param [type] $dynamoDb          [description]
     * @param [type] $entityNamespaces [description]
     */
    public function __construct($dynamoDb, $entityNamespaces)
    {
        $this->dynamoDb          = $dynamoDb;
        $this->entityNamespaces = $entityNamespaces;
        $this->entityClasses     = array();
    }

    /**
     * Returns the entity's fully qualified class name.
     *
     * @param string $entityClass
     *
     * @throws \DomainException
     */
    protected function getEntityClass($entityClass)
    {
        if (!isset($this->entityClasses[$entityClass])) {

            $found = class_exists($entityClass);

            if ($found) {
                $reflection = new \ReflectionClass($entityClass);
                $fqcn = '\\' . $reflection->getName();
            } elseif (strpos('\\', $entityClass) !== 0) {
                foreach ($this->entityNamespaces as $namespace) {
                    $fqcn = '\\' . trim($namespace, '\\') . '\\' . $entityClass;
                    if (class_exists($fqcn)) {
                        $found = true;
                        break;
                    }
                }
            }

            if (!$found) {
                throw new \DomainException('Entity class not found: ' . $entityClass);
            }

            $this->entityClasses[$entityClass] = $fqcn;
        }

        return $this->entityClasses[$entityClass];
    }

    /**
     * [createTable description]
     * @param  [type] $entity [description]
     * @return [type]         [description]
     */
    public function createTable($entity, $read_capacity = null, $write_capacity = null)
    {
        $class = $this->getEntityClass($entity);
        $datatype_mappings = $class::getDataTypeMappings();

        if (is_null($read_capacity) || !is_int($read_capacity) || $read_capacity < 1) {
            $read_capacity = $this->default_read_capacity;
        }
        if (is_null($write_capacity) || !is_int($write_capacity) || $write_capacity < 1) {
            $write_capacity = $this->default_write_capacity;
        }
        
        // Construction of parameter for the creation
        $params = array(
            'TableName' => $class::getTable(),
            'AttributeDefinitions' => array(
                array(
                    'AttributeName' => $class::getHashKeyAttribute(),
                    'AttributeType' => Type::STRING
                )
            ),
            'KeySchema' => array(
                array(
                    'AttributeName' => $class::getHashKeyAttribute(),
                    'KeyType'       => KeyType::HASH
                ),
            ),
            'ProvisionedThroughput' => array(
                'ReadCapacityUnits'  => $read_capacity,
                'WriteCapacityUnits' => $write_capacity
            )
        );

        if ($class::getRangeKeyAttribute() !== false) {
            $params['KeySchema'][] = array(
                    'AttributeName' => $class::getRangeKeyAttribute(),
                    'KeyType'       => KeyType::HASH
                );
        }
        foreach($datatype_mappings as $datatype_mapping_key => $datatype_mapping_type) {
            $params['AttributeDefinitions'][$datatype_mapping_key] = $datatype_mapping_type;
        }
        
        $this->dynamoDb->createTable($params);
        $this->dynamoDb->waitUntil('TableExists', array(
            'TableName' => $class::getTable()
        ));
    }

    /**
     * check if table exists
     * @param  [type]  $entity [description]
     * @return boolean         [description]
     */
    public function isTableExists($entity)
    {
        $class = $this->getEntityClass($entity);

        try {
            $result = $this->dynamoDb->describeTable(array(
                'TableName' => $class::getTable()
            ));
        } catch (\Aws\DynamoDb\Exception\ResourceNotFoundException $e) {
            return false;
        }
        catch (\Exception $e) {
            throw $e;
        }
        return true;
    }

    /**
     * Delete Table
     * @param  [type] $entity [description]
     * @return [type]         [description]
     */
    public function deleteTable($entity)
    {
        $class = $this->getEntityClass($entity);

        $this->dynamoDb->deleteTable(array(
            'TableName' => $class::getTable()
        ));

        $this->dynamoDb->waitUntil('TableNotExists', array(
            'TableName' => $class::getTable()
        ));
    }
}
