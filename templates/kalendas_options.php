<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2><?php _e( 'Kalendas\' Options', 'kalendas' ); ?></h2>
	<form method="post" action="<?php echo remove_query_arg(array('mode', 'id')); ?>">
		<input type="hidden" name="kalendas_hidden_field" value="Y"><input type="hidden" name="mode_x" value="manage_x">
		<table class="form-table">
			<tr>
				<td><?php _e("Date format", 'kalendas' ); ?>: </td>
				<td><input type='text' name='date_format' value='<?php echo $options['date_format']; ?>'> <a href='http://codex.wordpress.org/Formatting_Date_and_Time' target='_BLANK'><?php _e('Doumentation about date format.', 'kalendas'); ?></a></td>
			</tr>
			<tr>
				<td><?php _e("Show events since", 'kalendas' ); ?>: </td>
				<td><select name="days_begin">
						<option value='0'<?php if ($options['days_begin'] == 0) echo('selected'); ?>><?php echo _e('Today', 'kalendas') ?></option>
						<option value='1'<?php if ($options['days_begin'] == 1) echo('selected'); ?>><?php echo _e('One day before', 'kalendas') ?></option>
						<option value='2'<?php if ($options['days_begin'] == 2) echo('selected'); ?>><?php echo _e('Two days before', 'kalendas') ?></option>
						<option value='7'<?php if ($options['days_begin'] == 7) echo('selected'); ?>><?php echo _e('One week before', 'kalendas') ?></option>
						<option value='14'<?php if ($options['days_begin'] == 14) echo('selected'); ?>><?php echo _e('Two weeks before', 'kalendas') ?></option>
						<option value='31'<?php if ($options['days_begin'] == 31) echo('selected'); ?>><?php echo _e('One month before', 'kalendas') ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td><?php _e("Show events until", 'kalendas' ); ?>: </td>
				<td><select name="days_end">
						<option value='0'<?php if ($options['days_end'] == 0) echo('selected'); ?>><?php echo _e('Today', 'kalendas') ?></option>
						<option value='1'<?php if ($options['days_end'] == 1) echo('selected'); ?>><?php echo _e('One day after', 'kalendas') ?></option>
						<option value='2'<?php if ($options['days_end'] == 2) echo('selected'); ?>><?php echo _e('Two days after', 'kalendas') ?></option>
						<option value='7'<?php if ($options['days_end'] == 7) echo('selected'); ?>><?php echo _e('One week after', 'kalendas') ?></option>
						<option value='14'<?php if ($options['days_end'] == 14) echo('selected'); ?>><?php echo _e('Two weeks after', 'kalendas') ?></option>
						<option value='31'<?php if ($options['days_end'] == 31) echo('selected'); ?>><?php echo _e('One month after', 'kalendas') ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td><?php _e("Show at the most", 'kalendas' ); ?>: </td>
				<td><input type='text' name='max_events' value='<?php echo $options['max_events']; ?>'></td>
			</tr>
			<tr>
				<td><?php _e("Update every", 'kalendas' ); ?>: </td>
				<td><select name="hours_update">
						<option value='1'<?php if ($options['hours_update'] == 1) echo('selected'); ?>><?php echo _e('hour', 'kalendas') ?></option>
						<option value='24'<?php if ($options['hours_update'] == 24) echo('selected'); ?>><?php echo _e('day', 'kalendas') ?></option>
					</select>
				</td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="<?php _e('Update Options', 'kalendas' ) ?>" />
		</p>
	</form>
</div>
