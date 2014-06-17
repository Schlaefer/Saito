<?php

	App::uses('SimpleSearchString', 'Lib');

	class SimpleSearchStringTest extends CakeTestCase {

		public function testReplaceOperators() {
			$length = 4;
			$in = 'zap -foo-bar baz';
			$S = new SimpleSearchString($in, $length);

			$result = $S->replaceOperators();
			$expected = '+zap -"foo-bar" +baz';
			$this->assertEquals($expected, $result);

			$in = 'zap "foo-bar" baz';
			$S->setString($in);
			$result = $S->replaceOperators();
			$expected = '+zap +"foo-bar" +baz';
			$this->assertEquals($expected, $result);
		}

		public function testValidateMinWordLength() {
			$length = 4;
			$in = '+test -fooo';
			$S = new SimpleSearchString($in, $length);
			$result = $S->validateLength();
			$this->assertTrue($result);

			$in = '+tes -foo';
			$S->setString($in);
			$result = $S->validateLength();
			$this->assertFalse($result);

			$in = 'test +foo';
			$S->setString($in);
			$result = $S->validateLength();
			$this->assertFalse($result);

			$in = 'wm-foo';
			$S->setString($in);
			$result = $S->validateLength();
			$this->assertTrue($result);

			$in = 'w-m';
			$S->setString($in);
			$result = $S->validateLength();
			$this->assertFalse($result);

			$in = 'wmf';
			$S->setString($in);
			$result = $S->validateLength();
			$this->assertFalse($result);

			$in = '';
			$S->setString($in);
			$result = $S->validateLength();
			$this->assertFalse($result);

			$in = 'wm foo';
			$S->setString($in);
			$result = $S->validateLength();
			$this->assertFalse($result);

			$in = '"wm foo';
			$S->setString($in);
			$result = $S->validateLength();
			$this->assertFalse($result);

			$in = '"wm foo"';
			$S->setString($in);
			$result = $S->validateLength();
			$this->assertTrue($result);
		}

	}
