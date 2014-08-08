<?php

  echo $this->Form->input(
    'Message.subject',
    [
      'div' => ['class' => 'input required'],
      'label' => __('user_contact_subject'),
      'required' => 'required',
      'type' => 'text'
    ]
  );
	echo $this->Form->label('Message.text', __('user_contact_message'));
	echo $this->Form->textarea('Message.text', ['style' => 'height: 10em']);
	$checked = true;
	if (isset($this->request->data['Message']['carbon_copy'])) {
		$checked = $this->request->data['Message']['carbon_copy'];
	}
	echo $this->Form->input(
		'Message.carbon_copy',
		array(
			'type' => 'checkbox',
			'checked' => $checked,
			'label' => array(
				'text' => __('user_contact_send_carbon_copy'),
				'style' => 'display: inline;',
			),
		)
	);
	echo $this->Form->submit(__('Submit'), array(
		'class' => 'btn btn-submit'
	));
