<?php

    namespace Cron\Model\Behavior;

	use Cake\ORM\Behavior;
    use Saito\App\Registry;

    class CronBehavior extends Behavior {

        protected $_defaultConfig = [
           'implementedMethods' => [
               // @todo 3.0 not used?
               'clearHistoryCron' => 'clearHistoryCron'
           ]
        ];

        public function initialize(array $config) {
            $cron = Registry::get('Cron');
            foreach ($config as $func => $options) {
                $cron->addCronJob(
                    $options['id'],
                    $options['due'],
                    [$this->_table, $func]
                );
            }
        }

	}
