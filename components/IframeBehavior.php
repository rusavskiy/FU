<?php

/**
 * IframeBehavior class file.
 * Передає постом дані в редактор і відображає вікно редактора в фреймі
 * @author Rusavskiy Vitaliy <rusavskiy@gmail.com>
 * @version 1.0.0
 *
 *
 * echo CHtml::link('Создать', $this->createUrl('user/create/iframe'), array( 'class' => 'create open_iframe', 'onClick' => 'return false;', 'data-parametrs' => '{"src":"'.$this->createUrl('user/create/iframe').'","name":"create","width":"500","height":"500"}' ))
 */
class IframeBehavior extends CBehavior
{
	/* Унікальний індифікатор */

	public $htmlID = '';

	/*
	 * Дані, що передаються з контроллера якій підключає Behavior і які будуть передані в фрейм постом.
	 * Де key буде $_POST[key] => val.
	 */
	public $dataPost = array();

	/*
	 * Альтернативний варіант $dataPost.
	 * Використовувати коли необхідно щоб IframeBehavior вибрав і передав дані які містяться в DOM дереві сторінки.
	 * Задається array(key => id), де key - буде назвою в пості, а id - це індифікатор по якому вибиратимуться дані з DOM.
	 */
	public $htmlDataPost = array();

	/*
	 * Індифікатор елемента DOM дерева клікнувши по якому відкривається редактор
	 * Якщо дане значення не передавать то по замовчуванню буде встановлено 'action'+htmlID
	 */
	public $idActionOpenIframe	 = '';
	public $name				 = 'editor_Iframe';

	/* Адрес форми відправки */
	public $src						 = '';
	public $width					 = "1600";
	public $height					 = "1200";
	public $scrolling				 = "no";
	private $dataHtmlForm			 = '';
	private $dataHtmlObj			 = '{}';
	/*
	 * html фрейма, генериться лише один раз
	 */
	static private $dataHtmlIframe	 = '';

	/**
	 * Ініціалізація
	 */
	public function initIframeBehavior()
	{
		$this->initialization();
		$this->generetionForm();
		$this->generetionHtml();
		$this->scripts();
	}

	// Ініціалізація властивостей по default
	private function initialization()
	{
		if (!$this->htmlID)
			$this->htmlID = mt_rand();

		if (!$this->idActionOpenIframe)
			$this->idActionOpenIframe = 'action' . $this->htmlID;

		if (!$this->src)
			throw new Exception('No src!!!');
	}

	/**
	 * Скрипти
	 * Виконують додавання фрейма в початок body
	 * Створення форми для відправки даних в фрейм
	 */
	private function scripts()
	{
		if (0)
		{
			?><script><?php
		}
		ob_start();
		?>
			jQuery(document).ready(function() {
				var htmlDataPost = <?php echo $this->dataHtmlObj; ?>;

				// додавання фрейма в початок body
				if (!$('#iframeFileUploader').length)
					jQuery('<?php echo self::$dataHtmlIframe ?>').prependTo('body');

				jQuery('#iframeFileUploader').empty();

				// обработчик нажатия ссылки 'редактировать'
				jQuery('#<?php echo $this->idActionOpenIframe ?>').on('click', function() {

					// Настройка модального вікна
					jQuery('#iframeModal').modal({
						backdrop: 'static', // boolean or the string 'static'
						keyboard: true, //boolean
						show: false, //boolean
						remote: true
					});

					// Очистка минулої форми, створеннянової і її відправка в фрейм
					jQuery('#formIfraime').remove();
					jQuery('<?php echo $this->dataHtmlForm ?>').appendTo('body');
					for (var key in htmlDataPost) {
						if ($('#' + htmlDataPost[key]).text()) {
							$('<textarea/>')
									.attr('name', key)
									.text($('#' + htmlDataPost[key]).text())
									.appendTo('#formIfraime');
						}
					}
					// відправка форми в фрейм
					jQuery('#formIfraime').submit();






					var window_height = jQuery(window).height();
					var window_width = jQuery(window).width();
					//console.log(window_height);console.log(window_width);

					// Розрахунок висоти
					var height = window_height * 0.9;
					if (height > <?php echo $this->height ?>) {
						height = <?php echo $this->height ?>;
					}
					// Розрахунок ширини
					var width = window_width * 0.8;
					if (width > <?php echo $this->width ?>) {
						width = <?php echo $this->width ?>;
					}

					jQuery('#iframeModal .modal-dialog')
							.css('width', width + 'px')
							.css('height', height + 'px');

					// Встановлення параметрів фрейму
					jQuery('#iframeFileUploader')
							.attr('name', '<?php echo $this->name ?>')
							.css('width', width - 35 + 'px')
							.css('height', height - 35 + 'px')
							.attr('scrolling', '<?php echo $this->scrolling ?>')
							.show()
							.load(function() {
								jQuery('#iframeModal').modal('show');
							});

					// Закриття фрейму
					/*jQuery('.backgroundFileUploader,.windowWrapFileUploader').click(function() {
					 //$(parent.top.document.getElementById('iframe_div')).hide();
					 });*/
				});
			});
		<?php
		if (0)
		{
			?></script><?php
		}
		$Scripts = ob_get_contents();
		ob_end_clean();
		Yii::app()->clientScript->registerScript('Scripts_Iframe_FileUploader_' . $this->htmlID, "$Scripts");
	}

	/* Генерація форми відправки даних в фрейм */
	private function generetionForm()
	{
		ob_start();
		echo CHtml::form($this->src, "POST",
			array(
			'id'	 => 'formIfraime',
			'target' => $this->name,
			'style'	 => 'display:none'
		));
		{
			if (is_array($this->dataPost) && $this->dataPost)
			{
				foreach($this->dataPost as $k => $v)
				{
					if ($k && $v)
					{
						echo CHtml::textArea($k, $v);
					}
				}
			}
		}echo CHtml::endForm();
		$this->dataHtmlForm = ob_get_contents();
		ob_end_clean();

		/* Створення об'єкта для передачі в JS */
		if ($this->htmlDataPost && is_array($this->htmlDataPost))
		{
			$this->dataHtmlObj = '{';
			foreach($this->htmlDataPost as $k => $v)
			{
				if ($k && $v)
				{
					$this->dataHtmlObj .= $k . ":'" . $v . "'";
				}
			}
			$this->dataHtmlObj .= '}';
		}
	}

	/* Створення html розмітки для роботи фрейма */
	private function generetionHtml()
	{
		// Генерація фрейм
		if (!self::$dataHtmlIframe)
		{
			ob_start();
			?>
			<!-- Modal -->
			<div class="modal fade" id="iframeModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<iframe id ="iframeFileUploader" name = "<?php echo $this->name ?>"></iframe>
					</div><!-- /.modal-content -->
				</div><!-- /.modal-dialog -->
			</div><!-- /.modal -->
			<?php
			self::$dataHtmlIframe = preg_replace(array( '{\s+}', '{^\s+}' ), array( ' ', '' ), ob_get_contents());
			ob_end_clean();
		}
	}

	/**
	 * Вертає значення ID для кнопки редагування
	 * @return type string
	 */
	public function getIDButtonEditor()
	{
		return $this->idActionOpenIframe;
	}

	public function getHtmlID()
	{
		return $this->htmlID;
	}

	/**
	 * Скрипти исполняемые в фрейме
	 */
//	static function scriptIframe()
//	{
//		if (isset($_GET['iframe']) && $_GET['iframe'] == Iframe::IFRAME_URL_EXIT)
//		{
//			Yii::app()->clientScript->registerScript('Iframe2',
//				"$(parent.top.document.getElementsByClassName('background')[0]).click();");
//		}
//
//		Yii::app()->clientScript->registerScript('scriptIframe',
//			"
//			    //console.log(parent.$('#iframeFileUploader').height());
//			    //$('.wrap_overflow_editorImage').css('max-height', parent.$('#iframeFileUploader').height()-30 + 'px');
//			    $('.wrap_overflow_editorImage').css('max-height', parent.$('#iframeFileUploader').height()-50 + 'px');
//	        ");
//	}
}