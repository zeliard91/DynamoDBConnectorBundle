<?php

namespace Zeliard91\Bundle\DynamoDBConnectorBundle\Service;

use Zeliard91\Bundle\DynamoDBConnectorBundle\Repository\RepositoryFactory;
use Zeliard91\Bundle\DynamoDBConnectorBundle\Schema\SchemaManager;
use Aws\DynamoDb\DynamoDbClient;
use Cpliakas\DynamoDb\ODM\DocumentManager;

/**
* DynamoDB connector
*/
class Connector
{
    
    /**
     * @var Symfony\Bridge\Monolog\Logger
     */
    private $logger;
    
    /**
     * @var Aws\DynamoDb\DynamoDbClient
     */
    private $dynamoDb;

    /**
     * @var Cpliakas\DynamoDb\ODM\DocumentManager
     */
    private $dm;

    /**
     * @var Zeliard91\Bundle\DynamoDBConnectorBundle\Repository\RepositoryFactory
     */
    private $repositoryFactory;

    /**
     * @var array
     */
    private $entity_namespaces;

    /**
     * @var Zeliard91\Bundle\DynamoDBConnectorBundle\Schema\SchemaManager
     */
    private $SchemaManager;
    
    /**
     * constructor
     * @param Symfony\Bridge\Monolog\Logger $logger
     */
    public function __construct($logger)
    {
        $this->logger = $logger;
        $this->repositoryFactory = new RepositoryFactory();
    }

    /**
     * Create connexion to dynamodb and initialize document manager
     * @param [type] $key   
     * @param [type] $secret
     * @param [type] $region
     */
    public function setCredentials($key, $secret, $region, $base_url = null)
    {
        $this->logger->debug('set credentials to dynamodb client');

        $params = array(
            'key'    => $key,
            'secret' => $secret,
            'region' => $region,
        );
        if (!is_null($base_url)) {
            $params['base_url'] = $base_url;
        }

        $this->dynamoDb = DynamoDbClient::factory($params);

        $this->dm = new DocumentManager($this->dynamoDb);
    }

    /**
     * Tells document manager where are the entities to handle
     * @param array $entity_namespaces
     */
    public function setEntityNamespaces(array $entity_namespaces)
    {
        $this->entity_namespaces = $entity_namespaces;
        $this->logger->debug('assign entities to document mananager : '.var_export($entity_namespaces, true));
        if (count($entity_namespaces) > 0) {
            foreach ($entity_namespaces as $entity_namespace) {
                $this->dm->registerEntityNamesapce($entity_namespace);
            }
        }
    }

    /**
     * Return document manager
     * @return DocumentManager
     */
    public function getManager()
    {
        return $this->dm;
    }

    /**
     * Return dynamodb client instance
     * @return [type] [description]
     */
    public function getDynamoDb()
    {
        return $this->dynamoDb;
    }

    /**
     * Get ODM Repository for entity
     * @param  string $entityName
     * @return RepositoryInterface
     */
    public function getRepository($entityName)
    {
        return $this->repositoryFactory->getRepository($this->getManager(), $entityName, $this->entity_namespaces);
    }

    
    /**
     * Returns schema manager
     * @return SchemaManager
     */
    public function getSchemaManager()
    {
        if (!isset($this->schemamanager)) {
            $this->schemamanager = new SchemaManager($this->dynamoDb, $this->entity_namespaces);
        }
        return $this->schemamanager;
    }
}
