<div style="margin: 30px 50px 0px">
	<?php
	$i			 = 0;
	$array_style = array( 'style' => 'margin-top: 5px;' );


	$i++;
	echo CHtml::openTag('div', $array_style);
	$text	 = $i . ') ЗАВЖДИ АКТУАЛЬНІ ПАРАМЕТРИ, ЗМІНЮЮТЬСЯ ПРИ РЕДАГУВАННІ КОДУ!!!<br> Один віджет зав. ЗОБРАЖЕННЯ, відображення всіх можливих параметрів (встановлені по замовчуванню)';
	$url	 = Yii::app()->controller->createAbsoluteUrl('demo', array( 'view' => 'oneWidgetOllParamsDefault' ));
	echo CHtml::link($text, $url);
	echo CHtml::closeTag('div');


	$i++;
	echo CHtml::openTag('div', $array_style);
	$text	 = $i . ') Один віджет зав. ЗОБРАЖЕННЯ без редактора (мінімальний набір параметрів)';
	$url	 = Yii::app()->controller->createAbsoluteUrl('demo', array( 'view' => 'oneWidgetMinimum' ));
	echo CHtml::link($text, $url);
	echo CHtml::closeTag('div');


	$i++;
	echo CHtml::openTag('div', $array_style);
	$text	 = $i . ') Один віджет зав. зображення з debug, зберігається оригінальне зображення для редактора';
	$url	 = Yii::app()->controller->createAbsoluteUrl('demo', array( 'view' => 'oneWidget' ));
	echo CHtml::link($text, $url);
	echo CHtml::closeTag('div');


	$i++;
	echo CHtml::openTag('div', $array_style);
	$text	 = $i . ') Один віджет зав. зображення з debug, вказується тип зображень, зберігається оригінальне зображення для редактора';
	$url	 = Yii::app()->controller->createAbsoluteUrl('demo', array( 'view' => 'oneWidgetTypeImg' ));
	echo CHtml::link($text, $url);
	echo CHtml::closeTag('div');


	$i++;
	echo CHtml::openTag('div', $array_style);
	$text	 = $i . ') Один віджет зав. зображення з debug / вказується тип зображень / Конвертация в черно-белое изображение';
	$url	 = Yii::app()->controller->createAbsoluteUrl('demo', array( 'view' => 'oneWidgetCImageHandlerGrayscale' ));
	echo CHtml::link($text, $url);
	echo CHtml::closeTag('div');


	$i++;
	echo CHtml::openTag('div', $array_style);
	$text	 = $i . ') Один віджет зав. зображення з debug / вказується тип зображень / Переворачивание HORIZONTAL';
	$url	 = Yii::app()->controller->createAbsoluteUrl('demo', array( 'view' => 'oneWidgetCImageHandlerFlip1' ));
	echo CHtml::link($text, $url);
	echo CHtml::closeTag('div');


	$i++;
	echo CHtml::openTag('div', $array_style);
	$text	 = $i . ') Один віджет зав. зображення з debug / вказується тип зображень / Переворачивание VERTICAL';
	$url	 = Yii::app()->controller->createAbsoluteUrl('demo', array( 'view' => 'oneWidgetCImageHandlerFlip2' ));
	echo CHtml::link($text, $url);
	echo CHtml::closeTag('div');


	$i++;
	echo CHtml::openTag('div', $array_style);
	$text	 = $i . ') Один віджет зав. зображення з debug / вказується тип зображень / Переворачивание BOTH';
	$url	 = Yii::app()->controller->createAbsoluteUrl('demo', array( 'view' => 'oneWidgetCImageHandlerFlip3' ));
	echo CHtml::link($text, $url);
	echo CHtml::closeTag('div');


	$i++;
	echo CHtml::openTag('div', $array_style);
	$text	 = $i . ') Один віджет зав. зображення з debug / вказується тип зображень / Пропорційний ресайз(масштабирование) зображ до вказаних розмірів';
	$url	 = Yii::app()->controller->createAbsoluteUrl('demo', array( 'view' => 'oneWidgetCImageHandlerresize1' ));
	echo CHtml::link($text, $url);
	echo CHtml::closeTag('div');


	$i++;
	echo CHtml::openTag('div', $array_style);
	$text	 = $i . ') Один віджет зав. зображення з debug / вказується тип зображень / Нерропорційний ресайз(масштабирование) зображ до вказаних розмірів';
	$url	 = Yii::app()->controller->createAbsoluteUrl('demo', array( 'view' => 'oneWidgetCImageHandlerresize2' ));
	echo CHtml::link($text, $url);
	echo CHtml::closeTag('div');


	$i++;
	echo CHtml::openTag('div', $array_style);
	$text	 = $i . ') Один віджет зав. зображення з debug / вказується тип зображень / Пропорційне зменшення зображення до 400х400 і створення замбів 100х100 та 50х50';
	$url	 = Yii::app()->controller->createAbsoluteUrl('demo', array( 'view' => 'oneWidgetCImageHandlerThumb1' ));
	echo CHtml::link($text, $url);
	echo CHtml::closeTag('div');


	$i++;
	echo CHtml::openTag('div', $array_style);
	$text	 = $i . ') Один віджет зав. зображення з debug / вказується тип зображень / Непропорційне зменшення зображення до 400х400 і створення замбів 100х100 та 50х50';
	$url	 = Yii::app()->controller->createAbsoluteUrl('demo', array( 'view' => 'oneWidgetCImageHandlerThumb2' ));
	echo CHtml::link($text, $url);
	echo CHtml::closeTag('div');


	$i++;
	echo CHtml::openTag('div', $array_style);
	$text	 = $i . ') Один віджет зав. зображення з debug / вказується тип зображень / Превюшка с подгоном размера и обрезкой лишнего adaptiveThumb($width, $height); 400х300 і створення замбів 150х200 та 80х80';
	$url	 = Yii::app()->controller->createAbsoluteUrl('demo', array( 'view' => 'oneWidgetCImageHandlerAdaptiveThumb1' ));
	echo CHtml::link($text, $url);
	echo CHtml::closeTag('div');


	$i++;
	echo CHtml::openTag('div', $array_style);
	$text	 = $i . ') Один віджет зав. зображення з debug / вказується тип зображень / Превюшка с заливкой бекграунда ($toWidth, $toHeight, $backgroundColor));';
	$url	 = Yii::app()->controller->createAbsoluteUrl('demo', array( 'view' => 'oneWidgetCImageHandlerResizeCanvas1' ));
	echo CHtml::link($text, $url);
	echo CHtml::closeTag('div');


	$i++;
	echo CHtml::openTag('div', $array_style);
	$text	 = $i . ') Один віджет зав. зображення з debug / вказується тип зображень / Все відразу';
	$url	 = Yii::app()->controller->createAbsoluteUrl('demo', array( 'view' => 'oneWidgetCImageHandlerAll' ));
	echo CHtml::link($text, $url);
	echo CHtml::closeTag('div');


	$i++;
	echo CHtml::openTag('div', $array_style);
	$text	 = $i . ') Один віджет зав. ФАЙЛУ з debug, вказується тип файлу, зберігається оригінальне ім*я файлу в ТРАНСЛІТ';
	$url	 = Yii::app()->controller->createAbsoluteUrl('demo', array( 'view' => 'oneWidgetFileOriginalNameTranslit' ));
	echo CHtml::link($text, $url);
	echo CHtml::closeTag('div');


	$i++;
	echo CHtml::openTag('div', $array_style);
	$text	 = $i . ') Один віджет зав. ФАЙЛУ, вказується тип файлу, зберігається оригінальне ім*я файлу враховуючи КИРИЛИЦЮ';
	$url	 = Yii::app()->controller->createAbsoluteUrl('demo', array( 'view' => 'oneWidgetFileOriginalName' ));
	echo CHtml::link($text, $url);
	echo CHtml::closeTag('div');


	$i++;
	echo CHtml::openTag('div', $array_style);
	$text	 = $i . ') Один віджет зав. ВІДЕО, вказується тип файлу, зберігається оригінальне ім*я файлу враховуючи КИРИЛИЦЮ';
	$url	 = Yii::app()->controller->createAbsoluteUrl('demo', array( 'view' => 'oneWidgetFileOriginalNameVideo' ));
	echo CHtml::link($text, $url);
	echo CHtml::closeTag('div');


	$i++;
	echo CHtml::openTag('div', $array_style);
	$text	 = $i . ') Два віджета зав. зображення.';
	$url	 = Yii::app()->controller->createAbsoluteUrl('demo', array( 'view' => 'twoWidget' ));
	echo CHtml::link($text, $url);
	echo CHtml::closeTag('div');


	$i++;
	echo CHtml::openTag('div', $array_style);
	$text	 = $i . ') Три віджета зав. зображення.';
	$url	 = Yii::app()->controller->createAbsoluteUrl('demo', array( 'view' => 'fhreeWidget' ));
	echo CHtml::link($text, $url);
	echo CHtml::closeTag('div');


	$i++;
	echo CHtml::openTag('div', $array_style);
	$text	 = $i . ') bootstrap Modals modal.js.';
	$url	 = Yii::app()->controller->createAbsoluteUrl('demo', array( 'view' => 'bootstrap_modal' ));
	echo CHtml::link($text, $url);
	echo CHtml::closeTag('div');


	$i++;
	echo CHtml::openTag('div', $array_style);
	$text	 = $i . ') Photo Gallery';
	$url	 = Yii::app()->controller->createAbsoluteUrl('demo', array( 'view' => 'photoGallery' ));
	echo CHtml::link($text, $url);
	echo CHtml::closeTag('div');

	//phpinfo();
	?>
</div>