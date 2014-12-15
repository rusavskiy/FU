<?php

/**
 * EditorCropBehavior
 *
 * Створює ватермарк для зображення по заданим координатам
 * Переформовує (інформацією про зображення яке редагується) і віддає її назад в редактор
 *
 * @author Rusavskiy Vitaliy <rusavskiy@gmail.com>
 * @version 1.0.0
 */
class EditorWatermarkBehavior extends CBehavior
{

      public $objDataClient;

      /**
       * інформацією про зображення яке редагується
       */
      public $objFileData;

      /**
       * Координати ватермарку
       */
      public $watermarkData = array ( );

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

      /**
       * Назва папки для збереження ватермарків
       */
      private $nameDirImgWatermark = 'imgWatermark';

      /**
       * повний шлях до папки збереження ватрмарків
       */
      private $pathDirImgWatermark;

      /**
       * повний шлях до ватрмарка
       */
      private $pathImgWatermark;

      public function initEditorWatermark()
      {
            if ($this->watermarkData && isset($this->watermarkData['h']) && $this->watermarkData['h'])
            {
                  try
                  {
                        $this->initializationParameters();
                        $this->CreateIh();
                        $this->createNameNewImage();
                        $this->operationsImgWatermark();
                        $this->watermark();
                        $this->setFileData();
                  } catch (Exception $exc)
                  {
                        Yii::app()->end(__CLASS__ . ' ' . $exc->getMessage());
                  }
            }
      }

      public function getFileDataWatermark()
      {
            return $this->objFileData;
      }

      /**
       * Ініціалізація необхідних параметрів
       */
      private function initializationParameters()
      {
            if (isset($this->objFileData->pathTempDir) && $this->objFileData->pathTempDir)
            {
                  $this->pathTempDir         = $this->objFileData->pathTempDir;
                  $this->pathDirImgWatermark = $this->pathTempDir . '/' . $this->nameDirImgWatermark;
            }
            else
            {
                  throw new Exception('Error! No objFileData->pathTempDir');
            }

            if (isset($this->objFileData->nameFile) && $this->objFileData->nameFile)
            {
                  $this->pathFile = $this->pathTempDir . '/' . $this->objFileData->nameFile;
            }
            else
            {
                  throw new Exception('Error! No objFileData->nameFile');
            }

            if (isset($this->objFileData->extension) && $this->objFileData->extension)
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
       * Створення тимчасової директорії для зображення ватера і ресайз самого ватера по переданим параметрам
       */
      private function operationsImgWatermark()
      {
            if (!file_exists($this->pathDirImgWatermark) || !opendir($this->pathDirImgWatermark))
                  mkdir($this->pathDirImgWatermark, 0777, true);

            if (Yii::app()->createUrl($this->objDataClient->editorOptions->watermark_img))
            {
                  $this->pathImgWatermark = $this->pathDirImgWatermark . '/' . 'water.png';
                  $ih                     = new U_ImageHandler();
                  $ih->load($this->objDataClient->editorOptions->watermark_img)
                          ->resize($this->watermarkData['w'], $this->watermarkData['h'], FALSE)
                          ->save($this->pathImgWatermark);
            }
      }

      /**
       *
       */
      private function watermark()
      {
            if ($this->watermarkData['w'] && $this->watermarkData['h'])
            {
                  $this->ih
                          ->watermark($this->pathImgWatermark, $this->watermarkData['x1'], $this->watermarkData['y1'], U_ImageHandler::CORNER_LEFT_TOP )
                          ->save($this->pathTempDir . '/' . $this->newNameImage);
            }
            #/** @DUMPER */ varDumperCasper::dump($this->objFileData, array ( 'but' )); /**/
      }

      /**
       * Переформування обєкту FileData з інформацією про зображення яке редагується
       */
      private function setFileData()
      {
            #$this->objFileData = $this->objFileData; return;
            $this->objFileData = (object)array (
                        'nameFile'    => $this->newNameImage,
                        'extension'   => $this->extension,
                        'pathTempDir' => $this->pathTempDir,
                        'src'         => preg_replace('{/' . $this->objFileData->nameFile . '$}', '/' . $this->newNameImage, $this->objFileData->src)
            );
            #/**/echo"<pre class='prt'>> "; print_r($this->objFileData); echo"</pre>"; /**/
      }

}