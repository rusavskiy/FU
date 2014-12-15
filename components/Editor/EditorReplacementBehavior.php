<?php

/**
 * EditorReplacementBehavior
 *
 * @author Rusavskiy Vitaliy <rusavskiy@gmail.com>
 * @version 1.0.0
 */
class EditorReplacementBehavior extends CBehavior
{

	/**
	 * інформацією про зображення яке редагується
	 */
	public $objFileData;

	/**
	 * Містить об*єкт параметрів переданих клієнським віджетом
	 * @var type object
	 */
	public $objDataClient;

	public function initEditorReplacement()
	{
		try
		{
			$this->replacementImage();
		} catch(Exception $exc)
		{
			Yii::app()->end(__CLASS__ . ' ' . $exc->getMessage());
		}
	}

	/**
	 * Заміна зображення
	 */
	private function replacementImage()
	{
		if (
			isset($this->objDataClient->nameFile)
			&& isset($this->objDataClient->dir)
			&& $this->objDataClient->nameFile
			&& $this->objDataClient->dir
			&& $this->objFileData
		)
		{
			$path		 = Yii::getPathOfAlias('webroot') . '/' . $this->objDataClient->dir . '/';
			$pathImage	 = $path . $this->objDataClient->nameFile;

			if (!is_file($pathImage))
			{
				throw new Exception('Error->replacementImage()! Not Image');
			}

			if (!empty($this->objDataClient->CImageHandlerParams) && $this->fullPathDir && $this->fileIsImage)
			{
				if (!extension_loaded('gd'))
					throw new Exception('Требуеться наличие библиотеки GD.', 404);

				ini_set('memory_limit', '-1');
				ini_set('max_execution_time', '120');

				// Підключення "Поведение"
				$this->attachBehavior('ImageProces',
					array(
					'class'				 => 'ImageProcessingBehavior',
					'objDataClient'		 => $this->objDataClient,
					'pathFile'			 => $this->pathFile,
					'fullPathDir'		 => $this->fullPathDir,
					'pathInfoFileUpload' => $this->pathInfoFileUpload,
				));
				$this->initImageProcessing();
			}

			copy($this->objFileData->pathTempDir . '/' . $this->objFileData->nameFile, $pathImage);
		}
	}

}