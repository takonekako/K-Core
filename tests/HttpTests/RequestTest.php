<?php
use Core\Http\Request;

class RequestTest extends \PHPUnit\Framework\TestCase
{
	public function testConstruct()
	{
		// Mock random request
		$server = [
			'SERVER_NAME' => 'localhost',
			'SERVER_PORT' => 80,
			'HTTP_HOST' => 'localhost',
			'HTTP_USER_AGENT' => 'RandomAgent',
			'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
			'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5',
			'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
			'REMOTE_ADDR' => '127.0.0.1',
			'SCRIPT_NAME' => 'index.php',
			'SCRIPT_FILENAME' => '',
			'SERVER_PROTOCOL' => 'HTTP/1.1',
			'REQUEST_TIME' => time(),
			'REQUEST_URI' => 'foo/bar/2',
			'QUERY_STRING' => '',
			'REQUEST_METHOD' => 'PUT'
		];

		$request1 = new Request($server, [], [], [], [], []);

		// Test URI
		$this->assertEquals($request1->getUri(), 'foo/bar/2');

		// Test method
		$this->assertEquals($request1->getMethod(), 'PUT');

		// Test get/set protocol
		$this->assertEquals($request1->getProtocolVersion(), 'HTTP/1.1');
        $request1->setProtocolVersion('HTTP/1.0');
        $this->assertEquals($request1->getProtocolVersion(), 'HTTP/1.0');

		// Test get user agent
		$this->assertEquals($request1->getUserAgent(), 'RandomAgent');

		// Test get random variables
		$this->assertEquals($request1->headers->get('HTTP_HOST'), 'localhost');
		$this->assertEquals($request1->server->get('SERVER_NAME'), 'localhost');
		$this->assertEquals($request1->server->get('SERVER_PORT'), 80);

        // Test get/set body
        $request1->setBody('test body');
        $this->assertEquals('test body', $request1->getBody());
	}

	public function testGetAndIs()
	{
		// Mock random request
		$server['REQUEST_URI'] = '/foo/bar/';
		$server['SCRIPT_NAME'] = '/index.php';
		$server['QUERY_STRING'] = '?foo=2&bar=3';
		$server['REQUEST_METHOD'] = 'POST';

		$request2 = new Request($server);

		// Test URI
		$this->assertEquals($request2->getUri(), 'foo/bar');

		// Test method
		$this->assertEquals($request2->getMethod(), 'POST');

		// Test isPost ?
		$this->assertTrue($request2->isPost());

		// Mock random request
		$server['REQUEST_URI'] = '/public/foo/bar/';
		$server['SCRIPT_NAME'] = '/public/index.php';
		$server['QUERY_STRING'] = '?foo=2&bar=3';
		$server['REQUEST_METHOD'] = 'GET';
		$server['SERVER_PROTOCOL'] = 'HTTP/1.1';

		$request3 = new Request($server);

		// Test URI
		$this->assertEquals($request3->getUri(), 'foo/bar');

		// Test get protocol
		$this->assertEquals($request3->getProtocolVersion(), 'HTTP/1.1');

		// Test is methods
		$this->assertTrue($request3->isGet());

		$this->assertFalse($request3->isPut());

		$this->assertFalse($request3->isPatch());

		$this->assertFalse($request3->isOptions());

        $this->assertFalse($request3->isAjax());

		// Mock random request
		$server['REQUEST_URI'] = '/public/foo/bar/';
		$server['SCRIPT_NAME'] = '/public/index.php';
		$server['QUERY_STRING'] = '?foo=2&bar=3';
		$server['REQUEST_METHOD'] = 'DELETE';
		$server['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $server['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';

		$request3 = new Request($server);

		$this->assertTrue($request3->isDelete());

        $request3->setMethod('HEAD');
        $this->assertTrue($request3->isHead());

        $this->assertTrue($request3->isAjax());
	}

	public function testPostAndGet()
	{
		$server['REQUEST_URI'] = '/public/foo/bar/';
		$server['REQUEST_METHOD'] = 'POST';
		$server['SCRIPT_NAME'] = '/public/index.php';

		$post['foo'] = 'bar';
		$post['bar'] = 'foo';

		$get['goo'] = 'gar';

		$request = new Request($server, $get, $post);
	
		$this->assertEquals($request->post->get('foo'), 'bar');

		$this->assertEquals($request->post->all(), ['foo'=>'bar','bar'=>'foo']);

		$this->assertEquals($request->get->get('goo'), 'gar');

        $request->setUri('test/2');

        $this->assertEquals('test/2', $request->getUri());
	}

	public function testSegment()
	{
		// Mock random request
        $_TEST_SERVER['REQUEST_URI'] = '/public/foo/bar/2';
        $_TEST_SERVER['SCRIPT_NAME'] = '/public/index.php';
        $_TEST_SERVER['QUERY_STRING'] = '';
        $_TEST_SERVER['REQUEST_METHOD'] = 'GET';

		$request1 = new Request($_TEST_SERVER);

		$this->assertEquals($request1->getUriSegment(0), 'foo');

		$this->assertEquals($request1->getUriSegment(2), '2');

        $this->assertEquals($request1->getUriSegment(100), false);
	}

	public function testGetHeaders()
	{
        $_TEST_SERVER['REQUEST_URI'] = '/public/foo/bar/2';
        $_TEST_SERVER['SCRIPT_NAME'] = '/public/index.php';
        $_TEST_SERVER['QUERY_STRING'] = '';
        $_TEST_SERVER['REQUEST_METHOD'] = 'GET';

        $_TEST_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip';
        $headers['HTTP_ACCEPT_ENCODING'] = $_TEST_SERVER['HTTP_ACCEPT_ENCODING'];
        $_TEST_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        $headers['CONTENT_TYPE'] = $_TEST_SERVER['CONTENT_TYPE'];
        $_TEST_SERVER['CONTENT_LENGTH'] = 100;
        $headers['CONTENT_LENGTH'] = $_TEST_SERVER['CONTENT_LENGTH'];

        $req = new Request($_TEST_SERVER);

        $this->assertEquals($req->getHeaders(), $headers);
        $this->assertEquals('gzip', $req->headers->get('HTTP_ACCEPT_ENCODING'));
        $this->assertEquals(100, $req->getHeader('CONTENT_LENGTH'));
        $this->assertEquals(100, $req->getContentLength());
        $req->setHeader('HTTP_REFERRER', 'www.test.com');
        $this->assertEquals('www.test.com', $req->getReferrer());
        $this->assertEquals('application/x-www-form-urlencoded', $req->getContentType());
	}

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidRequest()
    {
        $this->expectException(InvalidArgumentException::class);

        $_TEST_SERVER = [];
        new Request($_TEST_SERVER);
    }

    public function testPutPatchDelete()
    {
        $_TEST_SERVER['REQUEST_URI'] = '/public/index.php';
        $_TEST_SERVER['SCRIPT_NAME'] = '/public/index.php';
        $_TEST_SERVER['QUERY_STRING'] = '';
        $_TEST_SERVER['REQUEST_METHOD'] = 'PUT';
        $_TEST_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        $_TEST_SERVER['HTTP_CONTENT_LENGTH'] = 0;

        $req = new Request($_TEST_SERVER);

        $this->assertEquals('PUT', $req->getMethod());
        $this->assertEquals('application/x-www-form-urlencoded', $req->getContentType());
    }
}