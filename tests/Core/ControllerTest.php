<?php

use Core\Core\Controller;
use Core\Core\Core;

class ControllerTest extends \PHPUnit\Framework\TestCase
{

    public function setUp() : void
    {
        $core = Core::getInstance(new \Core\Container\Container(__DIR__ . '/../MockApp'));

        $this->container = $core->getContainer();
        $config = $this->container['config'];
        $config['viewsPath'] = __DIR__ . '/../MockApp/MockViews';
        $this->container['config'] = $config;
    }

	public function testGetContainerObjects()
	{
		$con = new AnotherTestController();
        $con->setContainer($this->container);

		$this->assertSame($this->container['request'], $con->getRequest());
	}

	public function testRender()
	{
		$con = new AnotherTestController();
        $con->setContainer($this->container);


		// Try rendering view with no passed data
		$view = 'MockView';
		$result = $con->bufferIt($view);

		// Output string should be same as content of MockView.php file
		$this->expectOutputString(file_get_contents($this->container['config']['viewsPath'].'/'.$view.'.php'));
		echo $result;
	}

	public function testRenderDynamicPage()
	{
		$con = new AnotherTestController();

        $con->setContainer($this->container);

		// Used view files
		$view = 'MockDynamicView';
		$compareView = 'MockDynamicViewCompare';

		// Buffer view to nest in main MockView
		$data['content'] = '<div>Test</div>';

		// Output main and nested view
		$res = $con->bufferIt($view, $data);

		// Output string should be same as content of MockNestedViewTest.php file
		$this->expectOutputString(file_get_contents($this->container['config']['viewsPath'].'/'.$compareView.'.php'));
		echo $res;
	}

    public function testBuffer()
    {
        $con = new AnotherTestController();

        $con->setContainer($this->container);

        $view = 'MockView';

        $this->assertEquals($con->renderIt($view, [])->getBody(), <<<TAG
<!DOCTYPE html>
<html>
<head>
</head>
<body>
</body>
</html>
TAG
);
    }

    /**
     * @expectedException Core\Core\Exceptions\NotFoundException
     */
    public function testNotFound()
    {
        $con = new AnotherTestController();

        $con->setContainer($this->container);

        $con->notFoundIt();
    }

    public function testContainer()
    {
        $c = new AnotherTestController;

        $container = new \Core\Container\Container(__DIR__ . '/../MockApp');
        $c->setContainer($container);

        $this->assertEquals($container, $c->getApp());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArgument()
    {
        $c = new AnotherTestController;

        $container = new \Core\Container\Container(__DIR__ . '/../MockApp');
        $c->setContainer($container);

        $c->getUknown();
    }
}

class AnotherTestController extends Controller
{
	public function getRequest()
	{
		return $this->container['request'];
	}

	public function getResponse()
	{
		return $this->response;
	}

    public function bufferIt($view, $data = [])
    {
    	return $this->buffer($view, $data);
    }

    public function renderIt($view, $data = [])
    {
        return $this->render($view, $data);
    }

    public function notFoundIt()
    {
        $this->notFound();
    }

    public function getFoo()
    {
        return $this->foo;
    }

    public function getFoo2()
    {
        return $this->container['foo'];
    }

    public function getUknown()
    {
        return $this->uknown;
    }

    public function getApp()
    {
        return $this->container;
    }
}