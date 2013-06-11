<td class="<?= $today ? 'today ' : '' ?><?= count($reservation_items) ? 'lessons ' : '' ?><?= $link_view_day ? 'link ' : '' ?>"<?= $link_view_day ? " onclick=\"location.href='".$link_view_day."'\"" : ''?>>
<?= $day_num ?>
<? if($link_insert_day): ?>
	<p><?= $link_insert_day ?></p>
<? endif ?>
<? if(count($reservation_items)): ?>
	<ul>
	<? foreach($reservation_items as $value): ?>
		<li><?= $value ?></li>
	<? endforeach ?>
	</ul>
<? endif ?>
</td>
