<?php
$this->beginContent('//demo/layouts');
/**/echo"<pre class='prt'>> "; print_r($_POST); echo"</pre>"; /**/
?>

<div style="margin-bottom: 500px;">
	<div style="float: left"><?php
		$this->widget('ext.FileUploader.UploadOneFileWidget',
			array(
			'debug'							 => true,
			'packagesRegisterClientScript'	 => array( 'bootstrap', 'ui', 'css-js', 'fancybox', 'upload', 'photo_gallery' ),
			#'model'         => $model,
			#'attribute'     => 'image',
			'name'							 => 'recordName',
			'value'							 => (isset($_POST['recordName'])) ? $_POST['recordName'] : '',
			'usingTemporaryFilesHistory'	 => true, // Використання системи тимчасових файлів для ведення исторії завантаження і автоматичне зачищення мусора(файдів) з файлової системи (ВИМОКАТИ В УНІКАЛЬНИХ ВИПАДКАХ)
			'dir'							 => 'images/image',
			#'createHashPath'				 => true,
			'isImage'						 => true,
			'maxWH'							 => array( 800, 800 ),
			'saveOriginal'					 => true,
			'htmlOptions'					 => array(
				'div_general_blok'	 => array(
					'style' => 'width: 300px;',
				),
				'div_dropBox'		 => array(
					'style' => 'width: 300px; height: 200px;',
				),
			),
			'showElements'					 => array(
				'name'	 => true, // Назва файлу
				'weight' => true, // Вага зображення на сервері
				'size'	 => true, // Розміри завантаженого зображення
			),
			'CImageHandlerParams'			 => array(
				'thumb' => array(
					array( 400, 400, '', true ),
					array( 100, 100, 'thumb_', true ),
					array( 50, 50, 'thumb2_', true ),
				),
			),
		));
		?>
	</div>

	<div style="float: left; margin: 20px 0 0 20px;"><?php
		$this->widget('ext.FileUploader.UploadOneFileWidget',
			array(
			'debug'							 => false,
			'packagesRegisterClientScript'	 => array( 'bootstrap', 'ui', 'css-js', 'fancybox', 'upload', 'photo_gallery' ),
			#'model'         => $model,
			#'attribute'     => 'image',
			'name'							 => 'recordName2',
			'value'							 => (isset($_POST['recordName2'])) ? $_POST['recordName2'] : '',
			'dir'							 => 'images/image2',
			'createHashPath'				 => true,
			'maxWH'							 => array( 800, 800 ),
			'saveOriginal'					 => true,
			'isImage'						 => true,
			'fancyboxGallery'				 => true,
			'htmlOptions'					 => array(
				'div_general_blok'	 => array(
					'style' => 'width: 300px;',
				),
				'div_dropBox'		 => array(
					'style' => 'width: 300px; height: 200px;',
				),
			),
			'showElements'					 => array(
				'name'	 => true, // Назва файлу
				'weight' => true, // Вага зображення на сервері
				'size'	 => true, // Розміри завантаженого зображення
			),
			'CImageHandlerParams'			 => array(
				'thumb'	 => array(
					array( 600, 600, '', true ),
					array( 100, 100, 'thumb_', 'images/thumb', true ),
				),
				'resize' => array(
					array( 50, 50, 'thumb2_', 'images/resize', true ),
				),
			),
		));
		?>
	</div>


	<div style="float: left; margin: 20px;"><?php
		$this->widget('ext.FileUploader.UploadOneFileWidget',
			array(
			'debug'							 => false,
			'packagesRegisterClientScript'	 => array( 'bootstrap', 'ui', 'css-js', 'fancybox', 'upload', 'photo_gallery' ),
			'name'							 => 'recordName3',
			'value'							 => (isset($_POST['recordName3'])) ? $_POST['recordName3'] : '',
			'dir'							 => 'images/image',
			'isImage'						 => false,
			'htmlOptions'					 => array(
				'div_general_blok'	 => array(
					'style' => 'width: 300px;',
				),
				'div_dropBox'		 => array(
					'style' => 'height: 200px;',
				),
			),
		));
		?>
	</div>
</div>

<?php $this->endContent(); ?>