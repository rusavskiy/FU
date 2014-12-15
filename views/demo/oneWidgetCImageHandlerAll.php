<?php
$this->beginContent('//demo/layouts');
/**/echo"<pre class='prt'>POST> "; print_r($_POST); echo"</pre>"; /**/

$this->widget('ext.FileUploader.UploadOneFileWidget',
        array(
    'debug'               => true,
    #'model'         => $model,
    #'attribute'     => 'image',
    'name'                => 'recordName',
    'value'               => (isset($_POST['recordName'])) ? $_POST['recordName'] : '',
    'dir'                 => 'images/image',
    'maxWH'             => array( 800, 800 ),
    'saveOriginal'       => true,
    'isImage'            => true,
    'extensionFile'      => array( 'png', 'jpg' ),
    'CImageHandlerParams' => array(
        // Переворачивание HORIZONTAL=1, VERTICAL=2, BOTH=3
        'flip'          => 3,
        // Конвертация в черно-белое изображение
        'grayscale'     => 1,
        // Создание превюшек thumb($toWidth, $toHeight, $proportional = true);
        'thumb'         => array(
            array( 400, 400, '', true ),
            array( 300, 300, 'name_thumb1', true ),
        ),
        // Ресайз картинок resize($toWidth, $toHeight, $proportional = true);
        'resize'        => array(
            array( 600, 600, 'name_thumb2', true ),
        ),
        // Превюшка с подгоном размера и обрезкой лишнего adaptiveThumb($width, $height);
        'adaptiveThumb' => array(
            array( 60, 60, 'name_thumb3' ),
        ),
        // Превюшка с заливкой бекграунда ($toWidth, $toHeight, $backgroundColor));
        'resizeCanvas'  => array(
            array( 200, 200, array( 0, 255, 255 ), 'name_thumb4' ),
        ),
    ),
));

$this->endContent();
?>