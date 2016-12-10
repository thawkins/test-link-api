<?php
namespace Skalda\TestLinkAPI\Tests\Mockup;

use IXR\Message\Message;

class ClientMock
{
	private $method;
	private $error;
	private $response;
	private $args;

	public function __construct()
	{
	}

	public function query()
	{
		$args = func_get_args();
		$method = array_shift($args);
		$this->args = $args;
		$this->method = $method;
	}

	public function getResponse()
	{
		return $this->response;
	}

	public function isError()
	{
		return (is_object($this->error));
	}

	public function getError()
	{
		return $this->error;
	}

	public function getErrorCode()
	{
		return $this->error->code;
	}

	public function getErrorMessage()
	{
		return $this->error->message;
	}

	public function mockResponse($file)
	{
		$content = file_get_contents($file);
		$response = new Message($content);
		$response->parse();

		$this->response = $response->params[0];
	}

	public function mockError($code, $message)
	{
		$error = new \stdClass();
		$error->code = $code;
		$error->message = $message;
		$this->error = $error;
	}

	public function getCalledMethod()
	{
		return $this->method;
	}

	public function getCalledArgument($name)
	{
		return isset($this->args[0][$name])?$this->args[0][$name]:null;
	}
}