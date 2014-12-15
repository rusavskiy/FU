<div id="body_iframe">
	<!--	<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h4 class="modal-title" id="myModalLabel">Редактор</h4>
		</div>-->
	<div class="modal-body">

		<?php
		echo CHtml::openTag('div', $htmlOptions['div_form_crop']);
		{
			echo CHtml::openTag('div', $htmlOptions['div_button_editor']);
			{
				echo CHtml::openTag('div', $htmlOptions['div_button_group_editor']);
				{
					echo CHtml::button('button', $htmlOptions['button_create']);
					echo CHtml::button('button', $htmlOptions['button_save']);
					echo CHtml::button('button', $htmlOptions['button_save_close']);
				}
				echo CHtml::closeTag('div');
				echo CHtml::openTag('div', $htmlOptions['div_button_group_link_editor']);
				{
					echo CHtml::button('button', $htmlOptions['button_crop']);
					echo CHtml::button('button', $htmlOptions['button_watermark']);
				}
				echo CHtml::closeTag('div');
			}
			echo CHtml::closeTag('div');

			echo CHtml::form($action_form, "POST", $htmlOptions['form_crop']);
			{
				echo CHtml::textArea('dataClient', $dataClient, $htmlOptions['displayNone']);
				echo CHtml::textArea('listNameFilesStore', json_encode($listNameFilesStore), $htmlOptions['displayNone']);
				echo CHtml::textArea('listHistory', base64_encode(json_encode($listHistory)), $htmlOptions['displayNone']);
				echo CHtml::textArea('objFileData', base64_encode(json_encode($objFileData)), $htmlOptions['displayNone']);

				echo CHtml::openTag('div', $htmlOptions['div_inline_labels_crop']);
				{
					echo CHtml::label('X' . CHtml::textField('coordinates[x1]', '', $htmlOptions['x1']), false);
					echo CHtml::label('Y' . CHtml::textField('coordinates[y1]', '', $htmlOptions['y1']), false);
					echo CHtml::label('W' . CHtml::textField('coordinates[w]', '', $htmlOptions['w']), false);
					echo CHtml::label('H' . CHtml::textField('coordinates[h]', '', $htmlOptions['h']), false);

//                        if ($_GET['view'] == 'watermark')
//                        {
//                              echo CHtml::label('A' . CHtml::textField('coordinates[a]', '', $htmlOptions['a']), false);
//                              echo CHtml::label('Opacit&eacute;: ' . '<div id="sliderdiv"></div>', false);
//                        }
				}
				echo CHtml::closeTag('div');
			}
			echo CHtml::endForm();

			echo CHtml::openTag('div', $htmlOptions['div_list_image_crop']);
			{
				// Виведення списка історії
				echo CHtml::openTag('div', $htmlOptions['div_list_history']);
				{
					$class = $htmlOptions['list_button_restore']['class'];
					foreach($listHistory as $v)
					{
						/* Виведення кнопки видалить */
						if ($v['abilityRemove'])
						{
							$htmlOptions['list_button_delete']['name'] = $v['fullName'];
							echo CHtml::htmlButton('<span class="glyphicon glyphicon-remove-circle"></span>',
								$htmlOptions['list_button_delete']);
						}

						/* Виведення елемента */
						{
							$htmlOptions['list_button_restore']['class'] = $class;
							if ($v['fullName'] === $objFileData->nameFile)
								$htmlOptions['list_button_restore']['class'] = preg_replace('{btn btn-default}', 'btn btn-success', $class);

							$htmlOptions['list_button_restore']['name'] = $v['fullName'];
							echo CHtml::htmlButton($v['name'], $htmlOptions['list_button_restore']);
						}
					}
				}
				echo CHtml::closeTag('div');

				echo CHtml::openTag('div', $htmlOptions['wrap_overflow_editorImage_crop']);
				{
					// Виведення зображення
					if (isset($objFileData->src))
					{
						if ($_GET['view'] == 'watermark')
						{
							echo CHtml::image($objFileData->src, '', $htmlOptions['watermarked']);
						}
						else
						{
							echo CHtml::image($objFileData->src, '', $htmlOptions['img']);
						}
					}
				}
				echo CHtml::closeTag('div');
			}
			echo CHtml::closeTag('div');
		}
		echo CHtml::closeTag('div');
		?>
	</div>
</div>