<?php namespace Model\C3;

use Model\Core\Module;

class C3 extends Module
{
	/**
	 * @param iterable $list
	 * @param array $options
	 * @throws \Exception
	 */
	public function lineChart(iterable $list, array $options = [])
	{
		$options = array_merge([
			'id' => 'line-chart',
			'fields' => [],
			'label' => null,
			'label-type' => null, // supported at the moment: datetime
			'values-type' => null, // supported at the moment: price
		], $options);

		$chartColumns = [
			['x'],
		];

		$c_label = 1;
		foreach ($options['fields'] as $idx => $f) {
			if (is_numeric($idx))
				$label = $this->getLabel($f);
			else
				$label = $idx;
			$chartColumns[($c_label++)] = [
				$label,
			];
		}

		foreach ($list as $elIdx => $el) {
			$chartColumns[0][] = $options['label'] ? $el[$options['label']] : $elIdx;
			foreach ($options['fields'] as $idx => $f)
				$chartColumns[$idx + 1][] = $el[$f];
		}
		?>
		<div id="<?= entities($options['id']) ?>"></div>
		<script>
			<?php
			$chartOptions = [
				'bindto' => '#' . $options['id'],
				'data' => [
					'x' => 'x',
					'columns' => [
						['x'],
					],
				],
			];

			switch ($options['label-type']) {
				case 'datetime':
					if (!isset($options['label-format']))
						$options['label-format'] = '%d/%m/%Y';

					$chartOptions['axis'] = [
						'x' => [
							'type' => 'timeseries',
							'tick' => [
								'format' => $options['label-format'],
							],
						],
					];
					break;
			}
			?>
			var chartOptions = <?=json_encode($chartOptions, JSON_PRETTY_PRINT)?>;
			<?php
			switch ($options['values-type']) {
			case 'price':
			?>
			chartOptions['tooltip'] = {
				'format': {
					'value': value => {
						return makePrice(value);
					},
				}
			};
			<?php
			break;
			}
			?>
			if (typeof c3_chartes === 'undefined')
				var c3_chartes = {};
			c3_chartes['<?= entities($options['id']) ?>'] = c3.generate(chartOptions);
			setTimeout(function () {
				c3_chartes['<?= entities($options['id']) ?>'].load({
					'x': 'x',
					'columns': <?=json_encode($chartColumns)?>
				});
			}, 100);
		</script>
		<?php
	}

	/**
	 * @param iterable $list
	 * @param array $options
	 * @throws \Exception
	 */
	public function pieChart(iterable $list, array $options = [])
	{
		$options = array_merge([
			'id' => 'pie-chart',
			'field' => null,
			'label' => null,
			'text' => null,
			'label-type' => null,
			'values-type' => null, // supported at the moment: price
		], $options);

		if (!$options['label'] or !$options['field'])
			return;

		$chartColumns = [];

		$numbersDirection = null;
		foreach ($list as $elIdx => $el) {
			if ($options['text']) {
				if (!is_string($options['text']) and is_callable($options['text']))
					$label = call_user_func($options['text'], $el);
				else
					$label = $options['text'];
			} else {
				if (is_object($el)) {
					$form = $el->getForm();
					if ($form[$options['label']])
						$label = $form[$options['label']]->getText();
					else
						$label = $el[$options['label']];
				} else {
					$label = $el[$options['label']] ?? '';
				}
			}

			$value = $el[$options['field']];
			if (!is_numeric($value)) {
				echo 'Unsupported non-numeric value for pie chart';
				return;
			}

			if ($numbersDirection) {
				if (($numbersDirection == 1 and $value < 0) or ($numbersDirection == -1 and $value > 0)) {
					echo 'Cannot mix negative and positive numbers in a pie chart';
					return;
				}
			} else {
				$numbersDirection = $value > 0 ? 1 : -1;
			}
			if ($value < 0)
				$value = abs($value);

			$chartColumns[] = [
				$label,
				$value,
			];
		}
		?>
		<div id="<?= entities($options['id']) ?>"></div>
		<script>
			<?php
			$chartOptions = [
				'bindto' => '#' . $options['id'],
				'data' => [
					'type' => 'pie',
					'columns' => [],
				],
			];

			switch ($options['label-type']) {
				case 'datetime':
					if (!isset($options['label-format']))
						$options['label-format'] = '%d/%m/%Y';

					$chartOptions['axis'] = [
						'x' => [
							'type' => 'timeseries',
							'tick' => [
								'format' => $options['label-format'],
							],
						],
					];
					break;
			}
			?>
			var chartOptions = <?=json_encode($chartOptions, JSON_PRETTY_PRINT)?>;
			<?php
			switch ($options['values-type']) {
			case 'price':
			?>
			chartOptions['tooltip'] = {
				'format': {
					'value': value => {
						return makePrice(value);
					},
				}
			};
			<?php
			break;
			}
			?>
			if (typeof c3_chartes === 'undefined')
				var c3_chartes = {};
			c3_chartes['<?= entities($options['id']) ?>'] = c3.generate(chartOptions);
			setTimeout(function () {
				c3_chartes['<?= entities($options['id']) ?>'].load({
					'columns': <?=json_encode($chartColumns)?>
				});
			}, 100);
		</script>
		<?php
	}

	/**
	 * Converts a field name in a human-readable label
	 *
	 * @param string $k
	 * @return string
	 */
	public function getLabel(string $k): string
	{
		return ucwords(str_replace(array('-', '_'), ' ', $k));
	}
}
