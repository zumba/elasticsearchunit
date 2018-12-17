<?php

use Zumba\PHPUnit\Extensions\ElasticSearch\Client\Connector;

class ConnectorTest extends \PHPUnit_Framework_TestCase {

	public function testGeneralConnection() {
		$clientBuilder = \Elasticsearch\ClientBuilder::create();
		if (getenv('ES_TEST_HOST')) {
			$clientBuilder->setHosts([getenv('ES_TEST_HOST')]);
		}
		$connector = new Connector($clientBuilder->build());
		$this->assertInstanceOf('Zumba\PHPUnit\Extensions\ElasticSearch\Client\Connector', $connector);
		$connection = $connector->getConnection();

		$this->deleteIfPresent($connection);
		$response = $connection->index([
			'index' => 'testing',
			'type' => 'test',
			'id' => 1,
			'body' => ['testfield' => 'testvalue'],
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
	
	private function deleteIfPresent($connection) {
		try {
			$response = $connection->delete([
				'index' => 'testing',
				'type' => 'test',
				'id' => 1
			]);

			return $response;
		} catch (\Exception $e) {
			// ignore
		}
	}

}
