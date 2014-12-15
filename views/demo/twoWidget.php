<?php
$this->beginContent('//demo/layouts');
/**/echo"<pre class='prt'>> "; print_r($_POST); echo"</pre>"; /**/
?>

<div style="margin-bottom: 500px;">
	<div style="float: left"><?php
		$this->widget('ext.FileUploader.UploadOneFileWidget',
			array(
			'debug'					 => true,
			#'model'         => $model,
			#'attribute'     => 'image',
			'name'					 => 'recordName',
			'value'					 => (isset($_POST['recordName'])) ? $_POST['recordName'] : '',
			'dir'					 => 'images/image',
			'maxWH'				 => array( 800, 800 ),
			'saveOriginal'			 => true,
			'isImage'				 => true,
			'htmlOptions'			 => array(
				'div_general_blok'	 => array(
					'style' => 'width: 300px;',
				),
				'div_dropBox'		 => array(
					'style' => 'width: 300px; height: 200px;',
				),
			),
			'showElements'			 => array(
				'name'				 => true, // Назва файлу
				'weight'			 => true, // Вага зображення на сервері
				'size'				 => true, // Показувать розміри завантаженого зображення
				'button_download'	 => true, // Кнопка загрузки
				'button_edit'		 => true, // Кнопка редагування
				'button_delete'		 => true, // Кнопка видалення
			),
			'CImageHandlerParams'	 => array(
				'thumb' => array(
					array( 400, 400, '', true ),
					array( 100, 100, 'thumb_', true ),
					array( 50, 50, 'thumb2_', true ),
				),
			),
		));
		?>
	</div>


	<div style="float: left; margin: 20px;"><?php
		$this->widget('ext.FileUploader.UploadOneFileWidget',
			array(
			'debug'					 => false,
			#'model'         => $model,
			#'attribute'     => 'image',
			'name'					 => 'recordName2',
			'value'					 => (isset($_POST['recordName2'])) ? $_POST['recordName2'] : '',
			'dir'					 => 'images/image',
			'maxWH'				 => array( 1000, 1000 ),
			'saveOriginal'			 => false,
			'isImage'				 => false,
			'htmlOptions'			 => array(
				'div_general_blok'	 => array(
					'style' => 'width: 300px;',
				),
				'div_dropBox'		 => array(
					'style' => 'height: 200px;',
				),
			),
			'showElements'			 => array(
				'name'				 => true, // Назва файлу
				'weight'			 => true, // Вага зображення на сервері
				'size'				 => true, // Показувать розміри завантаженого зображення
				'button_download'	 => true, // Кнопка загрузки
				'button_edit'		 => true, // Кнопка редагування
				'button_delete'		 => true, // Кнопка видалення
			),
			'CImageHandlerParams'	 => array(
				'thumb' => array(
					array( 800, 800, '', true ),
					array( 500, 500, 'thumb_', true ),
					array( 150, 150, 'thumb2_', true ),
				),
			),
		));
		?>
	</div>
</div>

<?php $this->endContent(); ?>