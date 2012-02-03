<?php

class MailObfuscatorHelper extends AppHelper {
	public $helpers = array(
		'Html',
	);

	protected static $count = 0;

	public function createLink($addr, $link_content) {
		$rand = self::$count;
		self::$count += 1;
		echo $this->Html->script('/app/plugins/mail_obfuscator/webroot/js/rot13.js', array( 'inline' => TRUE, 'once' => TRUE ));
		// 
		//build the mailto link 
		$unencrypted_link = '<a href="mailto:' . $addr . '">' . $link_content . '</a>';
		//build this for people with js turned off 
		$noscript_link = '<noscript>' . $link_content . ' &lt;<span style="unicode-bidi:bidi-override;direction:rtl;">' . strrev($addr) . '</span>&gt;</noscript>';
		// $noscript_link = '<noscript>[You need to have Javascript enabled to see this mail address.]</noscript>';
		//put them together and encrypt 
		$encrypted_link = '<span id=\'moh_'.$rand.'\'></span><script type="text/javascript">Rot13.write(\'' . str_rot13($unencrypted_link) . '\', \'moh_'.$rand.'\');</script>' . $noscript_link;

		return $encrypted_link;
	}

}
?>