<table class="wide calendar">
<tr class="month_label">
	<th class="month_ctrl prev_month"><?= $prev_month_link ?></th><th colspan="5"><?= $month_label ?></th><th class="month_ctrl next_month"><?= $next_month_link ?></th>
</tr>
<tr>
	<th><?= substr(ucfirst(_('monday')), 0, 3)?></th>
	<th><?= substr(ucfirst(_('tuesday')), 0, 3)?></th>
	<th><?= substr(ucfirst(_('wednesday')), 0, 3)?></th>
	<th><?= substr(ucfirst(_('thursday')), 0, 3)?></th>
	<th><?= substr(ucfirst(_('friday')), 0, 3)?></th>
	<th><?= substr(ucfirst(_('saturday')), 0, 3)?></th>
	<th><?= substr(ucfirst(_('sunday')), 0, 3)?></th>
</tr>
<tr>
<?php
$i = 0;
$first_key = key($days);
for($c = 0; $c < $first_key; $c++) {
	echo "<td></td>";
	$i++;
}
foreach($days as $k=>$d_cell) {
	if($i%7 == 0 && $i != 0) {
		echo "</tr><tr>";
	}
	echo $d_cell;
	$i++;
}
for($c = 0; $c < 7 - $i%7 && $i%7 != 0; $c++) {
	echo "<td></td>";
}
?>
</tr>
</table>
