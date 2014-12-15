<?php
$this->beginContent('//demo/layouts');
/**/echo"<pre class='prt'>POST> "; print_r($_POST); echo"</pre>"; /**/

$this->widget('ext.FileUploader.UploadOneFileWidget',
		array(
		'debug'					 => true,
		#'model'         => $model,
		#'attribute'     => 'image',
		'name'					 => 'recordName',
		'value'					 => (isset($_POST['recordName'])) ? $_POST['recordName'] : '',
		'dir'						 => 'images/image',
		'maxWH'				 => array( 300, 300 ),
		'saveOriginal'	 => true,
		'isImage'			 => true,

		'htmlOptions'		 => array(
				'div_general_blok' => array(
						'style' => 'width: 300px; height: 350px;',
				),
				'div_dropBox' => array(
					  'style' => 'width: 300px; min-height: 200px;',
				),
		),
));

$this->endContent();
?>