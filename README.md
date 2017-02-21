ElasticSearchUnit is a PHPUnit extension for test cases that utilize the official ElasticSearch Client as their data source.

[![Build Status](https://travis-ci.org/zumba/elasticsearchunit.svg)](https://travis-ci.org/zumba/elasticsearchunit)

## Requirements

* PHP 5.6+
* ElasticSearch 5.0+

## Testing

1. Install dependencies `composer install`
2. Run `./bin/phpunit`

## Example use

```php
<?php

class MyElasticSearchTestCase extends \PHPUnit_Framework_TestCase {
	use \Zumba\PHPUnit\Extensions\ElasticSearch\TestTrait;

	/**
	 * Get the ElasticSearch connection for this test.
	 *
	 * @return Zumba\PHPUnit\Extensions\ElasticSearch\Client\Connector
	 */
	public function getElasticSearchConnector() {
		if (empty($this->connection)) {
			$this->connection = new \Zumba\PHPUnit\Extensions\ElasticSearch\Client\Connector(new \Elasticsearch\Client());
		}
		return $this->connection;
	}

	/**
	 * Get the dataset to be used for this test.
	 *
	 * @return Zumba\PHPUnit\Extensions\ElasticSearch\DataSet\DataSet
	 */
	public function getElasticSearchDataSet() {
		$dataset = new \Zumba\PHPUnit\Extensions\ElasticSearch\DataSet\DataSet($this->getElasticSearchConnector());
		$dataset->setFixture([
			'some_index' => [
				'some_type' => [
					['name' => 'Document 1'],
					['name' => 'Document 2']
				]
			]
		]);
		return $dataset;
	}

	public function testRead() {
		$result = $this->getElasticSearchConnector()->getConnection()->search(['index' => 'some_index']);
		$this->assertEquals(2, $result['hits']['total']);
	}

}
```

[See full working example.](https://github.com/zumba/elasticsearchunit/blob/master/examples/PizzaTraitTest.php)

## Testing with Docker/VM etc

If Elasticsearch is not running on localhost, you can provide the hostname of your Elasticsearch instance via environment variables:

```bash
ES_TEST_HOST=http://docker:9200 ./bin/phpunit
```

## Elasticsearch Version Support

This branch/version is compatible with ElasticSearch 5+. If you are using older versions of ElasticSearch,
please check the v1.x from this library.
