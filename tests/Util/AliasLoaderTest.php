<?php

class AliasLoaderTest extends PHPUnit_Framework_TestCase
{
	public function testLoader()
	{
		// Mock random server status.
		$_SERVER['HTTP_HOST'] = 'localhost';
    	$_SERVER['SCRIPT_NAME'] = '/www/index.php';

    	// Test assets get (remember PUBLIC folder is set to 'public')
    	$this->assertEquals(Util::base('foo'), 'http://localhost/www/foo');
    	$this->assertEquals(Util::css('foo.css'), 'http://localhost/www/public/css/foo.css');
    	$this->assertEquals(Util::js('foo.js'), 'http://localhost/www/public/js/foo.js');
    	$this->assertEquals(Util::img('foo.png'), 'http://localhost/www/public/images/foo.png');
	}
}