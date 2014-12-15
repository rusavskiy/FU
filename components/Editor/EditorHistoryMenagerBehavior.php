<?php

/**
 * EditorHistoryMenagerBehavior
 *
 * @author Rusavskiy Vitaliy <rusavskiy@gmail.com>
 * @version 1.0.0
 */
class EditorHistoryMenagerBehavior extends CBehavior
{
	/* інформацією про зображення яке редагується */

	public $objFileData;

	/* Список змін */
	public $listHistory = array();

	/* Керуючі дані */
	public $elemaneChange = array();

	public function getFileDataHistoryMenager()
	{
		return $this->objFileData;
	}

	public function initEditorHistoryMenager()
	{
		#/* @DUMPER */ varDumperCasper::dump($this->elemaneChange, array( 'but' )); /**/
		#/* @DUMPER */ varDumperCasper::dump($this->listHistory, array( 'but' )); /**/
		#/* @DUMPER */ varDumperCasper::dump($this->objFileData, array( 'but' )); /**/
		if (is_array($this->elemaneChange))
		{
			if (!empty($this->elemaneChange['flag']) && !empty($this->elemaneChange['nameFile']))
			{
				switch($this->elemaneChange['flag'])
				{
					case 'restore':
						$this->restoreElemetHistory();
						break;
					case 'delete':
						$this->deleteElemetHistory();
						break;
				}
			}
			else
				throw new Exception('Error! Wrong elemaneChange!!!');
		}
	}

	/**
	 * Переформування обєкту FileData з інформацією про зображення яке редагується
	 */
	private function restoreElemetHistory()
	{
		if (is_array($this->listHistory))
		{
			foreach($this->listHistory as $v)
			{
				if ($this->elemaneChange['nameFile'] == $v['fullName'])
				{
					$this->objFileData = (object) array(
							'nameFile'		 => $v['fullName'],
							'extension'		 => $this->objFileData->extension,
							'pathTempDir'	 => $this->objFileData->pathTempDir,
							'src'			 => preg_replace('{(^.+\/).*$}iu', '$1', $this->objFileData->src) . $v['fullName'] . '?' . mt_rand()
					);
					break;
				}
			}
			#echo"<pre class='casper'>> "; print_r($this->objFileData); echo"</pre>";
		}
		else
			throw new Exception('Error! listHistory is not array');
	}

	private function deleteElemetHistory()
	{
		if (is_array($this->listHistory))
		{
// Коли співпадають зображення яке видаляється і переглядається
			foreach($this->listHistory as $v)
			{
				if ($this->elemaneChange['nameFile'] == $v['fullName'])
				{
					if ($this->objFileData->nameFile == $this->elemaneChange['nameFile'])
					{
						/**
						 * Переформування обєкту FileData з інформацією про зображення яке редагується
						 */
						$this->objFileData = (object) array(
								'nameFile'		 => $prev['fullName'],
								'extension'		 => $this->objFileData->extension,
								'pathTempDir'	 => $this->objFileData->pathTempDir,
								'src'			 => preg_replace('{(^.+\/).*$}iu', '$1', $this->objFileData->src) . $prev['fullName'] . '?' . mt_rand()
						);
					}
					@unlink($v['pathImage']);
					break;
				}
				$prev = $v;
			}
		}
		else
			throw new Exception('Error! listHistory is not array');
	}

}