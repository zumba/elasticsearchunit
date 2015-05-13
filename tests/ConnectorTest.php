<?php

use Zumba\PHPUnit\Extensions\ElasticSearch\Client\Connector;

class ConnectorTest extends \PHPUnit_Framework_TestCase {

	public function testGeneralConnection() {
		$connector = new Connector(new \Elasticsearch\Client());
		$this->assertInstanceOf('Zumba\PHPUnit\Extensions\ElasticSearch\Client\Connector', $connector);
		$connection = $connector->getConnection();
		$response = $connection->index([
			'index' => 'testing',
			'type' => 'test',
			'id' => 1,
			'body' => ['testfield' => 'testvalue']
		]);
		$this->assertTrue($response['created']);
		$this->assertEquals(1, $response['_id']);

		$response = $connection->delete([
			'index' => 'testing',
			'type' => 'test',
			'id' => 1
		]);
		$this->assertTrue($response['found']);
	}

}