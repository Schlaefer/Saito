<?php

	namespace Saito\Validation;

	class SaitoValidationProvider {

		/**
		 * validator checking if value is unique case insensitive
		 *
		 * simplified version Cake\ORM\Table\validateUnique and ORM\Rule\isUnique
		 *
		 * @param $value
		 * @param array $context
		 * @return bool
		 */
		public static function validateIsUniqueCiString($value, array $context) {
			$field = $context['field'];
			$Table = $context['providers']['table'];
			$primaryKey = $Table->primaryKey();

			$conditions = ["LOWER($field)" => mb_strtolower($value)];
			if ($context['newRecord'] === false) {
				$conditions['NOT'] = [$primaryKey => $context['data'][$primaryKey]];
			}

			return !$Table->exists($conditions);
		}

	}
