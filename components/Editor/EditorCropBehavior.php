<?php

/**
 * EditorCropBehavior
 *
 * Обрізає зображення по заданим координатам
 * Переформовує (інформацією про зображення яке редагується) і віддає її назад в редактор
 *
 * @author Rusavskiy Vitaliy <rusavskiy@gmail.com>
 * @version 1.0.0
 */
class EditorCropBehavior extends CBehavior
{

	/**
	 * інформацією про зображення яке редагується
	 */
	public $objFileData;

	/**
	 * Координати кропу
	 */
	public $cropData = array();

	/**
	 * Об*єкт класа обробки зображення
	 */
	private $ih;

	/**
	 * Шлах до робочої темп папки
	 * @var type string
	 */
	private $pathTempDir = '';

	/**
	 * Шлах до зображення, яке потрібно обрізать
	 * @var type string
	 */
	private $pathFile = '';

	/**
	 * Назва нового зображення яке буде створено
	 */
	private $newNameImage = '';

	/**
	 * Розширення зображення
	 */
	private $extension = '';

	public function initEditorCrop()
	{
		if ($this->cropData && !empty($this->cropData['h']))
		{
			try
			{
				$this->initializationParameters();
				$this->CreateIh();
				$this->createNameNewImage();
				$this->crop();
				$this->setFileData();
			} catch(Exception $exc)
			{
				Yii::app()->end(__CLASS__ . ' ' . $exc->getMessage());
			}
		}
	}

	public function getFileDataCrop()
	{
		return $this->objFileData;
	}

	/**
	 * Ініціалізація необхідних параметрів
	 */
	private function initializationParameters()
	{
		if (!empty($this->objFileData->pathTempDir))
		{
			$this->pathTempDir = $this->objFileData->pathTempDir;
		}
		else
		{
			throw new Exception('Error! No objFileData->pathTempDir');
		}

		if (!empty($this->objFileData->nameFile))
		{
			$this->pathFile = $this->pathTempDir . '/' . $this->objFileData->nameFile;
		}
		else
		{
			throw new Exception('Error! No objFileData->nameFile');
		}

		if (!empty($this->objFileData->extension))
		{
			$this->extension = $this->objFileData->extension;
		}
		else
		{
			throw new Exception('Error! No objFileData->extension');
		}
	}

	private function CreateIh()
	{
		$this->ih = new U_ImageHandler();
		$this->ih->load($this->pathFile);
	}

	/**
	 * формування імені нового зображення
	 */
	private function createNameNewImage()
	{
		$this->newNameImage = date('H.i.s.') . $this->extension;
	}

	/**
	 * Обрізка
	 */
	private function crop()
	{
		if (!empty($this->cropData['w']) && !empty($this->cropData['h']))
		{
			$this->ih
				->crop($this->cropData['w'], $this->cropData['h'], $this->cropData['x1'], $this->cropData['y1'])
				->save($this->pathTempDir . '/' . $this->newNameImage);
		}
		#/** @DUMPER */ varDumperCasper::dump($this->objFileData, array ( 'but' )); /**/
	}

	/**
	 * Переформування обєкту FileData з інформацією про зображення яке редагується
	 */
	private function setFileData()
	{
		$this->objFileData = (object) array(
				'nameFile'		 => $this->newNameImage,
				'extension'		 => $this->extension,
				'pathTempDir'	 => $this->pathTempDir,
				'src'			 => preg_replace('{(^.+\/).*$}iu', '$1', $this->objFileData->src) . $this->newNameImage . '?' . mt_rand()
		);
		#/**/echo"<pre class='prt'>> "; print_r($this->objFileData); echo"</pre>"; /**/
	}

}

