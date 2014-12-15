<?php
echo CHtml::openTag('div', $html['div_general_blok']);
{

	echo CHtml::openTag('div', $html['div_dropBox']);
	{
		echo CHtml::openTag('div', $html['div_container']);
		{
			echo CHtml::openTag('div', $html['div_img_container']);
			{
				// progress-bar
				{
					echo CHtml::openTag('div', $html['div_blok_progress']);
					{
						echo CHtml::openTag('div', $html['div_progress']);
						{
							echo CHtml::openTag('div', $html['div_progress_bar']);
							{
								echo CHtml::openTag('span', $html['span_sr_only']); echo CHtml::closeTag('span');
							}echo CHtml::closeTag('div');
							echo CHtml::openTag('div', $html['div_loading']); echo CHtml::closeTag('div');
						}echo CHtml::closeTag('div');
					}echo CHtml::closeTag('div');
				}

				// Якщо файл має бути зображенням
				if ($isImage)
				{
					// Якщо використовується fancybox для перегляду зображень
					if (!empty($fancyboxGallery))
					{
						// Виведення зображення або ссилку на файл, якщо файл завантажено на фтп
						if (!empty($src))
						{
							// Створення списка ссилок для галереї
							$first							 = array_shift($src);
							$html['a_fancybox_img']['title'] = preg_replace('{^.*\/}iu', '', $first);
							echo CHtml::link(CHtml::image($first, $nameFile, $html['img']), $first, $html['a_fancybox_img']);

							echo CHtml::openTag('div', $html['div_fancybox_elements']);
							{
								foreach($src as $k => $v)
								{
									$html['a_fancybox_img_thumb']['title'] = preg_replace('{^.*\/}iu', '', $v);
									echo CHtml::link('', $v, $html['a_fancybox_img_thumb']);
								}
							}
							echo CHtml::closeTag('div');
						}
						else
						{
							echo CHtml::link(CHtml::image('', $nameFile, $html['img']), '', $html['a_fancybox_img']);
							echo CHtml::openTag('div', $html['div_fancybox_elements']); echo CHtml::closeTag('div');
						}
					}
					else
					{
						// Виведення зображення або ссилку на файл, якщо файл завантажено на фтп
						if ($src)
							echo CHtml::image(array_shift($src), $nameFile, $html['img']);
						else
							echo CHtml::image('', $nameFile, $html['img']);
					}
				}
				else
				{
					// Виведення ссилки на файл
					echo CHtml::tag('div', $html['div_link'],
						CHtml::link($textFileBox, ((!empty($src)) ? array_shift($src) : ''), $html['link']));
				}
			}
			echo CHtml::closeTag('div');

			// Виведення текста пустого блока
			echo CHtml::tag('div', $html['div_textCleanBox'], $textCleanBox);
		}
		echo CHtml::closeTag('div');
	}
	echo CHtml::closeTag('div');


	// Ім*я непотрібне, воно підставляється джава-скриптом при завантаженні файла
	echo CHtml::fileField('', '', $html['input_file']);
	// Поле, яке використовуватиметься для запису в БД (змінюється при завантаженні нового файлу)
	echo CHtml::hiddenField($recordName, $nameFile, $html['inputRecordName']);

	/* Список створених файлов (при одноразовому завантаженні файлу)
	 * використовуються для фільтрації під час видалення завантаженого мусора */
	echo CHtml::textArea('', $listNameFilesStore, $html['listNameFilesStore']);


	// Кнопки
	echo CHtml::openTag('div', $html['div_block_buttons']);
	{
		echo CHtml::tag('div', $html['button_upload'], CHtml::button('button', $html['button_upload_input']));
		echo CHtml::tag('div', $html['button_edit'], CHtml::button('button', $html['button_edit_input']));
		echo CHtml::tag('div', $html['button_delete'], CHtml::button('button', $html['button_delete_input']));
	}
	echo CHtml::closeTag('div');


	// Виведення даних файлу
	echo CHtml::openTag('pre', $html['div_data_file']);
	{
		echo CHtml::tag('div', $html['div_name_file'], $nameFile);
		echo CHtml::tag('div', $html['div_size_file'], $filesize);
		echo CHtml::tag('div', $html['div_getimagesize_file'], $getimagesize);
	}
	echo CHtml::closeTag('pre');
	// Виведення даних файлу
}
echo CHtml::closeTag('div');
