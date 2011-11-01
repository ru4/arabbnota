<?php if (!defined('APPLICATION')) die();
?>

<h1><?php echo $this->Data('Title');?></h1>

<?php 
echo $this->Form->Open();
echo $this->Form->Errors();
?>

<ul>
<li>
<?php
	echo Wrap('1) Example: Name = Plugins.QuickConfigSave.Test', 'p');	
	echo $this->Form->Label('@Name', 'Name'); // array('style' => 'float:none; width:auto')
	echo $this->Form->TextBox('Name', array('MultiLine' => False, 'placeholder' => 'Something.Namespace.Name'));
?>
</li>

<li>
<?php
	echo $this->Form->Label('Value', 'Value');
	echo $this->Form->TextBox('Value', array('MultiLine' => False));
?>
</li>

<li>
<?php
	$Options = array('string', 'boolean', 'integer', 'float', 'array', 'null');
	$Options = array_combine($Options, array_map('ucfirst', $Options));
	echo $this->Form->Label('Type', 'Type');
	echo $this->Form->DropDown('Type', $Options);
?>
</li>

<li>

<?php
	echo Wrap(T('Options:'), 'strong');
	echo $this->Form->CheckBox('RemoveEmpty', 'Remove Empty');
?>
</li>

<li>
<?php
	echo Wrap('2) You can paste here part of configuration file.', 'p');
	echo $this->Form->TextBox('Configuration', array('MultiLine' => True));
?>
</li>

</ul>

<?php
echo $this->Form->Button('Save');
echo $this->Form->Close();
?>