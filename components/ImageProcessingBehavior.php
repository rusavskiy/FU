<?php

/**
 * ImageProcessingBehavior class file.
 * Клас виконує обробку зображення
 * @author Rusavskiy Vitaliy <rusavskiy@gmail.com>
 * @version 1.1.0
 * @date 2013.12.29
 */
final class ImageProcessingBehavior extends CBehavior
{

	const PREFIX_ORIGINAL_IMAGE = 'original'; // префікс до оригінально зображ

	public $objDataClient;

	/**
	 * Повний фізчний шлях до завантаженого файлу
	 */
	public $pathFile;

	/**
	 * Повний фізчний шлях до папки для завантаження
	 */
	public $fullPathDir;

	/**
	 * Містить об*єкт з інфою про файл
	 */
	public $pathInfoFileUpload;

	/**
	 * хеш директорії перед файлом
	 * @var type
	 */
	private $hashDir;

	/**
	 * опис в класі який розширюється
	 */
	private $params;

	/**
	 * Дирикторія завантаження файлу від кореня сайта
	 */
	private $pathClientDir;

	/**
	 * Максимальна ширина і висота для зображ. array( W , H )
	 */
	private $maxWH;

	/**
	 * (bool) Зберегти оригінал файлу (для зображень)
	 */
	private $saveOriginal;

	/**
	 * Список створених файлов (при одноразовому завантаженні файлу)
	 */
	private $listNameImageStore = array();

	/**
	 * Об*єкт класа обробки зображення
	 */
	private $ih;

	/**
	 * Типи зображень які можна обробляти
	 */
	private $IMG_GIF_JPEG_PNG = array( '1', '2', '3' );

	/**
	 * Дані про зображення (позмір, тип)
	 */
	private $ImageSize = array();

	/**
	 * Повний фізчний шлях до кореня сайта
	 */
	private $webroot = '';

	public function initImageProcessing()
	{
		Yii::import('ext.' . basename(pathinfo(dirname(__FILE__), PATHINFO_DIRNAME)) . '.components.ImageHandler.U_ImageHandler');
		$this->setParams();
		$this->processingTypeImage();
		$this->createIh();
		$this->limitationMaximumExpansion();
		$this->saveOriginalSize();
		$this->thumb();
		$this->resize();
		$this->adaptiveThumb();
		$this->resizeCanvas();
		#/**/echo"<pre class='prt'>> "; print_r($this->listNameImageStore); echo"</pre>"; /**/
	}

	private function setParams()
	{
		$this->pathClientDir = $this->objDataClient->dir;
		$this->saveOriginal	 = $this->objDataClient->saveOriginal;
		$this->params		 = $this->objDataClient->CImageHandlerParams;
		$this->maxWH		 = $this->objDataClient->maxWH;
		$this->hashDir		 = $this->objDataClient->hashDir;

		/* Якщо використовується хеш директорії */
		if ($this->hashDir)
			$this->pathClientDir = $this->pathClientDir . '/' . $this->hashDir;

		#echo"<pre class='casper'>> ";print_r($this->pathClientDir);echo"</pre>";
		#echo"<pre class='casper'>> ";print_r($this->fullPathDir);echo"</pre>";die;

		if (!$this->pathFile)
			throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Error!!! pathFile');

		if (!$this->fullPathDir)
			throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Error!!! fullPathDir');

		if (!$this->pathInfoFileUpload || !is_object($this->owner->pathInfoFileUpload))
			throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Error!!! pathInfoFileUpload');

		$this->webroot = Yii::getPathOfAlias('webroot');
	}

	/**
	 *  Перевірка типу зображення
	 */
	private function processingTypeImage()
	{
		if ($this->pathFile)
		{
			$this->ImageSize = getimagesize($this->pathFile);
			#/**/echo"<pre class='prt'>> "; print_r($this->ImageSize); echo"</pre>"; /**/

			if (empty($this->ImageSize[2]) || !in_array($this->ImageSize[2], $this->IMG_GIF_JPEG_PNG))
				throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' ERROR processingTypeImage  GIF JPEG PNG');
		}
		else
			throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' ERROR Undefined pathFile!!!');
	}

	private function createIh()
	{
		if (class_exists('U_ImageHandler'))
		{
			$this->ih = new U_ImageHandler();
			$this->ih->load($this->pathFile);
		}
		else
			throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Not isset U_ImageHandler!');
	}

	/**
	 *  Приведення(зжимання) до Максимальної ширини і висоти зображ. array( W , H )
	 */
	private function limitationMaximumExpansion()
	{
		if ($this->maxWH && is_array($this->maxWH))
			$this->ih->thumb($this->maxWH[0], $this->maxWH[1])->save();
	}

	/**
	 *  Збереження зображеня в оригінальному розмірі для можливості подальшого редагування
	 */
	private function saveOriginalSize()
	{
		if ($this->saveOriginal)
		{
			$name = self::PREFIX_ORIGINAL_IMAGE .
				$this->pathInfoFileUpload->filename . '.' .
				$this->pathInfoFileUpload->extension;

			$this->ih->save($this->fullPathDir . '/' . $name);

			$this->listNameImageStore['original'] = $this->pathClientDir . '/' . $name;
		}
	}

	/**
	 *  Конвертация в черно-белое изображение
	 */
	private function grayscale()
	{
		if (isset($this->params->grayscale) && $this->params->grayscale)
		{
			$this->ih->grayscale($this->params->grayscale)->save();
			$this->ih->load($this->pathFile);
		}
	}

	/**
	 *  Создание превюшек thumb($toWidth, $toHeight, $proportional = true);
	 */
	private function thumb()
	{
		if (!empty($this->params->thumb) && is_array($this->params->thumb))
		{
			foreach($this->params->thumb as $v)
			{
				$params = $this->procesingParametrsImgThumb($v, __METHOD__);
				$this->ih->reload()->thumb($params->width, $params->height, $params->proportional)->save($params->fullPathImg);
			}
		}
	}

	/**
	 *  Ресайз(мастабирование) картинок resize($toWidth, $toHeight, $proportional = true);
	 */
	private function resize()
	{
		if (!empty($this->params->resize) && is_array($this->params->resize))
		{
			foreach($this->params->resize as $v)
			{
				$params = $this->procesingParametrsImgThumb($v, __METHOD__);
				$this->ih->reload()->resize($params->width, $params->height, $params->proportional)->save($params->fullPathImg);
			}
		}
	}

	/**
	 *  Превюшка с подгоном размера и обрезкой лишнего adaptiveThumb($width, $height);
	 */
	private function adaptiveThumb()
	{
		if (!empty($this->params->adaptiveThumb) && is_array($this->params->adaptiveThumb))
		{
			foreach($this->params->adaptiveThumb as $v)
			{
				$params = $this->procesingParametrsImgThumb($v, __METHOD__);
				$this->ih->reload()->adaptiveThumb($params->width, $params->height)->save($params->fullPathImg);
			}
		}
	}

	/**
	 *  Превюшка с заливкой бекграунда ($toWidth, $toHeight, $backgroundColor));
	 */
	private function resizeCanvas()
	{
		if (!empty($this->params->resizeCanvas) && is_array($this->params->resizeCanvas))
		{
			foreach($this->params->resizeCanvas as $v)
			{
				$params = $this->procesingParametrsImgThumb($v, __METHOD__);
				$this->ih->reload()->resizeCanvas($params->width, $params->height, $params->backgroundColor)->save($params->fullPathImg);
			}
		}
	}

	/**
	 * Повертає об'єкт з опрацьованими параметрами
	 * @param type $params
	 * @param type $flag
	 * @return string
	 * @throws Exception
	 */
	private function procesingParametrsImgThumb($setParams, $flag = '')
	{
		$params = (object) array(
				'width'				 => 0,
				'height'			 => 0,
				'backgroundColor'	 => array( 255, 255, 255 ),
				'name'				 => '',
				'fullPath'			 => $this->fullPathDir,
				'pathClientDir'		 => $this->pathClientDir,
				'fullPathImg'		 => '',
				'proportional'		 => true,
		);
		#varDumperCasper::dump($params, 10, true);

		if ($flag)
		{
			/* Встановлення і перевірка ширини */
			if (isset($setParams[0]))
				$params->width = $setParams[0];
			else
				throw new Exception('Not false "width"');

			/* Встановлення і перевірка висоти */
			if (isset($setParams[1]))
				$params->height = $setParams[1];
			else
				throw new Exception('Not false "height"');

			/* Встановлення імені */
			$setParams[2]	 = (isset($setParams[2])) ? $setParams[2] : '';
			$params->name	 = $setParams[2] . $this->pathInfoFileUpload->filename . '.' . $this->pathInfoFileUpload->extension;


			// Якщо вказано іниший шлях завантаження для замба
			if (!empty($setParams[3]) && is_string($setParams[3]))
			{
				$params->fullPath		 = $this->webroot . '/';
				$params->pathClientDir	 = $setParams[3];

				/* Якщо використовується хеш директорії */
				if ($this->hashDir)
					$params->pathClientDir = $params->pathClientDir . '/' . $this->hashDir;

				$params->fullPath .= $params->pathClientDir;
				#echo"<pre class='casper'>> ";print_r($params->fullPath);echo"</pre>";
				FileUploaderController::recursiveCreateDir($params->fullPath);
			}

			$params->fullPathImg		 = $params->fullPath . '/' . $params->name;
			$this->listNameImageStore[]	 = $params->pathClientDir . '/' . $params->name;
			#varDumperCasper::dump($this->listNameImageStore, 10, true);

			switch($flag)
			{
				case __CLASS__ . '::thumb':
				case __CLASS__ . '::resize':
					if (isset($setParams[4]) && is_bool($setParams[4]))
						$params->proportional	 = $setParams[4];
					break;
				case __CLASS__ . '::resizeCanvas':
					if (isset($setParams[4]) && is_array($setParams[4]))
						$params->backgroundColor = $setParams[4];
					break;
			}
		}
		else
			throw new Exception('Not flag!!!');
		#varDumperCasper::dump($params, 10, true);
		return $params;
	}

	public function returnListNameImageStore()
	{
		return $this->listNameImageStore;
	}

}