<?php

namespace Zeliard91\Bundle\DynamoDBConnectorBundle\Repository;

use Cpliakas\DynamoDb\ODM\DocumentManager;

class DefaultRepository
{
    /**
     * Class of the Document
     * @var string
     */
    private $class;

    /**
     * DocumentManager
     * @var Cpliakas\DynamoDb\ODM\DocumentManager
     */
    private $dm;

    public function __construct(DocumentManager $dm, $class)
    {
        $this->class = $class;
        $this->dm    = $dm;
    }

    
    public function read($conditions)
    {
        return $this->dm->read($this->class, $conditions);
    }

    
    public function scan($conditions)
    {
        return $this->dm->scan($this->class, $conditions);
    }
}
