<p>
	<label>
		<?php _e("Title", 'kalendas' ); ?>:
		<input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" style="width: 100%" value="<?php echo $instance['title']; ?>" />
	</label>
</p>

<p>
	<label>
		<?php _e("Google calendar feed", 'kalendas' ); ?>:
		<input type="text" id="<?php echo $this->get_field_id('source'); ?>" name="<?php echo $this->get_field_name('source'); ?>" style="width: 100%" value="<?php echo $instance['source']; ?>" />
	</label>
</p>
