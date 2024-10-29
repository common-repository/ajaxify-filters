<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<table>
	<thead>
		<tr>
			<th><?php _e( 'Name', CED_CAF_TXTDOMAIN ); ?></th>
			<th><?php _e( 'Label', CED_CAF_TXTDOMAIN ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php 
		if ( !empty( $terms ) and is_array( $terms ) ) {
			foreach ( $terms as $key => $val ) {?>
				<tr>
					<td><?php echo $val->name;?></td>
					<td>
						<input type="text" id="<?php echo $val->name;?>" name="<?php echo $thisRef.'['.$val->name.']' ;?>" value="<?php if(!empty($instanceRef[$val->name])){echo $instanceRef[$val->name];}?>">
					</td>
				</tr>
			<?php 
			}
		}
		?>
	</tbody>
</table>
