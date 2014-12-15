<?php
/**
 * ServerLogicController class file.
 * Призначений для обробки (збереженя , видалення, заміну) файлів переданих кліентом
 * @author Rusavskiy Vitaliy <rusavskiy@gmail.com>
 * @version 1.0.0
 */
/* Yii::import('ext.' . basename(dirname(__FILE__)) . '.components.FileUploaderController');
  Yii::import('ext.' . basename(dirname(__FILE__)) . '.components.ImageProcessingBehavior');
  Yii::import('ext.' . basename(dirname(__FILE__)) . '.components.OperationTempFileBehavior'); */
Yii::import('ext.' . basename(dirname(__FILE__)) . '.components.*');

class ServerLogicController extends FileUploaderController
{

	const NAME_CONTROLLER_URL = 'fileuploaderserverlogic'; // Назва контроллера яка прописана в MapController
	const AJAX_FLAG_UPLOAD_ONE_FILE = 'one_file';

	#const AJAX_FLAG_DELETE_FILE		 = 'delete_file';

	/**
	 * Містить об*єкт параметрів переданих клієнським віджетом
	 */
	public $objDataClient;

	/**
	 * Містить об*єкт файлу завантаження
	 */
	public $objFile;

	/**
	 * Містить об*єкт з інфою про файл
	 */
	public $pathInfoFileUpload;

	/**
	 * Повний фізчний шлях до папки для завантаження
	 */
	public $fullPathDir = '';

	/**
	 * Повний фізчний шлях до завантаженого файлу
	 */
	public $pathFile = '';

	/**
	 * Флаг, що файл являється зображенням
	 */
	private $fileIsImage = false;

	/**
	 * Список створених файлов (при одноразовому завантаженні файлу)
	 */
	private $listNameFilesStore = array();

	/**
	 * Результат запиту(роботи) аякса
	 * По замовчуванню заповнений масив що повідомляє про відсутність "помилки"
	 */
	private $resultAjax = array(
		'message_debug' => '',
		'message'       => '',
		'code_error'    => 0,
		'status'        => 1,
	);

	/**
	 * СИСТЕМА ОБРОБКИ ПОМИЛОК
	 * Обробка і передача помилок в клієнский скрипт. Визивається в catch (Exception $exc)
	 * Якщо код помилки 200 - значить повідомлення інформативного характеру і не є ознакою помилки
	 */
	private function operationException($exc)
	{
		if ($exc->getCode() !== 200) {
			$this->resultAjax['status'] = 0;
		}

		$this->resultAjax['code_error'] = $exc->getCode();

		if (!empty($this->objDataClient->debug)) {
			$this->resultAjax['message_debug'] = $exc->getMessage();
		}

		$this->resultAjax['message'] = $exc->getMessage();

		Yii::app()->end(json_encode($this->resultAjax));
	}

	/**
	 * ПОДІЯ Обробка файлів прийнятих через аякс
	 */
	public function actionIndex()
	{
		try {
			$this->decodePostDataClient();
			// Обробка для одного файла
			if ($this->objDataClient->ajaxFlagUpload == self::AJAX_FLAG_UPLOAD_ONE_FILE) {
				#/**/echo"<pre class='prt'>> "; print_r($_FILES); echo"</pre>"; /**/
				if (!empty($_FILES) && array_key_exists($this->objDataClient->inputNameFile, $_FILES)) {
					$this->createObjectFileUpload();
					$this->checkFileImage();
					$this->createPathInfoFileUpload();
					$this->checkFileType();
					$this->processFileName();
					$this->checkFolderPath();
					$this->saveFile();
					#$this->operationTempFileProcessing();
					$this->imageProcessing();
					$this->operationTempFileProcessing();
					#/**/echo"<pre class='prt'>> "; print_r($this->listNameFilesStore); echo"</pre>"; /**/
					$this->formationReturnDataAjax();
					Yii::app()->end(json_encode($this->resultAjax));
				}
			}
		} catch (Exception $exc) {
			$this->operationException($exc);
		}
	}

	/**
	 * ПОДІЯ Видалення лишніх файлів при збереженні статті
	 */
	public function actionDeleteTempFiles()
	{
		try {
			#/**/echo"<pre class='prt'>> ";print_r($_POST['list']);echo"</pre>";/**/
			if (!empty($_POST['list']) && is_array($_POST['list'])) {
				$listNotDeleteFiles = array();
				foreach ($_POST['list'] as $v) {
					$listNotDeleteFiles = array_merge($listNotDeleteFiles, json_decode($v, 1));
				}
				#/**/echo"<pre class='prt'>> ";print_r($listNotDeleteFiles);echo"</pre>";/**/die;

				if ($listNotDeleteFiles) {
					// Підключення "Поведение"
					$this->attachBehavior('OperationTempFile',
						array(
							'class'              => 'OperationTempFileBehavior',
							'listNotDeleteFiles' => $listNotDeleteFiles,
						)
					);
					$this->InitOperationTempFile();
				}
			}
			else {
				throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Error!!! actionDeleteTempFiles, No $_POST[\'list\']'
				);
			}
		} catch (Exception $exc) {
			$this->operationException($exc);
		}
		Yii::app()->end();
	}

	/**
	 * ПОДІЯ Видалення файла (Враховується видалення замбів для зображення)
	 */
	public function actionDeleteFile()
	{
		try {
			#echo"<pre class='casper'>> "; print_r($_POST); echo"</pre>"; die;
			if (!empty($_POST['arrayDeleteFile']) && is_array($_POST['arrayDeleteFile'])) {

				$arrayDeleteFiles = array();
				foreach ($_POST['arrayDeleteFile'] as $name_loaded_file) {
					$array = array();
					$array = json_decode($name_loaded_file, 1);

					if (is_array($array)) // Масив з файлами які потрібно видалить
					{
						$arrayDeleteFiles = array_merge($arrayDeleteFiles, $array);
					}
				}

				// Видалення файлів
				foreach ($arrayDeleteFiles as $file) {
					$file = $this->webroot . '/' . FileUploaderController::iconvFileName($file);

					if (is_file($file) && !@unlink($file)) {
						throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Error -> actionDeleteFile<br> Файл ' . $file . ' Не удаляется!'
						);
					}
				}
				throw new Exception('Удалено!', 200);
			}
		} catch (Exception $exc) {
			$this->operationException($exc);
		}
		Yii::app()->end();
	}

	/**
	 * перевіряє на існування і перетворює дані передані клієнським віджетом
	 */
	private function decodePostDataClient()
	{
		if (!empty($_POST['dataClient'])) {
			$this->objDataClient = json_decode(base64_decode($_POST['dataClient']));
			#/**/echo"<pre class='prt'>> "; print_r($this->objDataClient); echo"</pre>"; /**/
		}
		else {
			throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Error->decodePostDataClient()! There are no customer data (dataClient)'
			);
		}
	}

	/**
	 * Створення об*єкту файлу завантаження з $_FILE
	 */
	private function createObjectFileUpload()
	{
		if (!empty($this->objDataClient->inputNameFile)
			&& is_string($this->objDataClient->inputNameFile)
		) {
			// об*єкт завантаження
			$this->objFile = CUploadedFile::getInstanceByName($this->objDataClient->inputNameFile);
			#/**/echo"<pre class='prt'>> "; print_r($this->objFile); echo"</pre>"; /**/
		}
		else {
			throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Error -> createObjectFileUpload()!  No name input file (inputNameFile)!'
			);
		}
	}

	/**
	 * Створення об*єкту з інфою про файл
	 */
	private function createPathInfoFileUpload()
	{
		if (isset($this->objFile->name) && $this->objFile->name) {
			preg_match('{(.+(?=\.\w+$))\.(\w+$)}iux', $this->objFile->name, $res);
			#/**/echo"<pre class='prt'>res> "; print_r($res); echo"</pre>"; /**/

			$this->pathInfoFileUpload = (object)array(
				'basename'   => $this->objFile->name,
				'extension'  => $res[2],
				'filename'   => $res[1],
				'filenameDB' => $res[1],
			);
			#pathinfo($this->objFile->name); // Не робить на серваку vipdesign
			#/**/echo"<pre class='prt'>> "; print_r($this->pathInfoFileUpload); echo"</pre>"; /**/die;
		}
		else {
			throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Error -> createPathInfoFileUpload!  pathinfo. No file name!'
			);
		}
	}

	/**
	 * Перевірка чи файл є зображенням, якщо вказано в параметрах, що це має бути зображення.
	 * Створення флагу, що файл є зображення, який буде використовуватись при кропі і створенні буфера.
	 */
	private function checkFileImage()
	{
		if (
			isset($this->objDataClient->isImage)
			&& $this->objDataClient->isImage
			&& isset($this->objFile->type)
			&& preg_match('{^image/}', trim($this->objFile->type))
		) {
			$this->fileIsImage = true;
		}
		elseif (!empty($this->objDataClient->isImage)) {
			if ($this->objDataClient->debug) {
				throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Error -> checkFileImage!  No image', 404);
			}
			else {
				throw new Exception('Загружать можно только изображения!', 404);
			}
		}
	}

	/**
	 * Перевірка типу файла
	 */
	private function checkFileType()
	{
		if (
			!empty($this->pathInfoFileUpload->extension)
			&& !empty($this->objDataClient->extensionFile)
			&& is_array($this->objDataClient->extensionFile)
		) {
			if (!in_array(strtolower($this->pathInfoFileUpload->extension), $this->objDataClient->extensionFile)) {
				if ($this->objDataClient->debug) {
					throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Выбранный тип файла запрещено загружать!');
				}
				else {
					throw new Exception('Выбранный тип файла запрещено загружать!', 404);
				}
			}
		}
	}

	/**
	 * Обробка назви файлу (транслітерація, кодування, унікальне) в залежності від параметрів
	 */
	private function processFileName()
	{
		if (!empty($this->pathInfoFileUpload) && $this->pathInfoFileUpload->filename !== '') {
			$name   = trim($this->pathInfoFileUpload->filename);
			$nameDB = trim($this->pathInfoFileUpload->filenameDB);

			// якщо оригінальне ім*я
			if (isset($this->objDataClient->originalNameFile) && $this->objDataClient->originalNameFile) {
				// якщо тренслітерація
				if (!empty($this->objDataClient->translitNameFile)) {
					$name   = self::cyrillicToLatin($name);
					$nameDB = $name;
				}
				else // якщо перетворення під кирилицю (окрім зображень!!!) @TODO дописать перевіряючи на сервері UNIX
					if (!empty($this->objDataClient->iconvNameFile) && empty($this->fileIsImage)) {
						#$name = preg_replace('{\s+}i', '-', $name);
						$nameDB = $name;
						#$nameDB = preg_replace('{[-—]+}ui', '', $name);
						$name = FileUploaderController::iconvFileName($nameDB);
					}
					else {
						$name   = preg_replace('{[^\w]+}iu', '-', $name);
						$nameDB = $name;
					}
			}
			else {
				$name   = md5(microtime() + mt_rand());
				$nameDB = $name;
			}

			$this->pathInfoFileUpload->filename   = $name;
			$this->pathInfoFileUpload->filenameDB = $nameDB;
			#/**/echo"<pre class='prt'>> ";print_r($this->pathInfoFileUpload);echo"</pre>";/**/
		}
		else {
			throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Error -> processFileName! no file name');
		}
	}

	/**
	 * Перевіряє на існування вказаний адрес завантаження файлу, і якщо такий відсутній - створює його.
	 */
	private function checkFolderPath()
	{
		if (!empty($this->objDataClient->dir) && is_string($this->objDataClient->dir)) {
			$this->fullPathDir = $this->webroot . '/' . $this->objDataClient->dir;

			/* Додавання хеш директорії якщо поьрібно */
			if ($this->objDataClient->hashDir) {
				$this->fullPathDir .= '/' . $this->objDataClient->hashDir;
			}

			if (!file_exists($this->fullPathDir) || !opendir($this->fullPathDir)) {
				FileUploaderController::recursiveCreateDir($this->fullPathDir);
			}
		}
		else {
			throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Error -> checkFolderPath!  There is no path to the folder (dir)'
			);
		}
	}

	/**
	 * Збереження файла
	 */
	private function saveFile()
	{
		if ($this->objFile && $this->pathInfoFileUpload && $this->fullPathDir) {
			# Створення повного фізчного шляху до завантаженого файлу
			$this->pathFile = $this->fullPathDir . '/' .
				$this->pathInfoFileUpload->filename . '.' .
				$this->pathInfoFileUpload->extension;

			if (!$this->objFile->saveAs($this->pathFile)) {
				throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Error -> saveFile()! Not save file!');
			}

			if (!@chmod($this->pathFile, 0777)) {
				throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Error'
					. '<br>Невозвожно изменить права доступа: ' . $this->pathFile, 406
				);
			}

			# Додавання в список створених файлов для подальшого збереження в темп файл
			{
				$res = $this->objDataClient->dir . '/';
				if ($this->objDataClient->hashDir) {
					$res .= $this->objDataClient->hashDir . '/';
				}
				$res .= $this->pathInfoFileUpload->filenameDB . '.' .
					$this->pathInfoFileUpload->extension;
				$this->listNameFilesStore[] = $res;
			}
		}
		else {
			throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Error -> saveFile()! Not save file!');
		}
	}

	/**
	 * Підключення Behavior для обробка зображення
	 */
	private function imageProcessing()
	{
		if (!empty($this->objDataClient->CImageHandlerParams) && $this->fullPathDir && $this->fileIsImage) {
			if (!extension_loaded('gd')) {
				throw new Exception('Требуеться наличие библиотеки GD.', 404);
			}

			ini_set('memory_limit', '800M');
			ini_set('max_execution_time', '120');

			// Підключення "Поведение"
			$this->attachBehavior('ImageProces',
				array(
					'class'              => 'ImageProcessingBehavior',
					'objDataClient'      => $this->objDataClient,
					'pathFile'           => $this->pathFile,
					'fullPathDir'        => $this->fullPathDir,
					'pathInfoFileUpload' => $this->pathInfoFileUpload,
				)
			);
			$this->initImageProcessing();
			$this->listNameFilesStore = array_unique(array_merge($this->listNameFilesStore,
					$this->returnListNameImageStore()
				)
			);
			#/**/echo"<pre class='prt'>> ";print_r($this->listNameFilesStore);echo"</pre>";/**/
		}
	}

	/**
	 * Запис в тимчасовий файл завантажених файлів і його обробка
	 */
	private function operationTempFileProcessing()
	{
		#/**/echo"<pre class='prt'>" . __CLASS__ . __LINE__ . "> "; print_r($this->listNameFilesStore); echo"</pre>"; /**/
		if (!empty($this->listNameFilesStore)
			&& is_array($this->listNameFilesStore)
			&& $this->objDataClient->usingTemporaryFilesHistory
		) {
			// Підключення "Поведение"
			// написано не через $this->owner з міркувань інкапсуляції
			$this->attachBehavior('OperationTempFile',
				array(
					'class'              => 'OperationTempFileBehavior',
					'listNameFilesStore' => $this->listNameFilesStore,
				)
			);
			$this->InitOperationTempFile();
		}
	}

	/**
	 * Формування даних, які повертаються після обробки запиту аякс.
	 */
	private function formationReturnDataAjax()
	{
		if ($this->listNameFilesStore) {
			#/**/echo"<pre class='prt'>" . __CLASS__ . __LINE__ . "> "; print_r($this->listNameFilesStore); echo"</pre>"; /**/
			$nameFile = $this->pathInfoFileUpload->filenameDB . '.' . $this->pathInfoFileUpload->extension;
			if ($this->objDataClient->hashDir) {
				$nameFile = $this->objDataClient->hashDir . '/' . $nameFile;
			}

			if (@getimagesize($this->pathFile) && $this->objDataClient->isImage) {
				$getimagesize = getimagesize($this->pathFile);
				$getimagesize = $getimagesize[3];
			}
			else {
				$getimagesize = '';
			}

			$this->resultAjax = array(
				'listNameFilesStore' => json_encode($this->listNameFilesStore),
				'baseUrl'            => Yii::app()->baseUrl . '/',
				'nameFile'           => $nameFile,
				'filesize'           => round(filesize($this->pathFile) / 1024, 1),
				'getimagesize'       => $getimagesize,
			);
			#varDumperCasper::dump($this->resultAjax, 10, true); die;
		}
	}

}
/*
* №3
* Запис галереї не збережено в БД. (стаття)
* Загружається файл
* Робим запис в  темп_файл_БД  (дата_ід)
* Загружається файл
* Робим запис в  темп_файл_БД  (дата_ід)
* Загружається файл
* Робим запис в  темп_файл_БД  (дата_ід)
* Обновляєм браузер
   *
   *
* Запис галереї не збережено в БД. (стаття)
* Загружається файл
* Робим запис в  темп_файл_БД  (дата_ід)
* Загружається файл
* Робим запис в  темп_файл_БД  (дата_ід)
* Загружається файл
* Робим запис в  темп_файл_БД  (дата_ід)
* Загружається файл
* Робим запис в  темп_файл_БД  (дата_ід)
   *
* Зберігаєм запис галереї (стаття)
   *
* Вертаєм назви завантажених файлів
* Видяляєм з темп_файл_БД (дата_ід) записи теперішнього запису
* Видаляєм файли записані в темп_файл_БД (дата_ід)
* Видаляєм темп_файл_БД (дата_ід)
* Видаляєм файли записані в темп_файл_БД (дата_ід) який був створеной вчора і вид. сам темп_файл.
*/