<?php
if (! defined ( 'ABSPATH' )) {
	exit ();
}

?>
<table class="ccas_widget_table">
	<thead>
		<tr>
			<th><?php echo _e( 'Name', CED_CAF_TXTDOMAIN ) ?></th>
			<th><?php echo _e( 'Color Code', CED_CAF_TXTDOMAIN ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ( $terms as $key => $val ) {
			$color = '#ff6699';
			if( ! empty( $instanceRef[ $val->name.'_colorVal' ] ) ) {
				$color = $instanceRef[$val->name.'_colorVal'];
			}
			?>
			<tr>
				<td>
					<?php echo $val->name;?>
				</td>
				<td class="ccas_colorpicker_wrapper">
					<input type="text" class="ccas_colorpicker_input" readOnly id="<?php echo $val->name.'valueInput';?>" name="<?php echo $thisRef.'['.$val->name.'_colorVal]' ;?>" value="<?php echo $color;?>">
				</td>
			</tr>
		<?php 
		}
		?>
	</tbody>
</table>
