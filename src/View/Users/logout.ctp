<?php
  // trigger for logout in JS frontend
  echo $this->Html->tag('div', '', ['class' => 'users logout']);

  $redirect = $this->Html->meta([
    'http-equiv' => 'refresh',
    'content' => '1; ' . $this->webroot
  ]);
  $this->append('meta', $redirect);
