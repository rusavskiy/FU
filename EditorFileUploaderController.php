<?php
/**
 * FileUploaderEditorController class file.
 * @author Rusavskiy Vitaliy <rusavskiy@gmail.com>
 * @version 1.0.0
 */
Yii::import('ext.' . basename(dirname(__FILE__)) . '.components.*');
Yii::import('ext.' . basename(dirname(__FILE__)) . '.components.Editor.*');
Yii::import('ext.' . basename(dirname(__FILE__)) . '.components.ImageHandler.*');

class EditorFileUploaderController extends FileUploaderController
{

	const NAME_CONTROLLER_URL	 = 'fileuploadereditor';
	const SAVE				 = 'save';
	const CLOSE				 = 'close';
	const SAVE_CLOSE		 = 'save close';

	public $layout		 = '\\layouts\main';
	public $htmlOptions	 = array();

	/**
	 * Містить строку параметрів переданих клієнським віджетом
	 * @var type string
	 */
	private $dataClient			 = '';
	private $listNameFilesStore	 = array();

	/**
	 * Містить об*єкт параметрів переданих клієнським віджетом
	 * @var type object
	 */
	private $objDataClient;
	private $objFileData;

	/**
	 * Список змін
	 * @var type array
	 */
	private $listHistory = array();

	public function init()
	{
		parent::init();
		FileUploaderController::registerClientScript(array( 'editor' ));
	}

	/**
	 * ПОДІЯ
	 * CROP
	 */
	public function actionIndex()
	{
		// Вибір по замовчуванню
		if (empty($_GET['view']))
			$_GET['view'] = 'crop';

		switch($_GET['view'])
		{
			case 'watermark':
				$this->forward('watermark');
				break;

			default:
				$this->forward('crop');
				break;
		}
	}

	public function actionCrop()
	{
		#/* @DUMPER */ CVarDumper::dump($_GET, 10, true); /**/
		#echo"<pre class='casper'>> ";print_r($_POST);echo"</pre>";
		$action_form = Yii::app()->controller->createUrl('index', array( 'view' => 'crop' ));
		$this->decodePostData();
		$this->EditorCrop();
		$this->MenagerElementListHistory();
		$this->EditorReplacement();
		$this->EditorOperationTempDir();

		$this->ClientScript();
		$this->setHtmlOptions();
		$this->render('editorImage/editorImage',
			array(
			'action_form'		 => $action_form,
			'dataClient'		 => $this->dataClient,
			'listNameFilesStore' => $this->listNameFilesStore,
			'listHistory'		 => $this->listHistory,
			'objFileData'		 => $this->objFileData,
			'htmlOptions'		 => $this->htmlOptions,
		));
	}

	public function actionWatermark()
	{
		#/** @DUMPER */ CVarDumper::dump($_GET, 10, true); /**/
		#/** @DUMPER */ CVarDumper::dump($_POST, 10, true); /**/

		$action_form = Yii::app()->controller->createUrl('index', array( 'view' => 'watermark' ));

		$this->decodePostData();
		$this->EditorWatermark();
		$this->MenagerElementListHistory();
		$this->EditorReplacement();
		$this->EditorOperationTempDir();

		$this->ClientScript();
		$this->setHtmlOptions();
		#$this->render('editorImageWatermark', array (
		$this->render('editorImage/editorImage',
			array(
			'action_form'		 => $action_form,
			'dataClient'		 => $this->dataClient,
			'listNameFilesStore' => $this->listNameFilesStore,
			'listHistory'		 => $this->listHistory,
			'objFileData'		 => $this->objFileData,
			'htmlOptions'		 => $this->htmlOptions,
		));
	}

	private function ClientScript()
	{
		Yii::app()->clientScript->registerScript('editorImage',
			"var event = '" . $_GET['view'] . "',
				objEditor = CSPR.EditorImg(
				{
					debug:					'0',
					actionFormCrop:			'" . Yii::app()->controller->createUrl('index', array( 'view' => 'crop' )) . "',
					actionFormWatermark:	'" . Yii::app()->controller->createUrl('index', array( 'view' => 'watermark' )) . "',
					watermark_img:			'" . Yii::app()->baseUrl . '/' . $this->objDataClient->editorOptions->watermark_img . "',
					elemaneChangeSAVE:		'" . self::SAVE . "',
					elemaneChangeSAVE_CLOSE:'" . self::SAVE_CLOSE . "',
					elemaneChangeCLOSE:		'" . self::CLOSE . "',
					elemaneChangeCREATE:	'" . $_GET["view"] . "',
					objJQury: {
						listNameFilesStore: '',
					}
				}
			);
			objEditor.EventsOnAllPages();
			if(event === 'crop'){
				objEditor.EventCrop();
			}else if(event === 'watermark'){
				objEditor.EventWatermark();
			}
		");
		return false;

		if (0)

			?><script>

		<?php
		ob_start();
		if (isset($_POST['elemaneChange']) && stristr($_POST['elemaneChange'], self::CLOSE))
		{
			?>
				// Закриття фрейма
				$(parent.top.document.getElementById('iframeDivFileUploader')).hide();

				// Оновлення зображення
				var src = parent.$('#file_<?php echo $this->objDataClient->htmlID ?>').attr('src');
				src = src + (src.match(/\?/ig) === null ? "?" : "&") + new Date().getTime();
				$(parent.top.document.getElementById('file_<?php echo $this->objDataClient->htmlID ?>')).attr('src', src);

			<?php
		}
		else
		{
			?>
				function log(str) {
			<?php
			if (isset($this->objDataClient->debug) && $this->objDataClient->debug)
			{
				?>
						console.log(str);
			<?php } ?>
				}
				// Вывод на екран
				function toast(str) {
			<?php
			if (isset($this->objDataClient->debug) && $this->objDataClient->debug)
			{
				?>
						$().toastmessage('showToast', {
							text: str,
							position: 'top-right',
							type: 'notice',
							//sticky: true,
							stayTime: 6000,
							inEffectDuration: 200,
						});
			<?php } ?>
				}
				jQuery(document).ready(function() {
			<?php
			if ($_GET['view'] == 'crop')
			{
				?>

						var jcrop_api;
						jQuery(function($) {
							$('#target').Jcrop({
								onChange: showCoords,
								onSelect: showCoords,
								onRelease: clearCoords,
								bgOpacity: 0.5,
								bgColor: 'white',
								addClass: 'jcrop-light'
							}, function() {
								jcrop_api = this;
								$('.jcrop-holder').css('height', $('.jcrop-holder').height() + 2 + 'px');
								$('.jcrop-holder').css('width', $('.jcrop-holder').width() + 2 + 'px');
								//jcrop_api.setSelect([0, 0, $('#target').width(), $('#target').height()]);
								//jcrop_api.setOptions({bgFade: true});
								//jcrop_api.ui.selection.addClass('jcrop-selection');
							});
							// Встановлення розмірів і положення через введення даних в інпути
							$('.U_inline_labels input').on('change', function(e) {
								var x1 = $('#x1').val(), y1 = $('#y1').val(), h = $('#h').val(), w = $('#w').val();
								jcrop_api.setSelect([x1, y1, x1 * 1 + w * 1, y1 * 1 + h * 1]);
							});
							// Simple event handler, called from onChange and onSelect
							// event handlers, as per the Jcrop invocation above
							function showCoords(c)
							{
								$('#x1').val(c.x);
								$('#y1').val(c.y);
								$('#x2').val(c.x2);
								$('#y2').val(c.y2);
								$('#w').val(c.w);
								$('#h').val(c.h);
							}
						});
						//jQuery('.jcrop-tracker').css('height', jQuery('.jcrop-tracker').height() - 5 + 'px');
			<?php } ?>

					jQuery(function($) {
						$('#U_watermarked').Watermarker({
							watermark_img: '<?php echo Yii::app()->createUrl($this->objDataClient->editorOptions->watermark_img) ?>',
							opacity: 1,
							opacitySlider: $('#U_sliderdiv'),
							position: 'topleft',
							onChange: showCoords
						});
						function showCoords(c)
						{
							$('#x1').val(c.x);
							$('#y1').val(c.y);
							$('#w').val(c.w);
							$('#h').val(c.h);
							$('#a').val(c.opacity);
						}
					});


					function clearCoords()
					{
						$('#U_coords input').val('');
					}

					/**
					 * Робота з закладками
					 */
					{
						// Перехід на сторінку кропа
						jQuery('#button_crop').on('click', function() {
							jQuery('#U_coords')
									.attr('action', '<?php
			echo Yii::app()->controller->createUrl('index', array(
				'view' => 'crop' ));
			?>')
									.submit();
						});


						// Перехід на сторінку ватермарка
						jQuery('#button_watermark').on('click', function() {
							jQuery('#U_coords')
									.attr('action', '<?php
			echo Yii::app()->controller->createUrl('index', array(
				'view' => 'watermark' ));
			?>')
									.submit();
						});
					}



					// Відправка форми для збереження
					jQuery('#button_save').on('click', function() {
						$('<input/>')
								.attr({name: 'elemaneChange', value: '<?php echo self::SAVE ?>'})
								.hide()
								.appendTo('#U_coords');
						jQuery('#U_coords').submit();
					});

					// збереження і закриття фрейма
					jQuery('#button_save_close').on('click', function() {
						$('<input/>')
								.attr({name: 'elemaneChange', value: '<?php echo self::SAVE_CLOSE ?>'})
								.hide()
								.appendTo('#U_coords');
						jQuery('#U_coords').submit();
					});

					// Відправка форми для закриття фрейма
					jQuery('.U_popup_close_editorImage').on('click', function() {
						$('<input/>')
								.attr({name: 'elemaneChange', value: '<?php echo self::CLOSE ?>'})
								.hide()
								.appendTo('#U_coords');
						jQuery('#U_coords').submit();
					});

					// Відправка форми для створення зображення
					jQuery('#button_create').on('click', function() {

						$('<input/>')
								.attr({name: 'elemaneChange', value: '<?php echo $_GET['view'] ?>'})
								.hide()
								.appendTo('#U_coords');

						jQuery('#U_coords').submit();
					});

					// Вибір елемента з списка історії
					jQuery('.U_list_button_restore').on('click', function() {
						var name = $(this).attr('name');
						jQuery('<input/>')
								.attr({name: 'elemaneChange', value: JSON.stringify({flag: 'restore', nameFile: name})})
								.hide()
								.appendTo('#U_coords');
						jQuery('#U_coords').submit();
					});

					// Видалення елемента з списка історії
					jQuery('.U_list_button_delete').on('click', function() {
						var name = $(this).attr('name');
						jQuery('<input/>')
								.attr({name: 'elemaneChange', value: JSON.stringify({flag: 'delete', nameFile: name})})
								.hide()
								.appendTo('#U_coords');
						jQuery('#U_coords').submit();
					});





					// Встановлення висоти внутрішньо вікна фрейма
					jQuery('.U_wrap_overflow_editorImage')
							.css('max-height', parent.$('#iframeFileUploader').height() - 130 + 'px');


					// Встановлення висоти списка історії
					jQuery(function($) {
						var editorImage = parseInt(jQuery('.U_wrap_overflow_editorImage').css('max-height')),
								list_history = jQuery('.E_list_history').height();
						if (editorImage < list_history) {
							jQuery('.E_list_history').css('max-height', editorImage - 5 + 'px');
						}

					});
				});
			<?php
		}
		$scriptCrop_FileUploader = ob_get_contents();
		ob_end_clean();
		if (0)

			?></script><?php
		Yii::app()->clientScript->registerScript('scriptCropFileUploader', "$scriptCrop_FileUploader");
	}

	private function setHtmlOptions()
	{
		#/** @DUMPER */ varDumperCasper::dump($this->objDataClient, array( 'but' )); /**/
		$htmlOptions = array(
			################  BUTTONS  #################
			'div_button_editor'				 => array(
				'class' => 'U_div_button',
			),
			'div_button_group_editor'		 => array(
				'class' => 'btn-group btn-group-sm pull-left',
			),
			'div_button_group_link_editor'	 => array(
				'class' => 'btn-group btn-group-sm pull-right',
			),
			'button_create'					 => array(
				'id'	 => 'button_create',
				'class'	 => 'btn btn-primary',
				'value'	 => 'Создать',
			),
			'button_save'					 => array(
				'id'	 => 'button_save',
				'class'	 => 'btn btn-primary',
				'value'	 => 'Сохранить',
			),
			'button_save_close'				 => array(
				'id'	 => 'button_save_close',
				'class'	 => 'btn btn-primary',
				'value'	 => 'Сохранить/Выйти',
			),
			'button_crop'					 => array(
				'id'	 => 'button_crop',
				'class'	 => ($_GET['view'] === 'crop') ? 'btn btn-default  active' : 'btn btn-default',
				'value'	 => 'Страница CROP',
			),
			'button_watermark'				 => array(
				'id'	 => 'button_watermark',
				'class'	 => ($_GET['view'] === 'watermark') ? 'btn btn-default  active' : 'btn btn-default',
				'value'	 => 'Страница WATER',
			),
			################
			'displayNone'					 => array(
				'style' => "display: none;"
			),
			'form_crop'						 => array(
				'id'	 => 'U_coords',
				'class'	 => 'coords',
			),
			'div_form_crop'					 => array(
				'class' => 'form_crop',
			),
			'div_close_editorImage_crop'	 => array(
				'class' => 'U_popup_close_editorImage',
			),
			'div_inline_labels_crop'		 => array(
				'class' => 'U_inline_labels',
			),
			################ INPUT ################
			'x1'							 => array(
				'id'	 => 'x1',
				'class'	 => 'form-control input-sm',
				'size'	 => 4,
			),
			'x2'							 => array(
				'id'	 => 'x2',
				'class'	 => 'form-control input-sm',
				'size'	 => 4,
			),
			'y1'							 => array(
				'id'	 => 'y1',
				'class'	 => 'form-control input-sm',
				'size'	 => 4,
			),
			'y2'							 => array(
				'id'	 => 'y2',
				'class'	 => 'form-control input-sm',
				'size'	 => 4,
			),
			'h'								 => array(
				'id'	 => 'h',
				'class'	 => 'form-control input-sm',
				'size'	 => 4,
			),
			'w'								 => array(
				'id'	 => 'w',
				'class'	 => 'form-control input-sm',
				'size'	 => 4,
			),
			'a'								 => array(
				'id'		 => 'a',
				'class'		 => 'form-control input-sm',
				'size'		 => 4,
				'readonly'	 => 1,
			),
			'div_list_image_crop'			 => array(
				'class' => 'list_hist_image_crop',
			),
			'div_list_history'				 => array(
				'class' => 'E_list_history btn-group-vertical',
			),
			'wrap_overflow_editorImage_crop' => array(
				'class' => 'U_wrap_overflow_editorImage',
			),
			'img'							 => array(
				'id' => 'target',
			),
			'div_list_button'				 => array(
				'class' => 'U_div_list_button',
			),
			'list_button_restore'			 => array(
				'class'	 => 'U_list_button_restore btn-sm btn btn-default',
				'title'	 => 'Выбрать',
			),
			'list_button_delete'			 => array(
				'class'	 => 'U_list_button_delete',
				'title'	 => 'Удалить',
			),
			'watermarked'					 => array(
				'id' => 'U_watermarked',
			),
		);
		if (!empty($this->objDataClient->editorOptions->htmlOptions)
			&& is_array($this->objDataClient->editorOptions->htmlOptions))
		{
			foreach($htmlOptions as $k => $v)
			{
				if (array_key_exists($k, $this->objDataClient->editorOptions->htmlOptions))
				{
					$res[$k] = array_merge($v, $this->objDataClient->editorOptions->htmlOptions[$k]);
				}
				else
				{
					$res[$k] = $v;
				}
			}
			$htmlOptions = $res;
		}
		$this->htmlOptions = $htmlOptions;
	}

	/**
	 * перевіряє на існування і перетворює дані передані клієнським віджетом
	 */
	private function decodePostData()
	{
		#/** @DUMPER */ varDumperCasper::dump($_SERVER, array ( 'but' )); /**/
		#/**/echo"<pre class='prt'>> "; print_r($_POST); echo"</pre>"; /**/die;
		if (!empty($_POST['dataClient']))
		{
			$this->dataClient	 = $_POST['dataClient'];
			$this->objDataClient = json_decode(base64_decode($_POST['dataClient']));

			if (!empty($_POST['listNameFilesStore']))
				$this->listNameFilesStore = json_decode($_POST['listNameFilesStore']);
			else
				throw new Exception('No $_POST[listNameFilesStore]');
			#/**/echo"<pre class='prt'>> "; print_r($this->objDataClient); echo"</pre>"; /**/
		}
		else
		{
			$this->redirect(Yii::app()->controller->createUrl('demo'));
		}

		if (!empty($_POST['objFileData']))
		{
			$this->objFileData = json_decode(base64_decode($_POST['objFileData']));
			#echo"<pre class='casper'>> ";print_r($this->objFileData);echo"</pre>";
		}

		if (!empty($_POST['listHistory']))
		{
			$this->listHistory = json_decode(base64_decode($_POST['listHistory']), true);
			#/**/echo"<pre class='prt'>> "; print_r($this->objFileData); echo"</pre>"; /**/
		}
	}

	/**
	 * Обрізання зображення
	 */
	private function EditorCrop()
	{
		if (!empty($_POST['elemaneChange']) && $_POST['elemaneChange'] == 'crop' && $this->objFileData)
		{
			// Підключення "Поведение"
			$this->attachBehavior('EditorCrop',
				array(
				'class'			 => 'EditorCropBehavior',
				'objFileData'	 => $this->objFileData,
				'cropData'		 => (isset($_POST['coordinates'])) ? $_POST['coordinates'] : '',
			));
			$this->initEditorCrop();
			$this->objFileData = $this->getFileDataCrop();
		}
	}

	/**
	 * Створення водяного знака зображення
	 */
	private function EditorWatermark()
	{
		if (isset($_POST['elemaneChange']) && $_POST['elemaneChange'] == 'watermark' && $this->objFileData)
		{
			// Підключення "Поведение"
			$this->attachBehavior('EditorWatermark',
				array(
				'class'			 => 'EditorWatermarkBehavior',
				'objFileData'	 => $this->objFileData,
				'objDataClient'	 => $this->objDataClient,
				'watermarkData'	 => (isset($_POST['coordinates'])) ? $_POST['coordinates'] : '',
			));
			$this->initEditorWatermark();
			$this->objFileData = $this->getFileDataWatermark();
		}
	}

	/**
	 * Заміна відредагованого зображення і видалення мусора
	 */
	private function EditorReplacement()
	{
		if (
			isset($_POST['elemaneChange'])
			&& stristr($_POST['elemaneChange'], self::SAVE)
			&& $this->objFileData
		)
		{
			// Підключення "Поведение"
			$this->attachBehavior('EditorReplacement',
				array(
				'class'			 => 'EditorReplacementBehavior',
				'objFileData'	 => $this->objFileData,
				'objDataClient'	 => $this->objDataClient,
			));
			$this->initEditorReplacement();
			$this->objFileData = false;
		}
	}

	/**
	 * Керування списком історії
	 */
	private function MenagerElementListHistory()
	{
		if (!empty($_POST['elemaneChange']) && $this->objFileData)
		{
			#/**/echo"<pre class='prt'>> "; print_r($_POST); echo"</pre>"; /**/
			// Підключення "Поведение"
			$this->attachBehavior('EditorHistoryMenager',
				array(
				'class'			 => 'EditorHistoryMenagerBehavior',
				'objFileData'	 => $this->objFileData,
				'listHistory'	 => $this->listHistory,
				'elemaneChange'	 => json_decode($_POST['elemaneChange'], true),
			));
			$this->initEditorHistoryMenager();
			$this->objFileData = $this->getFileDataHistoryMenager();
			#echo"<pre class='casper'>> ";print_r($this->objFileData);echo"</pre>";
		}
	}

	/**
	 *
	 */
	private function EditorOperationTempDir()
	{
		if ($this->listNameFilesStore)
		{
			// Підключення "Поведение"
			$this->attachBehavior('EditorOperationTempDir',
				array(
				'class'				 => 'EditorOperationTempDirBehavior',
				'objFileData'		 => $this->objFileData,
				'listNameFilesStore' => $this->listNameFilesStore,
				'elemaneChange'		 => (isset($_POST['elemaneChange'])) ? $_POST['elemaneChange'] : '',
			));
			$this->initEditorOperationTempDir();
			$this->objFileData	 = $this->getFileData();
			$this->listHistory	 = $this->getListHistory();
		}
	}

	/**
	 * ПОДІЯ
	 * Демо варіанти використання розширення
	 */
	public function actionDemo($view = 'demo')
	{

		$this->layout = 'ext.' . basename(dirname(__FILE__)) . '.views.demo.layouts';
		$this->render('demo/' . $view);
	}

}