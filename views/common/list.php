<?php
	
	//permissions
	if(!isset($canEdit)) $canEdit = false;
	if(!isset($canDelete)) $canDelete = false;
	
	echo "<h4>$this->title</h4>";
?>
<table class="table">
<?php

function cleanColumnName($a)
{
	return implode(" ",array_map("ucfirst",explode("_",$a))); //ex. Log_id -> Log Id
}

$counter = 0;

if(isset($arr))
{
	if(isset($arr[0]) AND !isset($columns))
	{
		$columns = array_keys(get_object_vars($arr[0]));
		$columns = array_combine($columns, $columns);
		$columns = array_map("cleanColumnName",$columns);
	}
	
	foreach($arr AS $o)
	{
		if($counter == 0)
		{
			echo "<thead><tr>";
	
			//tools
			if($canEdit OR $canDelete) echo "<th class=\"colTools\" scope=\"col\"></th>";
			
				foreach($columns AS $key => $value)
				{
					echo "<th scope=\"col\">$value</th>";
				}
			echo "</tr></thead>\r\n";
			echo "<tbody>\r\n";
		}

		echo "<tr>";
		
		//tools
		if($canEdit OR $canDelete)
		{
			echo "<td class=\"colTools\">";
				if($canEdit) echo "<a href='/".str_replace("Controller","",get_class($this))."/update/".$o->{$o->getModelConfig()->keyField}."' title='Update'><span data-feather='edit'></span></a> ";
				if($canDelete) echo "<a href='/".str_replace("Controller","",get_class($this))."/delete/".$o->{$o->getModelConfig()->keyField}."' onclick=\"return confirm('Are you sure?')\" title='Delete'><span data-feather='trash-2'></span></a>";
			echo "</td>";
		}
		
		foreach($columns AS $key => $value)
		{
			echo "<td>".$o->{$key}."</td>";
		}
		echo "</tr>\r\n";
		
		$counter++;
	}
}
?>
</tbody>
</table>
<?php
if(isset($pageNumber))
{
	$prevPageNumber = ($pageNumber-1 < 1 ? 0 : $pageNumber-1);
	$nextPageNumber = ($pageNumber+1 > $totalNumberOfPages ? 0 : $pageNumber+1);

	if(!isset($baseURL)) $baseURL =  "/page/";

	if($totalNumberOfPages > 1)
	{
	?>
	<br>
	<nav>
	  <ul class="pagination justify-content-center">
		<li class="page-item <?php if($prevPageNumber == 0) echo "disabled"; ?>">
		  <a class="page-link" href="$baseURL<?php echo $prevPageNumber ?>" tabindex="-1">Previous</a>
		</li>
		<?php
			for($i=$pageNumber-5;$i<=$pageNumber+4;$i++)
			{
				if($i > 0 AND $i <= $totalNumberOfPages)
				{
					if($i != $pageNumber) echo "<li class=\"page-item\"><a class=\"page-link\" href=\"$baseURL$i\">$i</a></li>\r\n";
					else echo "<li class=\"page-item active\"><a class=\"page-link\" href=\"$baseURL$i\">$i<span class=\"sr-only\">(current)</span></a></li>\r\n";
				}
			}
		?>
		<li class="page-item <?php if($nextPageNumber == 0) echo "disabled"; ?>">
			<a class="page-link" href="$baseURL<?php echo $nextPageNumber ?>" tabindex="-1">Next</a>
		</li>
	  </ul>
	</nav>
	<?php
	}
}
?>