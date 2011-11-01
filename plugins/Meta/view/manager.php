<?php if (!defined('APPLICATION')) exit();
	echo $this->Form->Open();
	echo $this->Form->Errors();
?>

<h1><?php echo T('Meta'); ?></h1>
<div class="Info">
	<?php echo T('Locale.Usage'); ?>
</div>
<table>
	<tbody>
		<tr>
			<th><?php echo T('Meta Description'); ?></th>
			<td><?php echo $this->Form->TextBox('Meta.Description'); ?></td>
		</tr>
		<tr>
			<th><?php echo T('Meta Keywords'); ?></th>
			<td><?php echo $this->Form->TextBox('Meta.Keywords'); ?></td>
		</tr>			
	</tbody>
</table><br />

<?php echo $this->Form->Close(T('Save')); ?>

