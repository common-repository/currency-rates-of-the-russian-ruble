<h1><?php _e('Currency Rates of Russian Ruble', 'cur-rates-cb'); ?></h1>
<hr>

<table class="cur-rates">
	<caption class="text-left"><h3><?php _e('Last updated', 'cur-rates-cb');?>: <?php echo date_format(date_create(crrr_get_last_date()), 'd.m.Y H:i');?></h3></caption>
	<thead>
		<tr>
			<th><?php _e('Code','cur-rates-cb');?></th>
			<th><?php _e('Units','cur-rates-cb');?></th>
			<th><?php _e('Currency','cur-rates-cb');?></th>
			<th><?php _e('Rate','cur-rates-cb');?></th>
			<th><?php _e('Changes','cur-rates-cb');?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach (crrr_get_currency_rates() as $currencies) {
		if ( gettype($currencies) == 'object' ) {
			foreach ($currencies as $currency) {
		?>
		<tr>
			<td><?php echo $currency->CharCode; ?></td>
			<td><?php echo $currency->Nominal; ?></td>
			<td><?php echo $currency->Name; ?></td>
			<td class="text-center"><?php echo $currency->Value; ?></td>
			<?php $changes = round($currency->Value - $currency->Previous, 4); ?>
			<td class="text-center <?php ($changes > 0 ? printf('rate-up') : printf('rate-down'));?>"><?php ($changes > 0 ? printf('+'.$changes) : printf($changes)); ?></td>
		</tr>
		<?php
				}
			}
		} ?>
	</tbody>
</table>
<hr>

