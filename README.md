Zeliard91DynamoDBConnectorBundle
======================

This bundle provides a symfony 2 service to interact with [cpliakas/dynamo-db-odm](https://github.com/cpliakas/dynamo-db-odm).

## Installation

Installation is a quick 3 step process:

1. Download Zeliard91DynamoDBConnectorBundle using composer
2. Enable the Bundle
3. Configure your application's config.yml

### Step 1: Download Zeliard91DynamoDBConnectorBundle using composer

Add Zeliard91DynamoDBConnectorBundle in your composer.json:

```js
{
    "require": {
        "zeliard91/dynamodb-connector-bundle": "dev-master"
    }
}
```

Now tell composer to download the bundle by running the command:

``` bash
$ php composer.phar update zeliard91/dynamodb-connector-bundle
```

Composer will install the bundle to your project's `vendor/zeliard91/dynamodb-connector-bundle` directory.

### Step 2: Enable the bundle

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Zeliard91\Bundle\DynamoDBConnectorBundle\Zeliard91DynamoDBConnectorBundle(),
    );
}
```

### Step3: Add your DynamoDB credentials in your project configuration file

``` yaml
# app/config/config.yml

zeliard91_dynamo_db_connector:
    # get the values from parameters.yml
    key: "%dynamodb_key%"
    secret: "%dynamodb_secret%"
    region: "eu-west-1"
    # optional : for dev, you can specify base url
    base_url: "%dynamodb_url%"
    # optional : location of your entities
    entity_namespaces: 
        - Foo\BarBundle\Entity
```

## Usage

### Access to service objects

You can get DynamoDB client and document manager in your application by calling the service.

``` php
<?php

$document_manager = $this->get('zeliard91_dynamo_db_connector')->getManager();
$dynamo_client    = $this->get('zeliard91_dynamo_db_connector')->getDynamoDb();

```

### Entity repositories

If you have register entity namespace, you can also create repositories classes in order 
to define queries.

Let's assume you have created the entity `Foo\BarBundle\Entity\Book.php`

Now define the repository class, it has to be in the same directory and must end by 'Repository' : 

``` php
<?php
// Foo/BarBundle/Entity/BookRepository.php

namespace Foo\BarBundle\Entity;

use Zeliard91\Bundle\DynamoDBConnectorBundle\Repository\DefaultRepository as Repository;
use Cpliakas\DynamoDb\ODM\Conditions;
use Aws\DynamoDb\Enum\ComparisonOperator;

class BookRepository extends Repository
{
    /**
     * Find all books from an author
     * @param string $author
     * @return array
     */
    public function findByAuthor($author)
    {
        $conditions = Conditions::factory()
            ->addCondition('author', $author, ComparisonOperator::EQ)
        ;
        return $this->scan($conditions);
    }
}

```

You can now call the method in your controller :

``` php
<?php

$book_repository = $this->get('zeliard91_dynamo_db_connector')->getRepository('Book');
$books = $book_repository->findByAuthor($author);

```
