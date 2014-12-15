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
        'adaptiveThumb' => array(
            array( 400, 300, '' ),
            array( 150, 200, 'thumb1_' ),
            array( 80, 80, 'thumb2_' ),
        ),
    ),
));

$this->endContent();
?>