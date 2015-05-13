<?php

namespace Zumba\PHPUnit\Extensions\ElasticSearch;
use \Zumba\PHPUnit\Extensions\ElasticSearch\DataSet\DataSet;

trait TestTrait {

	/**
	 * Setup the Elastic Search db with fixture data.
	 *
	 * @return void
	 * @before
	 */
	public function elasticSearchSetUp() {
		$this->getElasticSearchDataSet()
			->deleteIndices()
			->buildIndices();
	}

	/**
	 * Cleanup after test.
	 *
	 * @return void
	 * @after
	 */
	public function elasticSearchTearDown() {
		$this->getElasticSearchDataSet()->deleteIndices();
	}

	/**
	 * Retrieve a Elastic Search connection client.
	 *
	 * @return Zumba\PHPUnit\Extensions\ElasticSearch\Client\Connector
	 */
	protected abstract function getElasticSearchConnector();

	/**
	 * Retrieve a dataset object.
	 *
	 * @return Zumba\PHPUnit\Extensions\ElasticSearch\DataSet\DataSet
	 */
	protected abstract function getElasticSearchDataSet();

}
