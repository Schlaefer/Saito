<?php

	App::uses('Controller', 'Controller');
	App::uses('View', 'View');
	App::uses('GlasenuhrHelper', 'View/Helper');

class GlasenuhrHelperTest extends CakeTestCase {

	public function setUp() {
		parent::setUp();

		$Controller = new Controller();
		$View = new View($Controller);
		$this->Glasenuhr = new GlasenuhrHelper($View);
	}

	public function tearDown() {
		parent::tearDown();
		unset($this->Glasenuhr);
		ClassRegistry::flush();
	}

	public function testFtime() {

		$watches = array (
				0 => __('Nacht'),
				1 => __('Morgen'),
				2 => __('Vormittag'),
				3 => __('Nachmittag'),
				4 => __('Freiwache'),
				5 => __('Abend'),
		);

		$this->Glasenuhr->setup(array('watches' => $watches));

		$input 		= mktime(0, 0);
		$expected = '8 Gl. Abend';
		$result 	=	$this->Glasenuhr->ftime($input);
		$this->assertSame($expected, $result);

		$input 		= mktime(0, 29);
		$expected = '8 Gl. Abend';
		$result 	=	$this->Glasenuhr->ftime($input);
		$this->assertSame($expected, $result, "%s (" . strftime('%H:%M', $input). ')');

		$input 		= mktime(0, 30, 0);
		$expected = '1 Gl. Nacht';
		$result 	=	$this->Glasenuhr->ftime($input);
		$this->assertSame($expected, $result, "%s (" . strftime('%H:%M', $input). ')');

		$input 		= mktime(0, 31, 0);
		$expected = '1 Gl. Nacht';
		$result 	=	$this->Glasenuhr->ftime($input);
		$this->assertSame($expected, $result, "%s (" . strftime('%H:%M', $input). ')');

		$input 		= mktime(4, 59, 0);
		$expected = '1 Gl. Morgen';
		$result 	=	$this->Glasenuhr->ftime($input);
		$this->assertSame($expected, $result, "%s (" . strftime('%H:%M', $input). ')');

		$input 		= mktime(9, 0, 0);
		$expected = '2 Gl. Vormittag';
		$result 	=	$this->Glasenuhr->ftime($input);
		$this->assertSame($expected, $result, "%s (" . strftime('%H:%M', $input). ')');

		$input 		= mktime(13, 31);
		$expected = '3 Gl. Nachmittag';
		$result 	=	$this->Glasenuhr->ftime($input);
		$this->assertSame($expected, $result, "%s (" . strftime('%H:%M', $input). ')');

		$input 		= mktime(18, 06);
		$expected = '4 Gl. Freiwache';
		$result 	=	$this->Glasenuhr->ftime($input);
		$this->assertSame($expected, $result, "%s (" . strftime('%H:%M', $input). ')');

		$input 		= mktime(22, 44);
		$expected = '5 Gl. Abend';
		$result 	=	$this->Glasenuhr->ftime($input);
		$this->assertSame($expected, $result, "%s (" . strftime('%H:%M', $input). ')');

		$input 		= mktime(15, 17);
		$expected = '6 Gl. Nachmittag';
		$result 	=	$this->Glasenuhr->ftime($input);
		$this->assertSame($expected, $result, "%s (" . strftime('%H:%M', $input). ')');

		$input 		= mktime(23, 50);
		$expected = '7 Gl. Abend';
		$result 	=	$this->Glasenuhr->ftime($input);
		$this->assertSame($expected, $result, "%s (" . strftime('%H:%M', $input). ')');

	}

}
?>