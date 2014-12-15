<?php
echo CHtml::openTag('div', $html['div_general_blok']);
{

	echo CHtml::openTag('div', $html['div_dropBox']);
	{
		echo CHtml::openTag('div', $html['div_container']);
		{

			?>
				<ul class="ace-thumbnails">
					<li>
						<a href="<?php echo Yii::app()->baseUrl; ?>/images/thumb-2.jpg" data-rel="colorbox" class="cboxElement">
							<img alt="150x150" src="<?php echo Yii::app()->baseUrl; ?>/images/thumb-2.jpg">
						</a>

						<div class="tools tools-right">
							<a href="#">
								<i class="icon-link"></i>
							</a>

							<a href="#">
								<i class="icon-paper-clip"></i>
							</a>

							<a href="#">
								<i class="icon-pencil"></i>
							</a>

							<a href="#">
								<i class="icon-remove red"></i>
							</a>
						</div>
					</li>
					<li>
						<a href="<?php echo Yii::app()->baseUrl; ?>/images/thumb-6.jpg" data-rel="colorbox" class="cboxElement">
							<img alt="150x150" src="<?php echo Yii::app()->baseUrl; ?>/images/thumb-6.jpg">
						</a>

						<div class="tools">
							<a href="#">
								<i class="icon-link"></i>
							</a>

							<a href="#">
								<i class="icon-paper-clip"></i>
							</a>

							<a href="#">
								<i class="icon-pencil"></i>
							</a>

							<a href="#">
								<i class="icon-remove red"></i>
							</a>
						</div>
					</li>
					<li>
						<a href="<?php echo Yii::app()->baseUrl; ?>/images/thumb-2.jpg" data-rel="colorbox" class="cboxElement">
							<img alt="150x150" src="<?php echo Yii::app()->baseUrl; ?>/images/thumb-2.jpg">
						</a>

						<div class="tools tools-top">
							<a href="#">
								<i class="icon-link"></i>
							</a>

							<a href="#">
								<i class="icon-paper-clip"></i>
							</a>

							<a href="#">
								<i class="icon-pencil"></i>
							</a>

							<a href="#">
								<i class="icon-remove red"></i>
							</a>
						</div>
					</li>
					<li>
						<a href="<?php echo Yii::app()->baseUrl; ?>/images/thumb-2.jpg" data-rel="colorbox" class="cboxElement">
							<img alt="150x150" src="<?php echo Yii::app()->baseUrl; ?>/images/thumb-2.jpg">
						</a>

						<div class="tools tools-top">
							<a href="#">
								<i class="icon-link"></i>
							</a>

							<a href="#">
								<i class="icon-paper-clip"></i>
							</a>

							<a href="#">
								<i class="icon-pencil"></i>
							</a>

							<a href="#">
								<i class="icon-remove red"></i>
							</a>
						</div>
					</li>
					<li>
						<a href="<?php echo Yii::app()->baseUrl; ?>/images/thumb-2.jpg" data-rel="colorbox" class="cboxElement">
							<img alt="150x150" src="<?php echo Yii::app()->baseUrl; ?>/images/thumb-2.jpg">
						</a>

						<div class="tools tools-top">
							<a href="#">
								<i class="icon-link"></i>
							</a>

							<a href="#">
								<i class="icon-paper-clip"></i>
							</a>

							<a href="#">
								<i class="icon-pencil"></i>
							</a>

							<a href="#">
								<i class="icon-remove red"></i>
							</a>
						</div>
					</li>
					<li>
						<a href="<?php echo Yii::app()->baseUrl; ?>/images/thumb-2.jpg" data-rel="colorbox" class="cboxElement">
							<img alt="150x150" src="<?php echo Yii::app()->baseUrl; ?>/images/thumb-2.jpg">
						</a>

						<div class="tools tools-top">
							<a href="#">
								<i class="icon-link"></i>
							</a>

							<a href="#">
								<i class="icon-paper-clip"></i>
							</a>

							<a href="#">
								<i class="icon-pencil"></i>
							</a>

							<a href="#">
								<i class="icon-remove red"></i>
							</a>
						</div>
					</li>
				</ul>
			<?php
		}
		echo CHtml::closeTag('div');
	}
	echo CHtml::closeTag('div');

	// Ім*я непотрібне, воно підставляється джава-скриптом при завантаженні файла
	echo CHtml::fileField('', '', $html['input_file']);

	// Кнопки
	echo CHtml::openTag('div', $html['div_block_buttons']);
	{
		echo CHtml::tag('div', $html['button_upload'], CHtml::button('button', $html['button_upload_input']));
		echo CHtml::tag('div', $html['button_edit'], CHtml::button('button', $html['button_edit_input']));
		echo CHtml::tag('div', $html['button_delete'], CHtml::button('button', $html['button_delete_input']));
	}
	echo CHtml::closeTag('div');
}
echo CHtml::closeTag('div');
