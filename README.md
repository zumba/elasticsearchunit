ElasticSearchUnit is a PHPUnit extension for test cases that utilize the official ElasticSearch Client as their data source.

## Requirements

* PHP 5.3+
* PHPUnit ~3.7, ~4.0
* ElasticSearch ~1.0

## Testing

1. Install dependencies `composer install -dev`
1. Run `./bin/phpunit`

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
	public function getElasticSearchConnection() {
		return new \Elasticsearch\Client();
	}

	/**
	 * Get the dataset to be used for this test.
	 *
	 * @return Zumba\PHPUnit\Extensions\ElasticSearch\DataSet\DataSet
	 */
	public function getElasticSearchDataSet() {
		$dataset = new \Zumba\PHPUnit\Extensions\ElasticSearch\DataSet\DataSet($this->getElasticSearchConnection());
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
		$result = $this->getElasticSearchConnection()->search(['index' => 'some_index']);
		$this->assertEquals(2, $result['hits']['total']);
	}

}
```

[See full working example.](https://github.com/zumba/elasticsearchunit/blob/master/examples/PizzaTraitTest.php)

## Note about PHP and PHPUnit Versions

PHP 5.3 is supported for PHPUnit ~3.7 by way of extending `\Zumba\PHPUnit\Extensions\ElasticSearch\TestCase`. PHPUnit 4 is working with this testcase, however it is not actively supported.

PHP 5.4 is supported via use of the `\Zumba\PHPUnit\Extensions\ElasticSearch\TestTrait` trait. It currently is supporting PHPUnit 4 `@before` and `@after` but can be used in PHPUnit ~3.7 by either aliasing the `elasticSearchSetUp` and `elasticSearchTearDown` to `setUp` and `tearDown`, or by calling `elasticSearchSetUp` and `elasticSearchTearDown` in your respective methods.
