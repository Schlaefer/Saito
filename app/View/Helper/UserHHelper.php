<?php

App::import('Lib', 'SaitoUser');
App::uses('AppHelper', 'View/Helper');

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class UserHHelper extends AppHelper {
	public $helpers = array ( 
			#App

			#CakePHP
			'Html',
			'Session',);
	/**
	 * generates the JavaSript commands to format the views according to user prefs
	 *
	 * @return string JavaScript commands
	 **/
	public function generateCss($User = NULL) {
			$out = array();
			$out[] = "<style type='text/css'>";

			if (isset($User['id'])) {

					$font_size = $User['user_font_size'];
					if ($font_size != 0)
					{
						$font_size = -1 * (1-$font_size)*20 + 12;
						if (!empty($font_size)) {
								$out[] = "body { font-size:". $font_size ."px; }";
								// scales the the following parameter to a subjective nicer view
								// @td set generaly to 1 1/3 em of font size ?
								 $line_height = number_format( (1 + 1/2) * $font_size, 3, '.', '');
								 $out[] =  "li { line-height:{$line_height}px; }";
//								$out[] =  "li { line-height: 1.45; }";
								 $out[] =  "ul.thread {margin-bottom:{$line_height}px ;}";
						}
					}

					$c_new					= $User['user_color_new_postings'];
					$c_old					= $User['user_color_old_postings'];
					$c_act					= $User['user_color_actual_posting'];

					$a_metatags     = array ( 'link', 'visited', 'hover', 'active' );
					foreach ($a_metatags as $a_metatag) {
							if (!empty($c_old) && $c_old !== '#') {
									$out[] = "li.thread .span_post_type:$a_metatag, li.reply .span_post_type:$a_metatag	{ color: $c_old; }";
							}
							if (!empty($c_new) && $c_new !== '#') {
									$out[] = "li.threadnew .span_post_type:$a_metatag, li.replynew .span_post_type:$a_metatag { color: $c_new; }";
							}
							if (!empty($c_act) && $c_act !== '#') {
									$out[] = "li.actthread .span_post_type:$a_metatag, li.actreply .span_post_type:$a_metatag 	{ color: $c_act; }";
							}
					}
			}
			$out[] = "</style>";
			return implode(" ", $out);
	}

	/**
	 * If input is text and empty return minus.
	 *
	 * If input is array make check all strings in first level and change to minus
	 * if empty
	 *
	 */
	public function minusIfEmpty(&$input) {
		if(is_array($input)) {
			foreach($input as $k => &$v) {
				$v = (empty($v)) ? '–' : $v;
			}
		} else {
			return (empty($input)) ? '–' : $input;
		}
	}

	/**
	 * Translates user types
	 *
	 * @param <type> $type
	 * @return <type>
	 */
	public function type($type) {
		# we could do this cleverer, but we want to write
		# all strings explicitly for Poedit
		switch ($type):
			case 'user':
				return __('ud_user');
			case 'mod':
				return __('ud_mod');
			case 'admin':
				return __('ud_admin');
		endswitch;
	}

	/**
	 * Creates link to user contanct page with image
	 * @param <type> $user
	 * @return <type>
	 */
	public function contact($user) {
		$out = '';
		if ($user['personal_messages'] && is_string($user['user_email'])) {
			$out = $this->Html->link(
							$this->Html->image(
											'email.png',
											array('alt'=> __('homepage_alt'))
							),
							array( 'controller' => 'users', 'action' => 'contact', $user['id']),
							array('escape' => false));
		}
		return $out;
	}

	/**
	 * Creates Homepage Links with Image from Url
	 * @param <type> $url
	 * @return <type>
	 */
	public function homepage($url) {
		$out = $url;
		if (is_string($url)) {
			if(substr($url, 0,4) == 'www.') { $url = 'http://' . $url; }
			if (substr($url, 0,4) == 'http') {
				$out = $this->Html->link(
								$this->Html->image(
												'house.png',
												array('alt'=> __('homepage_alt'))
								),
								$url,
								array('escape' => false));

			}
		}
		return $out;
	}

	/**
	 * calculates user rank depending on posting_count
	 *
	 * @param <type> $number_of_postings
	 * @return <type>
	 */
	public function userRank($number_of_postings = 0) {
			$out = '';
			if (Configure::read('Saito.Settings.userranks_show')){
				 $ranks = explode( "|", Configure::read('Saito.Settings.userranks_ranks')) ;
				 foreach ($ranks as $rank){
						 if (preg_match( '/(\d+)\s*=\s*(.*)/', trim($rank), $matches)) {
								$out = $matches[2];
								if ($number_of_postings <= $matches[1]) {
									break;
									} 
							} else {
									$out .= __('userranks_not_found');
									break;
							}
					}
			}
			return $out;
	}

	public function isMod($user) {
		// @td fix this fubar
		$Collection = new ComponentCollection();
		$User = new SaitoUser($Collection);
		$User->set($user);
		return $User->isMod($user);
	}

}
?>