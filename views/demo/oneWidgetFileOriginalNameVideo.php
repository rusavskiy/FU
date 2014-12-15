<?php
$this->beginContent('//demo/layouts');

$this->widget('ext.FileUploader.UploadOneFileWidget', array (
    'debug'               => true,
    #'model'         => $model,
    #'attribute'     => 'image',
    'name'     => 'recordName',
    'value'    => (isset($_POST['recordName'])) ? $_POST['recordName'] : '',
    'dir'                  => 'images/image',
    'isImage'             => false,
    'extensionFile'       => array ( 'avi', 'mp4', 'flv', 'mov' ),
    'max_size_upload_file' => '500000000',
    'originalNameFile'   => true,
    'iconvNameFile'      => true,
));

$this->endContent();
?>