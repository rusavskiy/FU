<?php
$this->beginContent('//demo/layouts');

$this->widget('ext.FileUploader.UploadOneFileWidget',
        array(
    #'model'         => $model,
    #'attribute'     => 'image',
    'name'     => 'recordName',
    'value'    => (isset($_POST['recordName'])) ? $_POST['recordName'] : '',
    'dir'      => 'images/image',
    'maxWH'  => array( 800, 800 ),
    'isImage' => true,
    'editor'   => false,
));

$this->endContent();
?>