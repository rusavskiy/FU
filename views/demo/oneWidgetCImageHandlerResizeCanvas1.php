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
        'resizeCanvas' => array(
            array( 300, 300, array( 255, 255, 0 ), '' ),
            array( 200, 200, array( 255, 255, 255 ), 'test' ),
            array( 100, 100, array( 255, 0, 255 ), 'name_thumb' ),
        ),
    ),
));

$this->endContent();
?>