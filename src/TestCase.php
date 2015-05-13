<?php

namespace Zumba\PHPUnit\Extensions\ElasticSearch;
use \Zumba\PHPUnit\Extensions\ElasticSearch\DataSet\DataSet;

abstract class TestCase extends \PHPUnit_Framework_TestCase {

	/**
	 * Setup the Elastic Search db with fixture data.
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		if (!class_exists('Elasticsearch\\Client')) {
			$this->markTestSkipped('The Elastic Search extension is not available.');
			return;
		}
		$this->getDataSet()
			->deleteIndices()
			->buildIndices();
	}

	/**
	 * Cleanup after test.
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		$this->dataSet->deleteIndices();
	}

	/**
	 * Retrieve a Elastic Search connection client.
	 *
	 * @return Zumba\PHPUnit\Extensions\ElasticSearch\Client\Connector
	 */
	protected abstract function getConnector();

	/**
	 * Retrieve a dataset object.
	 *
	 * @return Zumba\PHPUnit\Extensions\ElasticSearch\DataSet\DataSet
	 */
	protected abstract function getDataSet();

}