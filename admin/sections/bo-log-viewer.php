<?
	$jsFramework->jqueryTableSorter();
?>
<script type="text/javascript">
$(document).ready(
	function(){         
	 	$("#myTable")
			.tablesorter()
			.tablesorterPager({container: $("#pager")});
	} 
); 
</script>

<? print Logger::HtmlTable('tablesorter', 'myTable'); ?>

<div id="pager" class="pager">
	<form>
		<img src="<?php echo DIR_WS_JAVASCRIPT?>jquery/tableSorter/blue/first.png" class="first" alt="first"/>
		<img src="<?php echo DIR_WS_JAVASCRIPT?>jquery/tableSorter/blue/prev.png" class="prev" alt="previous"/>
		<input type="text" class="pagedisplay"/>
		<img src="<?php echo DIR_WS_JAVASCRIPT?>jquery/tableSorter/blue/next.png" class="next" alt="next"/>
		<img src="<?php echo DIR_WS_JAVASCRIPT?>jquery/tableSorter/blue/last.png" class="last" alt="last"/>
		<select class="pagesize">
			<option selected="selected"  value="10">10</option>
			<option value="20">20</option>
			<option value="30">30</option>
			<option  value="40">40</option>
		</select>
	</form>
</div>
