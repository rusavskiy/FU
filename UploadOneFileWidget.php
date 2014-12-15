<?php
/**
 * UploadOneFileWidget class file.
 * @author Rusavskiy Vitaliy <rusavskiy@gmail.com>
 * @version 1.1.0
 */
Yii::import('ext.' . basename(dirname(__FILE__)) . '.components.UploaderWidget');

class UploadOneFileWidget extends UploaderWidget
{

	public $model     = ''; // Модель
	public $attribute = ''; // Атрибут
	public $name      = ''; // Назва поля
	public $value     = ''; // Значення
	/**
	 * Використання системи тимчасових файлів для ведення історії завантаження і автоматичне
	 * зачищення мусора(файлів) з файлової системи (ВИМИКАТИ В УНІКАЛЬНИХ ВИПАДКАХ)
	 */
	public $usingTemporaryFilesHistory = true;
	public $htmlOptions                = array();
	public $textCleanBox               = 'Перетащите файлы мышкой в эту область или воспользуйтесь кнопкой ниже';
	public $textFileBox                = '<b>Скачать / Просмотреть</b>';

	/**
	 * Параметри відображення елементів
	 */
	public $showElements = array(
		'name'   => true, // Назва файлу
		'weight' => true, // Вага зображення на сервері
		'size'   => true, // Показувать розміри завантаженого зображення
	);
	#
	public $editorOptions = array( //
	                               'watermark_img' => 'pic/watermark.png', // PNG!!!
	);

	/**
	 * ВЛАСТИВОСТІ ПЕРЕЛІЧЕНІ НИЖЧЕ НЕ ВСТАНОВЛЮВАТИ ПРИ ВИЗОВІ ВІДЖЕТА
	 */
	public $src            = array(); // html Адрес файла або адреси якщо використовується фанксібокс для замбів
	public $filesize       = '';
	public $getimagesize   = '';
	public $recordName     = ''; // Назва поля, яке використовуватиметься для запису в БД
	public $ajaxFlagUpload = ServerLogicController::AJAX_FLAG_UPLOAD_ONE_FILE; // флаг вибору сценарія обробки файла в контролері

	/**
	 * Флаг існування файлу на сервері
	 */
	protected $issetFileOnServer = false;

	public function init()
	{
		$this->setNameAndVulue();
		parent::init();
		FileUploaderController::registerClientScript(array('fancybox', 'upload'));

		$this->checkFileOnServer();
		$this->constructionPathsFiles();
		$this->setSRC();

		// Встановлюєм назву поля (якщо не задано), яке використовується для запису в БД
		if (!$this->recordName) {
			$this->recordName = 'record_' . $this->htmlID;
		}
		$this->HtmlOptions();
		$this->ClientScript();
		$this->IframeEditorProcessing();
	}

	public function run()
	{
		if ($this->debug) {
			echo CHtml::link('demo list', Yii::app()->createUrl('/fileuploadereditor/demo'));
		}


		parent::run();
		$this->render('uploadFileWidget',
			array(
				'recordName'         => $this->recordName,
				'nameFile'           => $this->nameFile,
				'src'                => $this->src,
				'textCleanBox'       => $this->textCleanBox,
				'textFileBox'        => $this->textFileBox,
				'filesize'           => $this->filesize,
				'getimagesize'       => $this->getimagesize,
				'isImage'            => $this->isImage,
				'fancyboxGallery'    => $this->fancyboxGallery,
				'html'               => $this->htmlOptions,
				'listNameFilesStore' => ($this->listNameFilesStore) ? json_encode($this->listNameFilesStore) : '',
				'action_form'        => Yii::app()->createUrl(ServerLogicController::NAME_CONTROLLER_URL),
			)
		);
	}

	/**
	 * Використання оболочки  name і value для встановлення назви поля і значення з UploaderWidget
	 */
	private function setNameAndVulue()
	{
		if (empty($this->name)) {
			if (empty($this->model) || empty($this->attribute)) {
				throw new CHttpException(500, 'FileUploader "name" have to be set!');
			}
			else {
				$this->recordName = get_class($this->model) . "[" . $this->attribute . "]";

				#if (!isset($this->model->{$this->attribute}))
				#throw new CHttpException(500, 'FileUploader Не определено свойство ' . $this->attribute);

				$this->nameFile = $this->model->{$this->attribute};
			}
		}
		else {
			$this->recordName = $this->name;
			$this->nameFile   = $this->value;
		}
	}

	/**
	 * Перевірка існування файлу на севері.
	 * Визначення розміру файла який вже завантажений на сервер
	 */
	private function checkFileOnServer()
	{
		if ($this->nameFile && $this->dir) {
			$file = Yii::getPathOfAlias('webroot') . '/' . $this->dir . '/' . $this->nameFile;


			if ($this->originalNameFile && $this->iconvNameFile) {
				$file = FileUploaderController::iconvFileName($file);
			}

			if (is_file($file)) {
				$this->issetFileOnServer = true;

				$this->filesize = intval(filesize($file) / 1024);
				$this->filesize = ($this->filesize) ? 'Размер: ' . $this->filesize . ' Кб' : '';

				if ($this->isImage && $this->showElements['weight'] && @getimagesize($file)) {
					$res                = getimagesize($file);
					$this->getimagesize = $res[3];
				}
				else {
					$this->getimagesize = '';
				}
			}
		}
	}

	/**
	 * Побудова адресу для файлів. Перевіряється чи є замби для зображень і відбудовується адрес.
	 */
	private function constructionPathsFiles()
	{
		if ($this->issetFileOnServer) {
			$original                   = ImageProcessingBehavior::PREFIX_ORIGINAL_IMAGE;
			$this->listNameFilesStore[] = $this->dir . '/' . $this->nameFile;

			# Якщо вказано що має бути оригінал зображення - визначаємо назву цього файлу
			if ($this->saveOriginal) {
				/* Визначення назви оригінального зображення */
				/* якщо використовується хеш код шляху - перевизначаєм назву */
				if ($this->hashDir) {
					$name = preg_replace('%\/(?=[^/]+$)%iu', '/' . $original, $this->nameFile);
				}
				else {
					$name = $original . $this->nameFile;
				}

				$this->listNameFilesStore[$original] = $this->dir . '/' . $name;
			}

			if (!empty($this->CImageHandlerParams) && !empty($this->isImage)) {
				// Визначаєм чи є замби і їх назви
				foreach ($this->CImageHandlerParams as $parameters) {
					if (is_array($parameters)) {
						foreach ($parameters as $thumb) {
							if (!empty($thumb[2])) {
								/* Перевірка чи в замбів є власний шлях завантаження */
								$pathThumbImg = (!empty($thumb[3]) && is_string($thumb[3])) ? $thumb[3] : $this->dir;

								/* Визначення назви файлу замба */
								{
									$name = $thumb[2] . $this->nameFile;
									/* Якщо використовується хеш код перевизначаєм назву */
									if ($this->hashDir) {
										$name = preg_replace('%\/(?=[^/]+$)%iu', '/' . $thumb[2], $this->nameFile);
									}
								}

								if (is_file($this->webroot . '/' . $pathThumbImg . '/' . $name)) {
									$this->listNameFilesStore[] = $pathThumbImg . '/' . $name;
								}
							}
						}
					}
				}
			}
			#CVarDumper::dump($this->listNameFilesStore, 10, true);
		}
	}

	/**
	 * Побудова адресу для відображеня зображення.
	 * Якщо використовується fancybox перевіряється чи є замби для відображення
	 */
	private function setSRC()
	{
		#CVarDumper::dump($this->listNameFilesStore, 10, true);
		if ($this->listNameFilesStore) {
			foreach ($this->listNameFilesStore as $k => $v) {
				if ($k === ImageProcessingBehavior::PREFIX_ORIGINAL_IMAGE) {
					continue;
				}
				$this->src[] = Yii::app()->baseUrl . '/' . $v;
			}
		}
		#CVarDumper::dump($this->src, 10, true);
	}

	/**
	 *
	 */
	protected function HtmlOptions()
	{
		$htmlOptions = array(
			'div_general_blok'      => array( #19.12.13
			                                  'class' => 'U_general_blok',
			),
			'div_dropBox'           => array( #19.12.13
			                                  'id'    => 'dropBox_' . $this->htmlID,
			                                  'class' => 'U_dropBox',
			),
			################# IMG/LINK FILE  ##################
			'div_container'         => array(
				'id'    => 'container_' . $this->htmlID,
				'class' => 'U_container',
			),
			'div_img_container'     => array( #19.12.13
			                                  'id'    => 'img_container_' . $this->htmlID,
			                                  'class' => 'U_img_container',
			),
			'img'                   => array( #22.12.13
			                                  'id'    => 'file_' . $this->htmlID,
			                                  'class' => "img-thumbnail",
			),
			'div_fancybox_elements' => array(
				'id' => 'fancybox_elements_' . $this->htmlID,
			),
			'a_fancybox_img'        => array(
				'id'                  => 'fancybox_img_' . $this->htmlID,
				'class'               => 'U_fancybox_img',
				'data-fancybox-group' => 'fancybox_' . $this->htmlID,
			),
			'a_fancybox_img_thumb'  => array(
				'class'               => 'U_fancybox_img',
				'data-fancybox-group' => 'fancybox_' . $this->htmlID,
			),
			'div_link'              => array( #19.12.13
			                                  'id'    => 'U_div_link_' . $this->htmlID,
			                                  'class' => 'U_div_link',
			                                  'style' => (!$this->nameFile || !$this->issetFileOnServer) ? "display: none;" : '',
			),
			'link'                  => array(
				'id'     => 'linkFile_' . $this->htmlID,
				'target' => '_blank',
			),
			'div_textCleanBox'      => array( #19.12.13
			                                  'id'    => 'textCleanBox_' . $this->htmlID,
			                                  'class' => 'U_textCleanBox',
			                                  'style' => ($this->nameFile && $this->issetFileOnServer) ? "display: none;" : '',
			),
			#############  INPUT/TEXT HIDDEND  ################
			'listNameFilesStore'    => array( #19.12.13!!!
			                                  'id'    => 'listNameImageStore_' . $this->htmlID,
			                                  'class' => 'listNameFilesStore',
			),
			'inputRecordName'       => array( #19.12.13
			                                  'id'    => 'inputRecordName_' . $this->htmlID,
			                                  'class' => 'inputRecordName',
			),
			################   INPUT FILE  ##################
			'input_file'            => array( #19.12.13
			                                  'id'     => 'file-field_' . $this->htmlID,
			                                  'class'  => 'U_file',
			                                  'accept' => $this->acceptImage,
			),
			###################   FORM   ######################
			'form'                  => array(
				'enctype' => 'multipart/form-data',
				'name'    => 'name_form_' . $this->htmlID,
			),
			###################  BUTTONS  #####################
			'div_block_buttons'     => array(
				'class' => 'U_block_buttons',
			),
			'button_upload'         => array( #19.12.13
			                                  'class' => 'U_button_upload',
			),
			'button_upload_input'   => array( #19.12.13
			                                  'id'    => 'upload_' . $this->htmlID,
			                                  'class' => 'btn btn-primary btn-xs',
			                                  'value' => 'Загрузить',
			),
			'button_edit'           => array( #19.12.13
			                                  'id'    => 'button_edit_' . $this->htmlID,
			                                  'class' => 'U_button_edit',
			                                  'style' => ($this->editor
				                                  && $this->issetFileOnServer) ? '' : 'display:none',
			),
			'button_edit_input'     => array( #09.01.14
			                                  'id'    => 'edit_' . $this->htmlID,
			                                  'class' => 'btn btn-primary btn-xs',
			                                  'value' => 'Редактировать',
			),
			'button_delete'         => array( #19.12.13
			                                  'id'    => 'button_delete_' . $this->htmlID,
			                                  'class' => 'U_button_delete',
			                                  'style' => ($this->issetFileOnServer) ? '' : 'display:none',
			),
			'button_delete_input'   => array( #19.12.13
			                                  'id'    => 'delete_' . $this->htmlID,
			                                  'class' => 'btn btn-primary btn-xs',
			                                  'value' => 'Удалить',
			),
			################## PROGRESS BAR #####################
			'div_blok_progress'     => array( #19.12.13
			                                  'id'    => 'blok_progress_' . $this->htmlID,
			                                  'class' => 'U_blok_progress',
			),
			'div_progress'          => array( #19.12.13
			                                  'class' => 'progress',
			),
			'div_progress_bar'      => array( #19.12.13
			                                  'id'    => 'progress_bar_' . $this->htmlID,
			                                  'class' => 'progress-bar progress-bar-success',
			),
			'div_loading'           => array( #19.12.13
			                                  'id'    => 'loading_' . $this->htmlID,
			                                  'class' => 'U_loading',
			),
			'span_sr_only'          => array( #19.12.13
			                                  'class' => 'sr-only',
			),
			################## INFO DATA FILE ####################
			'div_data_file'         => array(
				//'id'    => 'size_file_' . $this->htmlID,
				'class' => 'U_data_file',
				'style' => (
					($this->showElements['size'] || $this->showElements['weight'] || $this->showElements['name'])
					&& $this->issetFileOnServer
				) ? '' : 'display:none',
			),
			'div_name_file'         => array( #19.12.13
			                                  'id'    => 'name_file_' . $this->htmlID,
			                                  'class' => 'U_name_file',
			                                  'style' => ($this->showElements['name'] && $this->issetFileOnServer) ? '' : 'display:none',
			),
			'div_size_file'         => array( #19.12.13
			                                  'id'    => 'size_file_' . $this->htmlID,
			                                  'class' => 'U_size_file',
			                                  'style' => ($this->showElements['size']) ? '' : 'display:none',
			),
			'div_getimagesize_file' => array( #19.12.13
			                                  'id'    => 'getimagesize_file_' . $this->htmlID,
			                                  'class' => 'U_weight_file',
			                                  'style' => ($this->showElements['weight']) ? '' : 'display:none',
			),
		);
		foreach ($htmlOptions as $k => $v) {
			if (array_key_exists($k, $this->htmlOptions)) {
				$res[$k] = array_merge($v, $this->htmlOptions[$k]);
			}
			else {
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
		if ($this->editor) {
			/* Підключення "Поведение" */
			$this->attachBehavior('IframeBehavior',
				array(
					'class'              => 'IframeBehavior',
					'htmlID'             => $this->htmlID,
					'idActionOpenIframe' => $this->htmlOptions['button_edit_input']['id'],
					'src'                => Yii::app(
					)->createAbsoluteUrl(EditorFileUploaderController::NAME_CONTROLLER_URL . '/index'),
					'scrolling'          => "yes",
					'dataPost'           => array('dataClient' => $this->dataClient, /* 'listNameFilesStore'=>$this->listNameFilesStore */),
					'htmlDataPost'       => array('listNameFilesStore' => $this->htmlOptions['listNameFilesStore']['id']),
				)
			);
			$this->initIframeBehavior();
			$this->IDButtonEditor = $this->getIDButtonEditor();
		}
	}

	protected function ClientScript()
	{
		# публикация директории extensions\FileUploaderEditor\assets
//		$basePath	 = 'ext.' . basename(__DIR__) . '.assets';
//		$assets		 = Yii::app()->assetManager->getPublishedUrl(Yii::getPathOfAlias($basePath));
//		Yii::app()->clientScript->registerScriptFile($assets . '/js/upload-one-file.js');

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
					urlDeleteFile:  '" . Yii::app(
			)->createUrl(ServerLogicController::NAME_CONTROLLER_URL . '/deleteFile') . "',
					upload: {
						url:		'" . Yii::app()->createUrl(ServerLogicController::NAME_CONTROLLER_URL) . "',
						fieldName:	'$this->inputNameFile'
					},
					flag:{
						debug:		'$this->debug',
						isImage:	'$this->isImage',
						'editor':	'$this->editor',
						fancybox:	'$this->fancyboxGallery',
					},
					objJQuery: {
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
		"
		);

		/**
		 * Видалення завантаженого мусора при відправленні форми (за допомогою перехвату форми)
		 * Підключається один раз і опрацьовує всі загружчики
		 * ПІДКЛЮЧЕНІ ДАНІ НЕ ДУБЛЮЮТЬСЯ З ТИМИ ЩО ВИЩЕ, ЦЕ ОКРЕМЕ ПІДКЛЮЧЕННЯ ІЗ ВЛАСНИМИ ПАРАМЕТРАМИ.
		 */
		Yii::app()->clientScript->registerScript('uploadOneFile_InterceptionSubmitGarbageRemoval',
			"CSPR.UploaderFile(
				{
					debug:		'0',
					urlDeleteTempFiles: '" . Yii::app(
			)->createUrl(ServerLogicController::NAME_CONTROLLER_URL . '/deleteTempFiles') . "',
					urlDeleteFile:   '" . Yii::app(
			)->createUrl(ServerLogicController::NAME_CONTROLLER_URL . '/deleteFile') . "',
					objJQuery: {
						listNameFilesStore: '.{$this->htmlOptions['listNameFilesStore']['class']}',
					}
				}
			).InterceptionSubmitGarbageRemoval();
		"
		);
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