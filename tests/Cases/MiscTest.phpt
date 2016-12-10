<?php
namespace Skalda\TestLinkAPI\Tests\Cases;

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/BaseTestCase.php';


class MiscTest extends BaseTestCase
{

	public function testCheckConnectivity()
	{
		$this->clientMock->mockResponse(__DIR__ . '/../Files/XML/tl.ping.xml');
		$response = $this->testLinkAPI->checkConnectivity();

		Assert::equal('tl.ping', $this->clientMock->getCalledMethod());
		Assert::true($response);
	}

	public function testCheckApiKey()
	{
		$this->clientMock->mockResponse(__DIR__ . '/../Files/XML/tl.checkDevKey.xml');
		$response = $this->testLinkAPI->checkApiKey('Test');

		Assert::equal('tl.checkDevKey', $this->clientMock->getCalledMethod());
		Assert::true($response);
	}

	public function testGetTestLinkVersion()
	{
		$this->clientMock->mockResponse(__DIR__ . '/../Files/XML/tl.testLinkVersion.xml');
		$response = $this->testLinkAPI->getTestLinkVersion();

		Assert::equal('tl.testLinkVersion', $this->clientMock->getCalledMethod());
		Assert::equal('1.9.15', $response);
	}

	public function testGetAboutApi()
	{
		$this->clientMock->mockResponse(__DIR__ . '/../Files/XML/tl.about.xml');
		$response = $this->testLinkAPI->getAboutApi();

		Assert::equal('tl.about', $this->clientMock->getCalledMethod());
		Assert::equal('Testlink API Version: 1.0 initially written by Asiel Brumfield\n with contributions by TestLink development Team', $response);
	}

	public function testSetTestMode()
	{

	}

	public function testGetFullPath()
	{

	}

	public function testGetAllFullPath()
	{

	}
}

$test = new MiscTest();
$test->run();