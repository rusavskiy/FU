<?php
/**
 * UploaderWidget abstract class file.
 * @author Rusavskiy Vitaliy <rusavskiy@gmail.com>
 * @version 1.0.0
 */
Yii::import('ext.' . basename(pathinfo(dirname(__FILE__), PATHINFO_DIRNAME)) . '.components.IframeBehavior');
Yii::import('ext.' . basename(pathinfo(dirname(__FILE__), PATHINFO_DIRNAME)) . '.ServerLogicController');
Yii::import('ext.' . basename(pathinfo(dirname(__FILE__), PATHINFO_DIRNAME)) . '.EditorFileUploaderController');

abstract class UploaderWidget extends CWidget
{

	/**
	 * отлаживать
	 */
	public $debug = false;

	/**
	 * Максимальний розмір файлу
	 */
	public $max_size_upload_file = 0;

	/**
	 * Встановлення назв пакетів які повинні бути підключені разом з загружчиком.
	 * Використовується для уникнення дублювання підключень однакових скриптів.
	 * По замовчуванню розраховується на те, що в проекті вже увімкнено 'jquery','bootstrap'.
	 * @var type array( 'jquery','bootstrap','ui','css-js')
	 */
	public $packagesRegisterClientScript = array( 'css-js' );

	/**
	 * Дирикторія завантаження
	 */
	public $dir = 'images';

	/**
	 * Створення хеш директорій перед назвою файлом. ( md5 )
	 * @var type
	 */
	public $createHashPath = false;

	/**
	 * (bool) Вказуємо що файл являється зображенням
	 */
	public $isImage = true;

	/**
	 * (bool) Використовувать fancybox для переляду зображеня (як для одного зображення так і для галереї)
	 */
	public $fancyboxGallery = true;

	/**
	 * (bool) Використовувать редактор для зображень, якщо isImage = true
	 */
	public $editor = true;

	/**
	 * (bool) Зберегти оригінал файлу (для зображень)
	 */
	public $saveOriginal = true;

	/**
	 * (bool) Залишать оригінальні імена файлів
	 */
	public $originalNameFile = false;

	/**
	 * (bool) Транслітеруемо ім*я файлу при збераженні
	 */
	public $translitNameFile = false;

	/**
	 * (bool) Перетворення ім*я для збереж. в кирилиці
	 */
	public $iconvNameFile = false;

	/**
	 * Типи файлів, які можна завантажувати
	 */
	public $extensionFile = array();

	/**
	 * Максимальна ширина і висота для зображ. array( W , H )
	 */
	public $maxWH = array();

	/**
	 * Використовується для розширення ImageHandler
	 */
	public $CImageHandlerParams = array( //компонент для обработки изображений
		// Создание превюшек thumb($toWidth, $toHeight, $proportional = true);
		'thumb'			 => array( /* array( 0, 0, 'name_thumb', $path = '', true ), */),
		// Ресайз картинок resize($toWidth, $toHeight, $proportional = true);
		'resize'		 => array( /* array( 0, 0, 'name_thumb', $path = '', true ), */),
		// Превюшка с подгоном размера и обрезкой лишнего adaptiveThumb($width, $height);
		'adaptiveThumb'	 => array( /* array( 0, 0, 'name_thumb', $path = '' ), */),
		// Превюшка с заливкой бекграунда ($toWidth, $toHeight, $backgroundColor));
		'resizeCanvas'	 => array( /* array( 0, 0, 'name_thumb', $path = '', array( 255, 255, 255 ) ), */),
	);


	/**
	 * ВЛАСТИВОСТІ ПЕРЕЛІЧЕНІ НИЖЧЕ НЕ ВСТАНОВЛЮВАТИ ПРИ ВИЗОВІ ВІДЖЕТА
	 */

	/**
	 * Назва файла
	 */
	public $nameFile = '';

	/**
	 * Назва поля file
	 */
	public $inputNameFile = 'file';

	/**
	 * хеш директорії
	 * @var type
	 */
	public $hashDir = '';

	/**
	 * Ідфифікатоор html коду
	 */
	public $htmlID = '';

	/**
	 * Флаг вибору сценарія обробки файла в контролері (переопреділяється)
	 */
	protected $ajaxFlagUpload = '';

	/**
	 * Список створених файлів(потрібний щоб при збереж. не видалялися файли)
	 */
	protected $listNameFilesStore	 = array();
	protected $acceptImage			 = '';
	protected $IDButtonEditor		 = '';

	/**
	 * Дані, що передаються через аякс (ОСНОВНІ ДАНІ ДЛЯ РОБОТИ)
	 */
	protected $dataClient = '';

	/**
	 * Повний фізчний шлях до кореня сайта
	 */
	protected $webroot = '';

	public function init()
	{
		FileUploaderController::registerClientScript($this->packagesRegisterClientScript);
		$this->initialization();
	}

	public function run()
	{

	}

// Ініціалізація властивостей по default
	private function initialization()
	{
		$this->webroot = Yii::getPathOfAlias('webroot');

		/* Залежності */
		{
			if (!$this->isImage)
			{
				$this->editor				 = false;
				$this->fancyboxGallery		 = false;
				$this->CImageHandlerParams	 = array();
			}

			if (!$this->editor)
				$this->saveOriginal = false;
		}

		if (empty($_SESSION))
		{
			@session_start();
		}
#/**/echo"<pre class='prt'>> ";print_r($_SESSION);echo"</pre>";/**/die;
#/**/echo"<pre class='casper'>> "; print_r(get_headers($_SERVER['HTTP_ORIGIN'], 1)); echo"</pre>"; /**/die;

		ini_set('mbstring.internal_encoding', 'UTF-8');
		iconv_set_encoding('output_encoding', 'UTF-8');  // Конечная кодировка
		if (!headers_sent())
			header('Content-Type: text/html; charset=UTF8');

		if (!$this->htmlID)
			$this->htmlID = mt_rand();

		/**
		 * Створення хеш директорій
		 */
		if ($this->createHashPath)
		{
			/* Перевіряємо чи є файл і якщо є визначаємо його хеш код */
			if (preg_match('%^(?P<hesh>.+)\/%ius', $this->nameFile, $res))
			{
				#varDumperCasper::dump($res['hesh'], 10, true);
				$this->hashDir = $res['hesh'];
			}
			else
			{
				$this->hashDir = preg_replace('%^(.{4})(.{4})(.{4})(.{4})(.{16})%ius', '$1/$2/$3/$4', md5(mt_rand() + time()));
			}
		}



		if (!$this->max_size_upload_file)
			$this->max_size_upload_file = $this->return_bytes(ini_get('post_max_size'));
		else
		{
			$max_size_upload_file = $this->return_bytes(ini_get('post_max_size'));
			if ($max_size_upload_file < $this->max_size_upload_file)
			{
				$this->max_size_upload_file = $max_size_upload_file;
			}
		}



		if (!isset($this->maxWH[0]) || !$this->maxWH[0])
			$this->maxWH = array( 2000, 2000 );

		$this->setAcceptFile();

		/* уборка зворотніх слешов */
		$this->dir = preg_replace('{\\\+}isxu', '/', $this->dir);

#/** @DUMPER */ varDumperCasper::dump($this, array( 'but' )); /**/
		$this->dataClient = base64_encode(json_encode($this));
	}

	private function setAcceptFile()
	{
		if (is_array($this->extensionFile) && $this->extensionFile)
		{
			$aaccept_type = array(
				'application/postscript'													 => array( 'ai', 'ps' ),
				'application/mime'															 => array( 'aps' ),
				'application/x-navi-animation'												 => array( 'ani' ),
				'application/octet-stream'													 => array( 'arc', 'arj',
					'bin', 'com', 'exe', 'lha', 'lhx', 'lzh', 'lzx',
					'psd' ),
				'application/arj'															 => array( 'arj' ),
				'application/x-mplayer2'													 => array( 'asx' ),
				'application/book'															 => array( 'boo', 'book' ),
				'application/java'															 => array( 'class' ),
				'application/clariscad'														 => array( 'ccad' ),
				'application/drafting'														 => array( 'drw' ),
				'application/msword'														 => array( 'doc', 'docx',
					'dot' ),
				'application/acad'															 => array( 'dwg' ),
				'application/x-gzip'														 => array( 'gzip' ),
				'application/inf'															 => array( 'inf' ),
				'application/pdf'															 => array( 'pdf' ),
				'application/mspowerpoint'													 => array( 'pot', 'pps',
					'ppt', 'ppz' ),
				'application/rtf'															 => array( 'rtf', 'rtx' ),
				'application/excel'															 => array( 'xla', 'xlb',
					'xlc', 'xld', 'xlk', 'xll', 'xlm', 'xls', 'xlt',
					'xlv', 'xlw' ),
				'application/x-compressed'													 => array( 'z', 'zip' ),
				'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'			 => array( 'xlsx' ),
				'application/vnd.openxmlformats-officedocument.spreadsheetml.template'		 => array( 'xltx' ),
				'application/vnd.openxmlformats-officedocument.presentationml.template'		 => array( 'potx' ),
				'application/vnd.openxmlformats-officedocument.presentationml.slideshow'	 => array( 'ppsx' ),
				'application/vnd.openxmlformats-officedocument.presentationml.presentation'	 => array( 'pptx' ),
				'application/vnd.openxmlformats-officedocument.presentationml.slide'		 => array( 'sldx' ),
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document'	 => array( 'docx' ),
				'application/vnd.openxmlformats-officedocument.wordprocessingml.template'	 => array( 'xlam' ),
				'application/vnd.ms-excel.sheet.binary.macroEnabled.12'						 => array( 'xlsb' ),
				'application/vnd.ms-excel.addin.macroEnabled.12'							 => array( 'dotx' ),
				'audio/aiff'																 => array( 'aif', 'aiff' ),
				'audio/x-aiff'																 => array( 'aif', 'aiff' ),
				'audio/basic'																 => array( 'au' ),
				'audio/midi'																 => array( 'mid', 'midi' ),
				'audio/mod'																	 => array( 'mod' ),
				'audio/mpeg'																 => array( 'mp2', 'mpa',
					'mpg', 'mpga' ),
				'audio/wav'																	 => array( 'wav' ),
				'text/x-asm'																 => array( 'asm' ),
				'text/asp'																	 => array( 'asp' ),
				'text/x-c'																	 => array( 'c', 'cpp' ),
				'text/plain'																 => array( 'c++', 'com',
					'conf', 'def', 'h', 'jav', 'java', 'list',
					'lst',
					'pl', 'text', 'txt' ),
				'text/css'																	 => array( 'css' ),
				'text/html'																	 => array( 'htm', 'html',
					'htmls', 'shtml' ),
				'text/xml'																	 => array( 'xml' ),
				'image/x.djvu djvu djv'														 => array( 'djvu' ),
				'image/png'																	 => array( 'png' ),
				'image/x-jg'																 => array( 'art' ),
				'image/bmp'																	 => array( 'bm', 'bmp' ),
				'image/gif'																	 => array( 'gif' ),
				'image/x-icon'																 => array( 'ico' ),
				'image/jpeg'																 => array( 'jfif', 'jpe',
					'jpeg', 'jpg', 'jfif-tbnl' ),
				'image/pict'																 => array( 'pic', 'picf' ),
				'image/tiff'																 => array( 'tif', 'tiff' ),
				'video/x-ms-asf'															 => array( 'asf', 'asx',
					'asx' ),
				'video/avi'																	 => array( 'avi' ),
				'video/quicktime'															 => array( 'mov', 'qt' ),
				'video/mp4'																	 => array( 'mp4' ),
				'video/ogg'																	 => array( 'ogg' ),
				'video/x-flv'																 => array( 'flv' ),
				'windows/metafile'															 => array( 'wmf' ),
			);

#/**/echo"<pre class='prt'>> "; print_r($this->extensionFile); echo"</pre>"; /**/
			$accept = '';
			foreach($aaccept_type as $k => $v)
			{
				if (array_intersect($v, $this->extensionFile))
				{
					$accept.=(
						($accept) ? ',' : ''
						) . $k;
				}
			}
			$this->acceptImage = $accept;
		}
		elseif (isset($this->isImage) && $this->isImage)
		{
			$this->acceptImage = 'image/*';
		}
#/**/echo"<pre class='prt'>> "; print_r($this->acceptImage); echo"</pre>"; /**/
	}

	/**
	 *  Перетворення максимального розміру завантаження в байти
	 */
	private function return_bytes($val)
	{
		$val	 = trim($val);
		$last	 = strtolower($val[strlen($val) - 1]);
		switch($last)
		{
// The 'G' modifier is available since PHP 5.1.0
			case 'g':
			case 'gb':
				$val *= 1024;
			case 'm':
			case 'mb':
				$val *= 1024;
			case 'k':
			case 'kb':
				$val *= 1024;
		}

		return (int) $val;
	}

	abstract protected function ClientScript();
	abstract protected function HtmlOptions();
}