<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
	</head>

	<body>

		<?php
#/**/echo"<pre class='prt'>POST> "; print_r($_POST); echo"</pre>"; /**/
		echo CHtml::form('', "POST", array( 'style' => 'margin: 30px 30px 0px' ));
		if (isset($_GET['view']))
		{
			echo CHtml::link('Назад', Yii::app()->controller->createAbsoluteUrl('demo'));
			echo CHtml::submitButton('Условно сохранить статью',
				array( 'style' => 'padding: 6px;
color: red;
border-radius: 12px;
background: lightgoldenrodyellow;
margin: 10px' ));
		}



		echo $content;


		if (isset($_GET['view']))
		{
			echo CHtml::endForm();

			echo CHtml::tag('br');
			echo CHtml::tag('br');

			$contents	 = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . $_GET['view'] . '.php');
			$contents	 = preg_replace(array( '{\$this\-\>beginContent\(\'//demo/layouts\'\);}', '{\$this\-\>endContent\(\);}' ),
				'', $contents);
			highlight_string($contents);
		}
		?>
	</body>
</html>