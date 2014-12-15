<?php

/**
 * EditorOperationTempDirBehavior
 *
 * @author Rusavskiy Vitaliy <rusavskiy@gmail.com>
 * @version 1.0.0
 */
class EditorOperationTempDirBehavior extends CBehavior
{
	/* Назва тимчасової папки */

	const EditorTempDir	 = 'editorTempDir';
	/* Назва зображення яка відображатиметься в списку історії змін */
	const NameImage		 = 'image';

	/* Список завантажених файлів */
	public $listNameFilesStore = array();

	/**
	 * обєкт з інформацією про зображення яке редагується
	 */
	public $objFileData;

	/* Список змін */
	private $listHistory = array();

	/* Назва темп папки */
	private $nameTempDir	 = '';
	private $webroot		 = '';
	private $pathImg		 = '';
	private $pathImgOriginal = '';


	/* Директорія темп папок */
	private $dir = '';

	/* Шлах до робочої темп папки */
	private $pathTempDir = '';
	private $urlTempDir	 = '';

	/* Керуючі дані */
	public $elemaneChange = '';

	public function initEditorOperationTempDir()
	{
		try
		{
			$this->webroot = Yii::getPathOfAlias('webroot');
			$this->setPathImg();
			$this->createNameTempDir();
			$this->сreateTempDir();

			if (stristr($this->elemaneChange, EditorFileUploaderController::CLOSE))
			{
				$this->deleteTempDir();
				$this->FilterTepmDirYesterday();
			}
			else
			{
				$this->copyImage();
				$this->setListHistory();
			}
		} catch(Exception $exc)
		{
			Yii::app()->end(__CLASS__ . ' ' . $exc->getMessage());
		}
	}

	public function getListHistory()
	{
		return $this->listHistory;
	}

	public function getFileData()
	{
		return $this->objFileData;
	}

	/* Визначення шляху до зображень і перевірка їх існування */
	private function setPathImg()
	{
		if (!empty($this->listNameFilesStore))
		{
			$this->pathImg			 = '';
			$this->pathImgOriginal	 = '';
			foreach($this->listNameFilesStore as $k => $v)
			{
				(!$this->pathImg) ? $this->pathImg			 = $this->webroot . '/' . $v : '';
				($k === ImageProcessingBehavior::PREFIX_ORIGINAL_IMAGE) ? $this->pathImgOriginal	 = $this->webroot . '/' . $v : '';
			}

			if (!is_file($this->pathImg))
				throw new Exception(' Error->copyImage()! Not Image');
			if ($this->pathImgOriginal && !is_file($this->pathImgOriginal))
				throw new Exception(' Error->copyImage()! Not Original Image');

			#echo"<pre class='casper'>> "; print_r($this->pathImg); echo"</pre>";
			#echo"<pre class='casper'>> "; print_r($this->pathImgOriginal); echo"</pre>";
		}
		else
			throw new Exception('Error! No listNameFilesStore');
	}

	/**
	 * Створення імені темп директорії (дата_ід)
	 */
	private function createNameTempDir()
	{
		if (isset($_COOKIE['PHPSESSID']) && $_COOKIE['PHPSESSID'])
		{
			$this->nameTempDir = date('Ymd_') . $_COOKIE['PHPSESSID'];
			#/**/echo"<pre class='prt'>> ";print_r($this->nameTempDir);echo"</pre>";/**/
		}
		else
		{
			throw new Exception('Error->CreateNameTempDir()! Not PHPSESSID');
		}
	}

	/**
	 * Перевірка існування директорії інакше її створення
	 */
	private function сreateTempDir()
	{
		preg_match('{^.+\/}i', $this->pathImg, $res);
		#echo"<pre class='casper'>> ";print_r($res);echo"</pre>";

		$this->dir			 = $res[0] . self::EditorTempDir;
		$this->pathTempDir	 = $this->dir . '/' . $this->nameTempDir;
		$this->urlTempDir	 = Yii::app()->baseUrl . preg_replace('{' . $this->webroot . '}iu', '', $this->pathTempDir);

		#/**/echo"<pre class='prt'>> "; print_r($this->pathTempDir); echo"</pre>"; /**/
		#/**/echo"<pre class='prt'>> "; print_r($this->urlTempDir); echo"</pre>"; /**/

		/* Якщо директорія не існує то створюєм інакше очищаэм папку, якщо немає даних про редагуємий файл*/
		if (!file_exists($this->pathTempDir) || !opendir($this->pathTempDir))
			mkdir($this->pathTempDir, 0777, true);
		elseif (!$this->objFileData)
			$this->clearDirectory($this->pathTempDir);
	}

	/**
	 * Копіювання зображень в тимчасову директорію, якщо їх там ще немає
	 */
	private function copyImage()
	{
		$pathinfo = (object) pathinfo($this->pathImg);
		#/**/echo"<pre class='prt'>> "; print_r($pathinfo); echo"</pre>"; /**/

		$destImage = $this->pathTempDir . '/' . self::NameImage . '.' . $pathinfo->extension;
		if (!is_file($destImage) || $this->elemaneChange == EditorFileUploaderController::SAVE)
		{
			if (!copy($this->pathImg, $destImage))
				throw new Exception('not copy pathImg!!!');

			// Якщо є оригінал зображення - копіюєм його
			if ($this->pathImgOriginal)
			{
				$destOriginalImage = $this->pathTempDir . '/' .
					ImageProcessingBehavior::PREFIX_ORIGINAL_IMAGE . '_' .
					self::NameImage . '.' .
					$pathinfo->extension;

				if (!is_file($destOriginalImage))
					if (!copy($this->pathImgOriginal, $destOriginalImage))
						throw new Exception('not copy pathImgOriginal!!!');
			}
		}
		$this->setFileData($pathinfo);
	}

	/**
	 * Формування обєкту FileData - інформація про зображення яке редагується
	 */
	private function setFileData($pathinfo)
	{
		/* Якщо даних ще не існує - створюєм */
		if (!$this->objFileData)
		{
			$this->objFileData = (object) array(
					'nameFile'		 => self::NameImage . '.' . $pathinfo->extension,
					'extension'		 => $pathinfo->extension,
					'pathTempDir'	 => $this->pathTempDir,
					'src'			 => $this->urlTempDir . '/' . self::NameImage . '.' . $pathinfo->extension . '?' . mt_rand()
			);
		}
		#/**/echo"<pre class='prt'>> "; print_r($this->objFileData); echo"</pre>"; /**/
	}

	/**
	 * Створення списку історії
	 */
	private function setListHistory()
	{
		if ($this->pathTempDir)
		{
			$res	 = glob($this->pathTempDir . '/*.*');
			#CVarDumper::dump($res, 10, true);
			$array1	 = $array2	 = array();
			foreach($res as $v)
			{
				$name = preg_replace('{(^.+\/)|(\.\w+$)}iu', '', $v);
				#echo"<pre class='casper'>> "; print_r($name); echo"</pre>";

				if ($name == self::NameImage || $name == ImageProcessingBehavior::PREFIX_ORIGINAL_IMAGE . '_' . self::NameImage)
					$array1[]		 = array(
						'name'			 => $name,
						'fullName'		 => preg_replace('{^.+\/}iu', '', $v),
						'pathImage'		 => $v,
						'abilityRemove'	 => 0,
					);
				else
					$array2[$name]	 = array(
						'name'			 => $name,
						'fullName'		 => preg_replace('{^.+\/}iu', '', $v),
						'pathImage'		 => $v,
						'abilityRemove'	 => 1,
					);
			}
			$this->listHistory = array_merge($array1, $array2);
		}
		#/**/echo"<pre class='prt'>> "; print_r($this->listHistory); echo"</pre>"; /**/
	}

	/**
	 * Видалення зображень в тимчасовій папці і видалення самої тимчасової папки
	 */
	private function deleteTempDir()
	{
		if (is_dir($this->pathTempDir))
		{
			$this->removeDirectory($this->pathTempDir);
			$this->objFileData = false;
		}
	}

	/**
	 * Якщо директорія збереження темп файлів не пуста - Перевірка існування темп файлів які були створені
	 * не сьогодні, збереження їх назв.
	 */
	private function FilterTepmDirYesterday()
	{

		if (is_dir($this->dir))
		{
			$dir = glob($this->dir . "/*");
			#/**/echo"<pre class='prt'>> "; print_r($dir); echo"</pre>"; /**/die;

			$date = date('Ymd');
			if ($dir)
			{
				foreach($dir as $v)
				{
					if (is_dir($v))
					{
						$v		 = preg_replace('{\\\+}', '/', $v);
						preg_match('{/(\w+)_\w+$}', $v, $res);
						$name	 = $res[1];
						if ((int) $name && (int) $name < (int) $date)
						{
							#/**/echo"<pre class='prt'>> "; print_r($v); echo"</pre>"; /**/
							$this->removeDirectory($v);
						}
					}
				}
			}
		}
	}

	/**
	 * Удаление каталогов с файлами
	 */
	private function removeDirectory($dir)
	{
		#/**/echo"<pre class='prt'>> ";print_r($dir);echo"</pre>";/**/
		if ($objs = glob($dir . "/*"))
		{
			foreach($objs as $obj)
			{
				#/**/echo"<pre class='prt'>> ";print_r($obj);echo"</pre>";/**/
				if (is_dir($obj))
					$this->removeDirectory($obj);
				else
				if (!@unlink($obj))
					throw new Exception(__CLASS__ . '<br> Файл ' . $obj . '<br> Не удаляется!');
			}
		}
		if (!@rmdir($dir))
			throw new Exception(__CLASS__ . '<br> Директория ' . $dir . '<br> Не удаляется!');
	}

	/**
	 * Удаление каталогов с файлами
	 */
	private function clearDirectory($dir)
	{
		if ($objs = glob($dir . "/*"))
		{
			foreach($objs as $obj)
			{
				#/**/echo"<pre class='prt'>> ";print_r($obj);echo"</pre>";/**/
				if (is_dir($obj))
					$this->removeDirectory($obj);
				else
				if (!@unlink($obj))
					throw new Exception(__CLASS__ . '<br> Файл ' . $obj . '<br> Не удаляется!');
			}
		}
	}

}