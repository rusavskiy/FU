<?php
/**
 * UploadPhotoGalleryWidget class file.
 * @author Rusavskiy Vitaliy <rusavskiy@gmail.com>
 * @version 1.1.0
 */
Yii::import('ext.' . basename(dirname(__FILE__)) . '.components.UploaderWidget');

class UploadPhotoGalleryWidget extends UploaderWidget
{

	public $model		 = ''; // Модель
	public $attribute	 = ''; // Атрибут
	public $name		 = ''; // Назва поля
	public $value		 = ''; // Значення
	public $htmlOptions	 = array();
	public $textCleanBox = 'Перетащите файлы мышкой в эту область или воспользуйтесь кнопкой ниже';
	public $textFileBox	 = '<b>Скачать / Просмотреть</b>';

	/**
	 * Параметри відображення елементів
	 */
	public $showElements	 = array(
		'name'	 => true, // Назва файлу
		'weight' => true, // Вага зображення на сервері
		'size'	 => true, // Показувать розміри завантаженого зображення
	);
	#
	public $editorOptions	 = array( //
		'watermark_img'	 => 'pic/watermark.png', // PNG!!!
		'htmlOptions'	 => array(),
	);

	/**
	 * ВЛАСТИВОСТІ ПЕРЕЛІЧЕНІ НИЖЧЕ НЕ ВСТАНОВЛЮВАТИ ПРИ ВИЗОВІ ВІДЖЕТА
	 */
	public $src				 = array(); // html Адрес файла або адреси якщо використовується фанксібокс для замбів
	public $filesize		 = '';
	public $getimagesize	 = '';
	public $recordName		 = ''; // Назва поля, яке використовуватиметься для запису в БД
	public $ajaxFlagUpload = ServerLogicController::AJAX_FLAG_UPLOAD_ONE_FILE; // флаг вибору сценарія обробки файла в контролері

	/**
	 * Флаг існування файлу на сервері
	 */
	protected $issetFileOnServer = false;

	public function init()
	{
		$this->setNameAndVulue();
		parent::init();
		FileUploaderController::registerClientScript(array( 'fancybox', 'photo_gallery' ));

		$this->checkFileOnServer();
		$this->constructionPathsFiles();
		$this->setSRC();

		// Встановлюєм назву поля (якщо не задано), яке використовується для запису в БД
		if (!$this->recordName)
		{
			$this->recordName = 'record_' . $this->htmlID;
		}
		$this->HtmlOptions();
		$this->ClientScript();
		$this->IframeEditorProcessing();
	}

	public function run()
	{
		if ($this->debug)
			echo CHtml::link('demo list', Yii::app()->createUrl('/fileuploadereditor/demo'));


		parent::run();
		$this->render('photoGallery/uploadPhotoGalleryWidget',
			array(
			'recordName'		 => $this->recordName,
			'nameFile'			 => $this->nameFile,
			'src'				 => $this->src,
			'textCleanBox'		 => $this->textCleanBox,
			'textFileBox'		 => $this->textFileBox,
			'filesize'			 => $this->filesize,
			'getimagesize'		 => $this->getimagesize,
			'isImage'			 => $this->isImage,
			'fancyboxGallery'	 => $this->fancyboxGallery,
			'html'				 => $this->htmlOptions,
			'listNameFilesStore' => ($this->listNameFilesStore) ? json_encode($this->listNameFilesStore) : '',
			'action_form'		 => Yii::app()->createUrl(ServerLogicController::NAME_CONTROLLER_URL),
		));
	}

	/**
	 * Установка назви поля і значення
	 */
	private function setNameAndVulue()
	{
		if (empty($this->name))
		{
			if (empty($this->model) || empty($this->attribute))
				throw new CHttpException(500, 'FileUploader "name" have to be set!');
			else
			{
				$this->recordName = get_class($this->model) . "[" . $this->attribute . "]";

				#if (!isset($this->model->{$this->attribute}))
				#throw new CHttpException(500, 'FileUploader Не определено свойство ' . $this->attribute);

				$this->nameFile = $this->model->{$this->attribute};
			}
		}
		else
		{
			$this->recordName	 = $this->name;
			$this->nameFile		 = $this->value;
		}
	}

	/**
	 * Перевірка існування файлу на севері.
	 * Визначення розміру файла який вже завантажений на сервер
	 */
	private function checkFileOnServer()
	{
		if ($this->nameFile && $this->dir)
		{
			$file = Yii::getPathOfAlias('webroot') . '/' . $this->dir . '/' . $this->nameFile;


			if ($this->originalNameFile && $this->iconvNameFile)
			{
				$file = FileUploaderController::iconvFileName($file);
			}

			if (is_file($file))
			{
				$this->issetFileOnServer = true;

				$this->filesize	 = intval(filesize($file) / 1024);
				$this->filesize	 = ($this->filesize) ? 'Размер: ' . $this->filesize . ' Кб' : '';

				if ($this->isImage && $this->showElements['weight'] && @getimagesize($file))
				{
					$res				 = getimagesize($file);
					$this->getimagesize	 = $res[3];
				}
				else
				{
					$this->getimagesize = '';
				}
			}
		}
	}

	/**
	 * Побудова адресу для файла.
	 * Перевіряється чи є замби для зображень і відбудовується адрес.
	 */
	private function constructionPathsFiles()
	{
		if ($this->issetFileOnServer)
		{
			$this->listNameFilesStore[] = $this->dir . '/' . $this->nameFile;
			if (!empty($this->CImageHandlerParams) && !empty($this->isImage))
			{
				#varDumperCasper::dump($this->CImageHandlerParams, array( 'but' ), '');
				// Визначаєм чи є замби і їх назви
				foreach($this->CImageHandlerParams as $k => $v)
				{
					switch($k)
					{
						case 'thumb':
						case 'resize':
						case 'adaptiveThumb':
							foreach($v as $thumb)
							{
								if (!empty($thumb[2]))
								{
									$name						 = $thumb[2] . $this->nameFile;
									if (is_file(Yii::getPathOfAlias('webroot') . '/' . $this->dir . '/' . $name))
										$this->listNameFilesStore[]	 = $this->dir . '/' . $name;
								}
							}
							break;
						case 'resizeCanvas':
							foreach($v as $thumb)
							{
								if (isset($thumb[3]) && $thumb[3])
								{
									$name						 = $thumb[3] . $this->nameFile;
									if (is_file(Yii::getPathOfAlias('webroot') . '/' . $this->dir . '/' . $name))
										$this->listNameFilesStore[]	 = $this->dir . '/' . $name;
								}
							}
							break;
					}
					# Якщо вказано що має бути оригінал зображення - визначаємо назву цього файлу
					if ($this->saveOriginal)
					{
						$this->listNameFilesStore[ImageProcessingBehavior::PREFIX_ORIGINAL_IMAGE] = $this->dir . '/' . ImageProcessingBehavior::PREFIX_ORIGINAL_IMAGE . $this->nameFile;
					}
				}
				#CVarDumper::dump($this->listNameFilesStore, 10, true);
			}
		}
	}

	/**
	 * Побудова адресу для відображеня зображення.
	 * Якщо використовується fancybox перевіряється чи є замби для відображення
	 */
	private function setSRC()
	{
		#CVarDumper::dump($this->listNameFilesStore, 10, true);
		if ($this->listNameFilesStore)
			foreach($this->listNameFilesStore as $k => $v)
			{
				if ($k === ImageProcessingBehavior::PREFIX_ORIGINAL_IMAGE)
					continue;
				$this->src[] = Yii::app()->baseUrl . '/' . $v;
			}
		#CVarDumper::dump($this->src, 10, true);
	}

	/**
	 *
	 */
	protected function HtmlOptions()
	{
		$htmlOptions = array(
			'div_general_blok'		 => array(
				'class' => 'G_general_blok',
			),
			'div_dropBox'			 => array(
				'id'	 => 'G_dropBox_' . $this->htmlID,
				'class'	 => 'G_dropBox',
			),
			################# IMG/LINK FILE  ##################
			'div_container'			 => array(
				'id'	 => 'G_container_' . $this->htmlID,
				'class'	 => 'G_container',
			),
			'div_img_container'		 => array( #19.12.13
				'id'	 => 'G_img_container_' . $this->htmlID,
				'class'	 => 'G_img_container',
			),
			'img'					 => array( #22.12.13
				'id'	 => 'G_file_' . $this->htmlID,
				'class'	 => "G_img-thumbnail",
			),
			'div_fancybox_elements'	 => array(
				'id' => 'G_fancybox_elements_' . $this->htmlID,
			),
			'a_fancybox_img'		 => array(
				'id'					 => 'G_fancybox_img_' . $this->htmlID,
				'class'					 => 'G_fancybox_img',
				'data-fancybox-group'	 => 'G_fancybox_' . $this->htmlID,
			),
			'a_fancybox_img_thumb'	 => array(
				'class'					 => 'G_fancybox_img',
				'data-fancybox-group'	 => 'G_fancybox_' . $this->htmlID,
			),
			'div_textCleanBox'		 => array( #19.12.13
				'id'	 => 'G_textCleanBox_' . $this->htmlID,
				'class'	 => 'G_textCleanBox',
				'style'	 => ($this->nameFile && $this->issetFileOnServer) ? "display: none;" : '',
			),
			'div_link'				 => array( #19.12.13
				'id'	 => 'G_div_link_' . $this->htmlID,
				'class'	 => 'G_div_link',
				'style'	 => (!$this->nameFile || !$this->issetFileOnServer) ? "display: none;" : '',
			),
			'link'					 => array(
				'id'	 => 'G_linkFile_' . $this->htmlID,
				'target' => '_blank',
			),
			#############  INPUT/TEXT HIDDEND  ################
			'listNameFilesStore'	 => array( #19.12.13!!!
				'id'	 => 'G_listNameImageStore_' . $this->htmlID,
				'class'	 => 'G_listNameFilesStore',
			),
			'inputRecordName'		 => array( #19.12.13
				'id'	 => 'G_inputRecordName_' . $this->htmlID,
				'class'	 => 'G_inputRecordName',
			),
			################   INPUT FILE  ##################
			'input_file'			 => array( #19.12.13
				'id'	 => 'G_file-field_' . $this->htmlID,
				'class'	 => 'G_file',
				'accept' => $this->acceptImage,
			),
			###################   FORM   ######################
			'form'					 => array(
				'enctype'	 => 'multipart/form-data',
				'name'		 => 'name_form_' . $this->htmlID,
			),
			###################  BUTTONS  #####################
			'div_block_buttons'		 => array(
				'class' => 'G_block_buttons',
			),
			'button_upload'			 => array( #19.12.13
				'class' => 'G_button_upload',
			),
			'button_upload_input'	 => array( #19.12.13
				'id'	 => 'G_upload_' . $this->htmlID,
				'class'	 => 'G_btn btn-primary btn-xs',
				'value'	 => 'Загрузить',
			),
			'button_edit'			 => array( #19.12.13
				'id'	 => 'G_button_edit_' . $this->htmlID,
				'class'	 => 'G_button_edit',
				'style'	 => ($this->editor
				&& $this->issetFileOnServer) ? '' : 'display:none',
			),
			'button_edit_input'		 => array( #09.01.14
				'id'	 => 'G_edit_' . $this->htmlID,
				'class'	 => 'G_btn btn-primary btn-xs',
				'value'	 => 'Редактировать',
			),
			'button_delete'			 => array( #19.12.13
				'id'	 => 'G_button_delete_' . $this->htmlID,
				'class'	 => 'G_button_delete',
				'style'	 => ($this->issetFileOnServer) ? '' : 'display:none',
			),
			'button_delete_input'	 => array( #19.12.13
				'id'	 => 'G_delete_' . $this->htmlID,
				'class'	 => 'G_btn btn-primary btn-xs',
				'value'	 => 'Удалить',
			),
			################## PROGRESS BAR #####################
			'div_blok_progress'		 => array( #19.12.13
				'id'	 => 'G_blok_progress_' . $this->htmlID,
				'class'	 => 'G_blok_progress',
			),
			'div_progress'			 => array( #19.12.13
				'class' => 'G_progress',
			),
			'div_progress_bar'		 => array( #19.12.13
				'id'	 => 'G_progress_bar_' . $this->htmlID,
				'class'	 => 'G_progress-bar progress-bar-success',
			),
			'div_loading'			 => array( #19.12.13
				'id'	 => 'G_loading_' . $this->htmlID,
				'class'	 => 'G_loading',
			),
			'span_sr_only'			 => array( #19.12.13
				'class' => 'G_sr-only',
			),
			################## INFO DATA FILE ####################
			'div_data_file'			 => array(
				//'id'    => 'size_file_' . $this->htmlID,
				'class'	 => 'G_data_file',
				'style'	 => (
				($this->showElements['size']
				|| $this->showElements['weight']
				|| $this->showElements['name'])
				&& $this->issetFileOnServer
				) ? '' : 'display:none',
			),
			'div_name_file'			 => array( #19.12.13
				'id'	 => 'G_name_file_' . $this->htmlID,
				'class'	 => 'G_name_file',
				'style'	 => ($this->showElements['name'] && $this->issetFileOnServer) ? '' : 'display:none',
			),
			'div_size_file'			 => array( #19.12.13
				'id'	 => 'G_size_file_' . $this->htmlID,
				'class'	 => 'G_size_file',
				'style'	 => ($this->showElements['size']) ? '' : 'display:none',
			),
			'div_getimagesize_file'	 => array( #19.12.13
				'id'	 => 'G_getimagesize_file_' . $this->htmlID,
				'class'	 => 'G_weight_file',
				'style'	 => ($this->showElements['weight']) ? '' : 'display:none',
			),
		);
		foreach($htmlOptions as $k => $v)
		{
			if (array_key_exists($k, $this->htmlOptions))
			{
				$res[$k] = array_merge($v, $this->htmlOptions[$k]);
			}
			else
			{
				$res[$k] = $v;
			}
		}
		$this->htmlOptions = $res;
	}

	/**
	 * Підключення фрейму для редактора
	 */
	private function IframeEditorProcessing()
	{
		if ($this->editor)
		{
			/* Підключення "Поведение" */
			$this->attachBehavior('IframeBehavior',
				array(
				'class'				 => 'IframeBehavior',
				'htmlID'			 => $this->htmlID,
				'idActionOpenIframe' => $this->htmlOptions['button_edit_input']['id'],
				'src'				 => Yii::app()->createAbsoluteUrl(EditorFileUploaderController::NAME_CONTROLLER_URL . '/index'),
				'scrolling'			 => "yes",
				'dataPost'			 => array( 'dataClient' => $this->dataClient, /* 'listNameFilesStore'=>$this->listNameFilesStore */ ),
				'htmlDataPost'		 => array( 'listNameFilesStore' => $this->htmlOptions['listNameFilesStore']['id'] ),
				)
			);
			$this->initIframeBehavior();
			$this->IDButtonEditor = $this->getIDButtonEditor();
		}
	}

	protected function ClientScript()
	{
		$this->clientScriptUploader();
		$this->setClientScriptFancyboxImg();
	}

	/**
	 * Подключаем и настраиваем плагин загрузки
	 * Отображение выбраных файлов, создание миниатюр и ручное добавление в очередь загрузки.
	 * Проверка поддержки File API, FormData и FileReader
	 */
	private function clientScriptUploader()
	{
		Yii::app()->clientScript->registerScript('uploadOneFile_FileUploader_' . $this->htmlID,
			"CSPR.UploaderFile(
				{
					dataClient: '$this->dataClient',
					max_size:	'$this->max_size_upload_file',
					imgSize:	0,
					urlDeleteFile:   '" . Yii::app()->createUrl(ServerLogicController::NAME_CONTROLLER_URL . '/deleteFile') . "',
					upload: {
						url:			'" . Yii::app()->createUrl(ServerLogicController::NAME_CONTROLLER_URL) . "',
						fieldName:		'$this->inputNameFile'
					},
					flag:{
						debug:		'$this->debug',
						isImage:	'$this->isImage',
						'editor':	'$this->editor',
						fancybox:	'$this->fancyboxGallery',
					},
					objJQury: {
						/** Контейнер, куда можно помещать файлы методом drag and drop */
						dropBox:			'#{$this->htmlOptions['div_dropBox']['id']}',
						/** Стандарный input для файлов */
						fileInput:			'#{$this->htmlOptions['input_file']['id']}',
						recordName:			'#{$this->htmlOptions['inputRecordName']['id']}',
						/** Ссилка на файл */
						linkFile:			'#{$this->htmlOptions['link']['id']}',
						/** Встановлюється JavaScript-ом
						* Список створених файлов (при одноразовому завантаженні файлу)
						* використовуються для фільтрації під час видалення завантаженого мусора */
						id_listNameFilesStore: '#{$this->htmlOptions['listNameFilesStore']['id']}',
						/** Виведення назви файла */
						nameFile:			'#{$this->htmlOptions['div_name_file']['id']}',
						textBox:			'#{$this->htmlOptions['div_textCleanBox']['id']}',
						buttonUpload:		'#{$this->htmlOptions['button_upload_input']['id']}',
						buttonEdit:			'#{$this->htmlOptions['button_edit']['id']}',
						buttonDelete:		'#{$this->htmlOptions['button_delete']['id']}',
						blokProgress:		'#{$this->htmlOptions['div_blok_progress']['id']}',
						progressBar:		'#{$this->htmlOptions['div_progress_bar']['id']}',
						loading:			'#{$this->htmlOptions['div_loading']['id']}',
						infoSize:			'#{$this->htmlOptions['div_size_file']['id']}',
						getimagesizeFile:	'#{$this->htmlOptions['div_getimagesize_file']['id']}',
						/** Поле для виведення зображення */
						fileImg:			'#{$this->htmlOptions['img']['id']}',
						fancybox_img:		'#{$this->htmlOptions['a_fancybox_img']['id']}',
						fancybox_elements:	'#{$this->htmlOptions['div_fancybox_elements']['id']}',
					}
				}
			).UploadOneFile();
		");

		Yii::app()->clientScript->registerScript('uploadOneFile_InterceptionSubmitGarbageRemoval',
			"CSPR.UploaderFile(
				{
					debug:		'0',
					urlDeleteTempFiles: '" . Yii::app()->createUrl(ServerLogicController::NAME_CONTROLLER_URL . '/deleteTempFiles') . "',
					urlDeleteFile:   '" . Yii::app()->createUrl(ServerLogicController::NAME_CONTROLLER_URL . '/deleteFile') . "',
					objJQury: {
						listNameFilesStore: '.{$this->htmlOptions['listNameFilesStore']['class']}',
					}
				}
			).InterceptionSubmitGarbageRemoval();
		");
	}

	/**
	 * Скрипт для роботи Fancybox
	 */
	private function setClientScriptFancyboxImg()
	{
		$script = "$(document).ready(function() {
						$('.{$this->htmlOptions['a_fancybox_img']['class']}').fancybox({
							    openEffect  : 'none',
								closeEffect : 'none',
								prevEffect : 'none',
								nextEffect : 'none',

								minWidth: 20,
								minHeight: 20,

								helpers : {
									title : {
										type : 'float'
									},
								},
								afterLoad : function() {
									if(this.group.length > 1){
										this.title = (this.index + 1)+' из '+this.group.length+(this.title ?' - '+this.title:'');
									}else{
										this.title = (this.title ?this.title:'');
									}
								}
						});
				  });";
		Yii::app()->clientScript->registerScript('FancyboxImg', $script);
	}

}