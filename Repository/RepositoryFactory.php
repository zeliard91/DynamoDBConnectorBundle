<?php

namespace Zeliard91\Bundle\DynamoDBConnectorBundle\Repository;

use Cpliakas\DynamoDb\ODM\DocumentManager;

class RepositoryFactory
{
    
    /**
     * The list of EntityRepository instances.
     *
     * @var array<\Doctrine\Common\Persistence\ObjectRepository>
     */
    private $repositoryList = array();

    /**
     * Return ODM Repository for a entity
     * @param  DocumentManager $documentManager [description]
     * @param  string          $entityName       [description]
     * @param  array          $entity_namespaces       [description]
     * @return [type]                           [description]
     */
    public function getRepository(DocumentManager $documentManager, $entityName, $entity_namespaces)
    {
        if (isset($this->repositoryList[$entityName])) {
            return $this->repositoryList[$entityName];
        }

        $repository = $this->createRepository($documentManager, $entityName, $entity_namespaces);

        $this->repositoryList[$entityName] = $repository;

        return $repository;
    }

    /**
     * Create a new repository instance for an entity class.
     *
     * @param DocumentManager $documentManager The EntityManager instance.
     * @param string $entityName    The name of the entity.
     * @param array  $entity_namespaces
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function createRepository(DocumentManager $documentManager, $entityName, $entity_namespaces)
    {
        $repositoryClassName = $this->getRepositoryClass($entityName, $entity_namespaces);
        
        if ($repositoryClassName === null) {
            $repositoryClassName = $this->getDefaultRepositoryClassName();
        }

        return new $repositoryClassName($documentManager, $entityName);
    }

    /**
     * Return Repository class
     * @param string $entityName    The name of the entity.
     * @param array  $entity_namespaces
     * @return [type]                    [description]
     */
    protected function getRepositoryClass($entityName, $entity_namespaces)
    {
        $repositoryClassName = $entityName.'Repository';

        $found = class_exists($repositoryClassName);

        if ($found) {
            $reflection = new \ReflectionClass($repositoryClassName);
            $fqcn = '\\' . $reflection->getName();
        } elseif (strpos('\\', $repositoryClassName) !== 0) {
            foreach ($entity_namespaces as $namespace) {
                $fqcn = '\\' . trim($namespace, '\\') . '\\' . $repositoryClassName;
                if (class_exists($fqcn)) {
                    $found = true;
                    break;
                }
            }
        }
        if ($found === true) {
            return $fqcn;
        }

        return null;
    }

    
    protected function getDefaultRepositoryClassName()
    {
        return '\Zeliard91\Bundle\DynamoDBConnectorBundle\Repository\DefaultRepository';
    }
}
