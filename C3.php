<?php namespace Model\C3;

use Model\Core\Module;

class C3 extends Module
{
	public function lineChart($list, array $options = [])
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
					'columns' => $chartColumns,
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
			c3.generate(chartOptions);
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
