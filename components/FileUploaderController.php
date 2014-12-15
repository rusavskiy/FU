<?php

/**
 * FileUploaderController class file.
 * Головний контролер, в якому виконується підключення скриптів і стилів
 * @author Rusavskiy Vitaliy <rusavskiy@gmail.com>
 * @version 1.1.0
 * @date 2014.01.07
 */
abstract class FileUploaderController extends CExtController
{

	// Перезапис min.js файлів в режимі debug
	const REGENERATION_JS_SCRIPT = false;

	/**
	 * Повний фізчний шлях до кореня сайта
	 */
	public $webroot = '';

	//public $layout = '/layouts/body_clear';
	public function init()
	{
		$this->webroot = Yii::getPathOfAlias('webroot');
		$this->googleCompilerCreateMinFile();
	}

	/**
	 * Створення(або перезапис) js файлів за допомогою Closure Compiler Compilation
	 * https://developers.google.com/closure/compiler
	 */
	private function googleCompilerCreateMinFile()
	{
		if (YII_DEBUG && self::REGENERATION_JS_SCRIPT)
		{
			Yii::app()->setComponent('googleCompiler',
				array( 'class' => 'ext.' . basename(pathinfo(dirname(__FILE__), PATHINFO_DIRNAME)) . '.components.googleCompiler' ));
			$adress_folder = Yii::getPathOfAlias('ext.' . basename(pathinfo(dirname(__FILE__), PATHINFO_DIRNAME) . '.assets.js')) . '/';

			$files = array(
				'toastmessage/javascript/jquery.toastmessage.js',
				'jquery.damnUploader.js',
				'uploaderFiles.js',
				'jcrop/jquery.color.js',
				'jcrop/jquery-watermarker.js',
			);

			foreach($files as $v)
				Yii::app()->googleCompiler->compiledFileServer($adress_folder . $v);
		}
	}

	/**
	 * Подключение скриптов и стилей
	 * FileUploaderController::registerClientScript();
	 * $packages = array('bootstrap','ui','css-js','fancybox','upload','editor','photo_gallery')
	 */
	static public function registerClientScript(array $packages)
	{
		# публикация директории extensions\FileUploaderEditor\assets
		$basePath = 'ext.' . basename(pathinfo(dirname(__FILE__), PATHINFO_DIRNAME)) . '.assets';
		Yii::app()->assetManager->publish(Yii::getPathOfAlias($basePath), false, -1, YII_DEBUG);

		/* Пакети */
		{
			/* jquery-2.0.3 */
			Yii::app()->clientScript->packages['jquery'] = array(
				'basePath'	 => $basePath,
				'js'		 => array( 'js/ui/' . (YII_DEBUG ? 'jquery-2.0.3.js' : 'jquery-2.0.3.min.js'), ),
			);

			/* bootstrap */
			Yii::app()->clientScript->packages['bootstrap'] = array(
				'basePath'	 => $basePath,
				'css'		 => array( 'css/' . (YII_DEBUG ? 'bootstrap.css' : 'bootstrap.min.css'), ),
				'js'		 => array( 'js/' . (YII_DEBUG ? 'bootstrap.js' : 'bootstrap.min.js'), ),
				'depends'	 => array( 'jquery' ),
			);

			/* jquery-ui-1.10.3 */
			Yii::app()->clientScript->packages['ui'] = array(
				'basePath'	 => $basePath,
				'css'		 => array( 'js/ui/css/ui-lightness/jquery-ui-1.10.3.custom.css' ),
				'js'		 => array( 'js/ui/' . (YII_DEBUG ? 'jquery-ui-1.10.3.custom.js' : 'jquery-ui-1.10.3.custom.min.js') ),
				'depends'	 => array( 'jquery' ),
			);

			/* Скрипти і стилі для відображення повідомлень toastmessage, а також плагин завантаження */
			Yii::app()->clientScript->packages['css-js'] = array(
				'basePath'	 => $basePath,
				'css'		 => array(
					'css/iframe.css',
					'js/toastmessage/resources/css/jquery.toastmessage.css',
				),
				'js'		 => array(
					#'js/interface.js',
					'js/' . (YII_DEBUG ? 'jquery.damnUploader.js' : 'jquery.damnUploader.js'),
					'js/toastmessage/javascript/' . (YII_DEBUG ? 'jquery.toastmessage.js' : 'jquery.toastmessage.min.js'),
				),
				'depends'	 => array( 'jquery' ),
			);

			/* fancybox для перегляду зображень */
			Yii::app()->clientScript->packages['fancybox'] = array(
				'basePath'	 => $basePath,
				'css'		 => array( 'css/fancybox/jquery.fancybox.css', ),
				'js'		 => array( 'js/fancybox/' . (YII_DEBUG ? 'jquery.fancybox.js' : 'jquery.fancybox.pack.js'), ),
				'depends'	 => array( 'jquery' ),
			);

			/* Скрипт і стилі для завантаження одного файлу */
			Yii::app()->clientScript->packages['upload'] = array(
				'basePath'	 => $basePath,
				'css'		 => array( 'css/upload.css', ),
				'js'		 => array( 'js/' . (YII_DEBUG ? 'uploaderFiles.js' : 'uploaderFiles.js'), ),
				'depends'	 => array( 'jquery' ),
			);

			/* Скрпти редактора */
			Yii::app()->clientScript->packages['editor'] = array(
				'basePath'	 => $basePath,
				'css'		 => array(
					'css/editor.css',
					'css/jcrop/jquery.Jcrop.css',
				),
				'js'		 => array(
					'js/jcrop/' . (YII_DEBUG ? 'jquery.Jcrop.js' : 'jquery.Jcrop.min.js'),
					'js/jcrop/' . (YII_DEBUG ? 'jquery.color.js' : 'jquery.color.min.js'),
					'js/jcrop/' . (YII_DEBUG ? 'jquery-watermarker.js' : 'jquery-watermarker.min.js'),
					'js/' . (YII_DEBUG ? 'editorImg.js' : 'editorImg.min.js'),
				),
				'depends'	 => array( 'jquery', 'bootstrap', 'ui', 'css-js' ),
			);

			/* Скрипти для фотогалереї */
			Yii::app()->clientScript->packages['photo_gallery'] = array(
				'basePath'	 => $basePath,
				'css'		 => array( 'css/photo_gallery.css', ),
				'js'		 => array(),
			);
		}
		# подлючения скриптов и стилей
		{
			foreach($packages as $packag)
			{
				switch($packag)
				{
					case 'ui':
						Yii::app()->clientScript->registerPackage('ui'); break;
					case 'bootstrap':
						Yii::app()->clientScript->registerPackage('bootstrap'); break;
					case 'css-js':
						Yii::app()->clientScript->registerPackage('css-js'); break;
					case 'fancybox':
						Yii::app()->clientScript->registerPackage('fancybox'); break;
					case 'upload':
						Yii::app()->clientScript->registerPackage('upload'); break;
					case 'editor':
						Yii::app()->clientScript->registerPackage('editor'); break;
					case 'photo_gallery':
						Yii::app()->clientScript->registerPackage('photo_gallery'); break;
				}
			}
		}
	}

	/**
	 * Translit text from cyrillic to latin letters.
	 * @static
	 * @param string $text the text being translit.
	 * @return string
	 */
	protected static function cyrillicToLatin($text, $toLowCase = TRUE)
	{
		$matrix = array(
			"й"		 => "i",
			"ц"		 => "c",
			"у"		 => "u",
			"к"		 => "k",
			"е"		 => "e",
			"н"		 => "n",
			"г"		 => "g",
			"ш"		 => "sh",
			"щ"		 => "shch",
			"з"		 => "z",
			"х"		 => "h",
			"ъ"		 => "",
			"ф"		 => "f",
			"ы"		 => "y",
			"в"		 => "v",
			"а"		 => "a",
			"п"		 => "p",
			"р"		 => "r",
			"о"		 => "o",
			"л"		 => "l",
			"д"		 => "d",
			"ж"		 => "zh",
			"э"		 => "e",
			"ё"		 => "e",
			"я"		 => "ya",
			"ч"		 => "ch",
			"с"		 => "s",
			"м"		 => "m",
			"и"		 => "i",
			"т"		 => "t",
			"ь"		 => "",
			"б"		 => "b",
			"ю"		 => "yu",
			"Й"		 => "I",
			"Ц"		 => "C",
			"У"		 => "U",
			"К"		 => "K",
			"Е"		 => "E",
			"Н"		 => "N",
			"Г"		 => "G",
			"Ш"		 => "SH",
			"Щ"		 => "SHCH",
			"З"		 => "Z",
			"Х"		 => "X",
			"Ъ"		 => "",
			"Ф"		 => "F",
			"Ы"		 => "Y",
			"В"		 => "V",
			"А"		 => "A",
			"П"		 => "P",
			"Р"		 => "R",
			"О"		 => "O",
			"Л"		 => "L",
			"Д"		 => "D",
			"Ж"		 => "ZH",
			"Э"		 => "E",
			"Ё"		 => "E",
			"Я"		 => "YA",
			"Ч"		 => "CH",
			"С"		 => "S",
			"М"		 => "M",
			"И"		 => "I",
			"Т"		 => "T",
			"Ь"		 => "",
			"Б"		 => "B",
			"Ю"		 => "YU",
			"«"		 => "",
			"»"		 => "",
			" "		 => "-",
			"\""	 => "",
			"\."	 => "",
			"–"		 => "-",
			"\,"	 => "",
			"\("	 => "",
			"\)"	 => "",
			"\?"	 => "",
			"\!"	 => "",
			"\:"	 => "",
			'#'		 => '',
			'№'		 => '',
			' - '	 => '-',
			'—'		 => '-',
			'/'		 => '-',
			'  '	 => ' ',
		);

		// Enforce the maximum component length
		$maxlength	 = 100;
		$text		 = implode(array_slice(explode('<br>',
					wordwrap(trim(strip_tags(html_entity_decode($text))), $maxlength, '<br>', false)), 0, 1));

		//$text = substr(, 0, $maxlength);

		foreach($matrix as $from => $to)
			$text = mb_eregi_replace($from, $to, $text);

// Optionally convert to lower case.
		if ($toLowCase)
		{
			$text = strtolower($text);
		}

		return $text;
	}

	/**
	 *  Костиль перекодування імены файлу для збереження на сервері (тестить під кожний сервак!!!)
	 */
	static function iconvFileName($file)
	{
		if (!preg_match('{Ubuntu}i', $_SERVER['SERVER_SOFTWARE']))
		{
			return iconv('UTF-8', 'windows-1251//TRANSLIT', trim($file));
			$converted = @iconv("UTF-8", $encoding . "//IGNORE//TRANSLIT", $fileName);
		}
		else
		{
			return trim($file);
		}
	}

	/**
	 * Рекурсивне створення директорій з встановленням прав доступу
	 * @param type $path
	 * @param type $orginalpPath
	 * @throws Exception
	 */
	static function recursiveCreateDir($path, $orginalpPath = false)
	{
		if (is_dir($path))
			return;
		/* Перевіряєм чи існує директорія на рівень нижче */
		$prevDir = preg_replace('%\/[^\/]++$%ius', '', $path);
		if (!is_dir($prevDir))
		{
			/* Якщо не існує, спускаємся нарівень нижче і робим перевірку, разом з тим відправивши оригінальний шлях */
			self::recursiveCreateDir($prevDir, (!$orginalpPath) ? $path : $orginalpPath);
		}
		else
		{
			/* Якщо директорія існує, робимо перевірку на можливість запису в неї фалів */
			if (substr(decoct(fileperms($prevDir)), 1) != '0777')
			{
				throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Error'
				. '<br>Одна из директорий пути недоступна для записи -> mkdir<br>' . $prevDir, 406);
			}
			/* Якщо директорія записується то створюєм в ній нову директорію */
			elseif (!@mkdir($path))
			{
				throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Error'
				. '<br>mkdir' . $path, 406);
			}
			elseif (!@chmod($path, 0777))
			{
				throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Error'
				. '<br>Невозвожно изменить права доступа: ' . $path, 406);
			}

			/* Умова при якій відбувається вихід з рекурсії або ж повторний виток циклів */
			if ($orginalpPath)
				self::recursiveCreateDir($orginalpPath);
		}
	}

}