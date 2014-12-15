<?php

/**
 * OperationTempFile class file.
 *
 * Клас призначений для роботи з тимчасовими файлами в які записані назви файлів завантажені користувачами.
 * Тимчасові файли виконують роль бази даних. Створюється тимчасові файли для кожного користувача окремо.
 *
 * РОБОТА КЛАСУ:
 * - Створення назви тимчасового файлу для користувача (по сесії)
 * - Перевірка існування дерикторії розташування для темп файлів
 * - Створення або відкриття темп файла користувача
 * - Читання вмісту темп файлу в масив
 * - Перевірка на співпадіння даних записаних і отриманих, якщо немає співпадіння додається назва файлу в масив
 * для запису в темп файл
 * - Перезаписуєм темп файл з доданими даними
 *
 * Якщо в клас передані дані про файли які не портрібно видалять - це означає, що стаття, в якій проводилося
 * завантаження файлів, зберігається в Базу даних.
 * В цьому випадку клас робить наступне:
 * - Видалення назв файлів(які не портрібно видалять) з масиву даних що містить темпфайл, щоб потім не видалять з ФТП.
 * - Видалення файлів з ФТП, які записані в темп_файл і потім видаляється сам темп_файл.
 * - Зчитування назв файлів з директорії в яку збередені темп файли
 * - Якщо директорія збереження темп файлів не пуста - Перевірка існування темп файлів які були створені
 * не сьогодні, збереження їх назв, зчитування з цих файлів даних в масив.
 * - Видалення файлів які були записані в темп файлах і які були створені не сьогодні
 *
 *
 * @author Rusavskiy Vitaliy <rusavskiy@gmail.com>
 * @version 1.0.0
 */
class OperationTempFileBehavior extends CBehavior
{

	const CATALOG_TEMP_FILE = '.runtime.TempFile';

	/**
	 * Список створених файлов (при одноразовому завантаженні файлу)
	 */
	public $listNameFilesStore = array();

	/**
	 * Ті ж дані що і в listNameFilesStore, лише передані з іншої події і виконують роль файлів,
	 * які невидаляються при збереженні статті(новини, тощо ...)
	 */
	public $listNotDeleteFiles = array();

	/**
	 * Назва темп файлу
	 */
	private $nameTempFile = '';

	/**
	 * Директорія знаходження темп файлів
	 */
	private $fullPathTempDir	 = '';
	private $pachTempFile		 = '';
	private $resourceTempFile	 = false;
	private $valueTempFile		 = array();

	/**
	 *  Файли, які знаходяться в темп директорії
	 */
	private $allFilesTempDir = array();

	/**
	 * файли, які записані в темп файлах
	 */
	private $dataTempFilesYesterday = array();

	public function initOperationTempFile()
	{
		if (!$this->listNameFilesStore && !$this->listNotDeleteFiles)
			return;

		$this->CreateNameTempFile();
		$this->CreateOrOpenTempFile();
		$this->ReadTempFile();
		$this->CompareValueTempFileAndListNameImageStore();
		$this->RewritingTempFile();

		if (!empty($this->listNotDeleteFiles))
		{
			$this->CleanerTempFile();
			$this->DeleteFileWhithTempFile();
			$this->ReadDirTempFiles();
			$this->FilterSaveNameTepmFilesYesterday();
			$this->DeleteTepmFilesYesterday();
		}
	}

	/**
	 * Створення імені темп файлу (дата_ід)
	 */
	private function CreateNameTempFile()
	{
		if (isset($_COOKIE['PHPSESSID']) && $_COOKIE['PHPSESSID'])
		{
			$this->nameTempFile = date('Ymd_') . $_COOKIE['PHPSESSID'];
			#/**/echo"<pre class='prt'>> ";print_r($this->nameTempFile);echo"</pre>";/**/
		}
		else
			throw new Exception(__CLASS__ . ' Error->CreateNameTempFile()! Not PHPSESSID');
	}

	/**
	 * Створюемо або відкриваемо темп файл
	 */
	private function CreateOrOpenTempFile()
	{
		$this->fullPathTempDir = Yii::getPathOfAlias('ext.' . basename(pathinfo(dirname(__FILE__), PATHINFO_DIRNAME)) . self::CATALOG_TEMP_FILE);

		if (!file_exists($this->fullPathTempDir) || !opendir($this->fullPathTempDir))
		{
			if (!@mkdir($this->fullPathTempDir, 0777, true))
				throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Error -> CreateOrOpenTempFile<br>Одна из директорий '
				. 'пути недоступна для записи -> mkdir<br>' . $this->fullPathTempDir, 1);
		}
		elseif (substr(decoct(fileperms($this->fullPathTempDir)), 1) != '0777')
			throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . 'Error -> CreateOrOpenTempFile<br> Директория недоступна '
			. 'для записи -> chmod ' . substr(decoct(fileperms($this->fullPathTempDir)), 1) . '<br>' . $this->fullPathTempDir, 1);

		$this->pachTempFile = $this->fullPathTempDir . '/' . $this->nameTempFile;

		# Відкриття або створення темп файла
		$this->resourceTempFile = fopen($this->pachTempFile, 'c+b');

		if (!$this->resourceTempFile)
			throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Error -> CreateOrOpenTempFile! Not resourceFile');
	}

	/**
	 * Читаєм дані з темп файлу в масив
	 */
	private function ReadTempFile()
	{
		if (file_exists($this->pachTempFile))
		{
			$this->valueTempFile = file($this->pachTempFile);
			#/**/echo"<pre class='prt'>> ";print_r($this->valueTempFile);echo"</pre>"; /**/
		}
	}

	/**
	 * Перевіряєм отриманий масив на співпадіння даних записаних і отриманих, якщо немає співпадіння додається назва
	 * файлу в  масив для запису в темп файл
	 */
	private function CompareValueTempFileAndListNameImageStore()
	{
		#/**/echo"<pre class='prt'> valueTempFile> "; print_r($this->valueTempFile); echo"</pre>"; /**/
		#/**/echo"<pre class='casper'>listNameFilesStore> "; print_r($this->listNameFilesStore); echo"</pre>"; /**/
		# Перевіряємо чи є дані в темп файлі
		if (!$this->valueTempFile && $this->listNameFilesStore)
			$this->valueTempFile = $this->listNameFilesStore;
		else
			$this->valueTempFile = array_merge($this->valueTempFile, $this->listNameFilesStore);

		# Прибираються переноси рядків, пробіли
		array_walk($this->valueTempFile, function(&$v, $k)
		{
			$v = trim($v);
		});

		$this->valueTempFile = array_unique($this->valueTempFile);
		#/**/echo"<pre class='prt'>> "; print_r($this->valueTempFile); echo"</pre>"; /**/
	}

	/**
	 * Перезаписуєм темп файл з доданими даними (використовується при завантаженні файлів)
	 */
	private function RewritingTempFile()
	{
		#/**/echo"<pre class='prt'>> "; print_r($this->valueTempFile); echo"</pre>"; /**/
		if ($this->valueTempFile)
			foreach($this->valueTempFile as $value)
				fwrite($this->resourceTempFile, $value . PHP_EOL);
//		else
//			throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Error -> RewritingTempFile!   Not valueTempFile');
		# Закриття файлу для подальшої можливості його видалення
		if (!fclose($this->resourceTempFile))
			throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Error -> RewritingTempFile!  fclose');
	}

	#===========================================================================================#
	################# МЕТОДИ ЯКІ ВИКОРИСТОВУЮТЬСЯ ПРИ ЗБЕРЕЖЕННІ СТАТІ ##########################
	#===========================================================================================#
	/**
	 * При збереження запису і поверненні назв завантажених файлів
	 * Видалення назв файлів(які не портрібно видалять) з масиву даних що містить темпфайл, щоб потім не видалить з ФТП.
	 */
	private function CleanerTempFile()
	{
		if ($this->valueTempFile)
		{
			#/**/echo"<pre class='prt'>> ";print_r($this->valueTempFile);echo"</pre>";/**/
			foreach($this->valueTempFile as $k => $v)
			{
				if (in_array(trim($v), $this->listNotDeleteFiles))
				{
					unset($this->valueTempFile[$k]);
				}
			}
			#/**/echo"<pre class='prt'>> ";print_r($this->valueTempFile);echo"</pre>";/**/
			#/**/echo"<pre class='prt'>> ";print_r($this->listNotDeleteFiles);echo"</pre>";/**/die;
		}
//		else
//			throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Error -> CleanerTempFile!   Not valueTempFile');
	}

	/**
	 * Видалення файлів з ФТП, які записані в темп_файл і потім видаляється сам темп_файл.
	 */
	private function DeleteFileWhithTempFile()
	{
		if ($this->valueTempFile)
		{
			foreach($this->valueTempFile as $value)
			{
				$file = $this->owner->webroot . '/' . FileUploaderController::iconvFileName($value);
				#echo"<pre class='casper'>> "; print_r($file); echo"</pre>";

				if (is_file($file) && !@unlink($file))
					throw new Exception(__CLASS__ . ' Error -> DeleteFileWhithTempFile Файл <br>' . $value . ' который записан в темп файл ' . $this->nameTempFile . ' не удаляется!');
			}
		}
		if (is_file($this->pachTempFile) && !@unlink(trim($this->pachTempFile)))
			throw new Exception(__CLASS__ . ' >>> ' . __LINE__ . ' Темп файл ' . $this->nameTempFile . ' не удаляется!');
	}

	/**
	 * Зчитування файлів з директорії(виборка темп файлів)
	 */
	private function ReadDirTempFiles()
	{
		if (is_dir($this->fullPathTempDir))
		{
			$this->allFilesTempDir = glob($this->fullPathTempDir . "/*");
			#/**/echo"<pre class='prt'>> "; print_r($this->allFilesTempDir); echo"</pre>"; /**/
		}
		else
			throw new Exception(__CLASS__ . ' Error -> ReadDirTempFiles!   Not DIR TEMP FILE!');
	}

	/**
	 * Якщо директорія збереження темп файлів не пуста - Перевірка існування темп файлів які були створені
	 * не сьогодні, збереження їх назв, зчитування з цих файлів даних в масив.
	 */
	private function FilterSaveNameTepmFilesYesterday()
	{
		#echo"<pre class='casper'>> ";print_r($this->allFilesTempDir);echo"</pre>";
		if (is_array($this->allFilesTempDir) && $this->allFilesTempDir)
		{
			$date = date('Ymd');
			foreach($this->allFilesTempDir as $v)
			{
				if (!is_dir($v))
				{
					$v		 = preg_replace('{\\\+}', '/', $v);
					preg_match('{/(\w+)_.+$}', $v, $res);
					#echo"<pre class='casper'>> ";print_r($res);echo"</pre>";
					$name	 = $res[1];

					if ((int) $name < (int) $date)
					{
						if ($file = @file($v))
						{
							$this->dataTempFilesYesterday = array_merge($this->dataTempFilesYesterday, $file);
						}

						// Видалення самих темп файлів, які були створені не сьогодні
						if (!@unlink($v))
							throw new Exception(__CLASS__ . ' Error -> FilterSaveNameTepmFilesYesterday<br> Файл ' . $v . ' Не удаляется!');
					}
				}
			}
			#/**/echo"<pre class='prt'>> "; print_r($this->dataTempFilesYesterday); echo"</pre>"; /**/
		}
	}

	/**
	 * Видалення файлів які були записані в темп файлах і які були створені не сьогодні
	 */
	private function DeleteTepmFilesYesterday()
	{
		if ($this->dataTempFilesYesterday)
		{
			foreach($this->dataTempFilesYesterday as $v)
			{
				#/**/echo"<pre class='prt'>> "; print_r($v); echo"</pre>"; /**/
				if (!@unlink(trim($this->owner->webroot . '/' . $v)))
					throw new Exception(__CLASS__ . ' Error -> DeleteTepmFilesYesterday<br> Файл ' . $v . ' Не удаляется!');
			}
		}
	}

}