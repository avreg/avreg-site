<script type="application/javascript">
	function br_spec_out() {
		if (GECKO)
			document.write('Одинарный клик мышью - камеру на весь экран. &nbsp;F11 - полноэкранный режим.');
		else if (MSIE)
			document.write('Мышь: одинарный клик левой - камеру на весь экран, клик правой - контекст. меню. &nbsp;F11 - полноэкранный режим.');
		else
			document.write('Необходимо использовать браузеры: MS Internet Explorer или Firefox.');
	}
</script>

<div id="toolbar" style="width:100%; margin:0; padding:0;">
	<table width="100%" cellspacing="0" border="0" cellpadding="0">
		<tr id="head" style="height: 35px; overflow: hidden;">
			<td width="150px">
				<a href="<? echo $conf['prefix']; ?>/online/index.php" title='Назад, к выбору камер.'>
					<!--<img src="<?echo $conf['prefix']; ?>/img/dvrlogo-134x25.png" width="134" height="25" border="0">
			--></a>
			</td>
			<td width="90%" style="color: #E0E0E0; text-align: right; height: 25px; overflow: hidden;">
				<script type="text/javascript">
					//br_spec_out();
				</script>
				&nbsp;&nbsp;Нет изображений с камер?
			</td>
			<td style="color: #E0E0E0; text-align: right;" nowrap="true">
				&nbsp;Читаем&nbsp;
				<a title="HELP" class="jqModal" href="#" style="cursor: pointer; color:#FFA500; font-weight:bold;">справку!</a>&nbsp;
			</td>
		</tr>
	</table>

</div>

<div class="jqmWindow" id="dialog">
	<div style="text-align: right;">
		<span class="jqmClose" style="text-align: center; border: 1px solid #000; font-weight: bold; padding: 5px;"><a
				href="#">X</a></span>
	</div>
	<hr>
	<p>Если вы <b>не видите изображения от видеокамер</b>, то возможно:</p>
	<ul>
		<li>проблемы видеозахвата с этих(ой) камер(ы);</li>
		<li>вам не разрешено смотреть эту(и) камеру(ы);</li>
		<li>другие пользователи сейчас смотрят камеры и сработало ограничение по количеству одновременных просмотров;
		</li>
		<li>проблемы в настройках интернет-браузера:
			<ul>
				<li>Firefox: в настройках браузера отключена опция &quot;загружать изображения&quot;.</li>
				<li>Internet Explorer: настройки браузера не позволяют загружать и выполнять компоненты ActiveX.
					Cпросите у вашего системного администратора или у нас.;
				</li>
			</ul>
		<li>настройки сетевого экрана firewall на вашем компьютере блокируют запросы к камерам;</li>
		<li><i>возможно просто нужно перезапустить браузер или обновить страницу;</i></li>
		<li>ещё какая-нибудь причина которую мы пока не знаем :-)</li>
	</ul>
	<?php
	if (!empty($conf['admin-name']) || !empty($conf['admin-mail']) || !empty($conf['admin-tel'])) {
		echo 'Администратор системы: ' . $conf['admin-name'] . ' &lt;' . $conf['admin-mail'] . '&gt; (' . $conf['admin-tel'] . ')';
	}

	?>
	<hr>
	<div style="text-align: right;"><a href="#" class="jqmClose">Закрыть</a></div>
</div>
