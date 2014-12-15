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
    'saveOriginal'       => false,
    'editor'              => false,
    'isImage'            => true,
    'extensionFile'      => array( 'png', 'jpg' ),
    'CImageHandlerParams' => array(
        'flip' => 2,
    ),
));

$this->endContent();
?>