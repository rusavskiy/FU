<?php
$this->beginContent('//demo/layouts');
/**/echo"<pre class='prt'>POST> "; print_r($_POST); echo"</pre>"; /**/

$this->widget('ext.FileUploader.UploadOneFileWidget', array (
    'debug'               => true,
    #'model'         => $model,
    #'attribute'     => 'image',
    'name'     => 'recordName',
    'value'    => (isset($_POST['recordName'])) ? $_POST['recordName'] : '',
    'dir'                 => 'images/image',
    #'model'         => $model,
    #'attribute'     => 'image',
    'name'     => 'recordName',
    'value'    => (isset($_POST['recordName'])) ? $_POST['recordName'] : '',
    'maxWH'             => array ( 800, 800 ),
    'saveOriginal'       => true,
    'isImage'            => true,
    'extensionFile'      => array ( 'png', 'jpg' ),
    'CImageHandlerParams' => array (
        'thumb' => array (
            array ( 400, 400, '', true ),
            array ( 100, 100, 'thumb_', true ),
            array ( 50, 50, 'thumb2_', true ),
        ),
    ),
));

$this->endContent();
?>