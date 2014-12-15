<?php
$this->beginContent('//demo/layouts');

$this->widget('ext.FileUploader.UploadOneFileWidget', array (
    'debug'              => true,
    #'model'         => $model,
    #'attribute'     => 'image',
    'name'     => 'recordName',
    'value'    => (isset($_POST['recordName'])) ? $_POST['recordName'] : '',
    'dir'                => 'images/image',
    'isImage'           => false,
    'extensionFile'     => array ( 'doc', 'docx' ),
    'originalNameFile' => true,
    'translitNameFile' => true,
));

$this->endContent();
?>