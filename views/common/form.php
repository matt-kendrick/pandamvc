<h4><?php echo $formDescription; ?></h4>
<div style="padding-top:25px;">
	<form action="<?php echo $action; ?>" method="POST">
		<input type='hidden' name='<?php echo $keyField ?>' value='<?php echo $o->{$keyField}; ?>'>
		<?php
			foreach(get_object_vars($o) AS $var => $varValue)
			{
				if($var != $keyField)
				{
					echo "
					<div class=\"form-group\">
						<label for=\"".$var."\">".ucfirst($var)."</label>
						<input type=\"textbox\" class=\"form-control\" id=\"$var\" name=\"$var\" placeholder=\"".ucfirst($var)."\" value=\"$varValue\">
					</div>
					";
				}
			}
		?>
		
		<input class='btn btn-primary' type='submit' value='Submit'>
	</form>
</div>