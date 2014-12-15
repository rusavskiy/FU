if (CSPR === undefined)
	var CSPR = {};

CSPR.EditorImg = function(options_client) {
	var obj = {},
		  /** Вывод в консоль в режимі відладки * @param {type} str*/
		  log = function(str) {
			  if (obj.flag.debug) {
				  console.log(str);
			  }
		  },
		  /** Вывод на екран в режимі відладки * @param {type} str*/
		  toast = function(str) {
			  if (obj.flag.debug) {
				  $().toastmessage('showToast', {
					  text: str,
					  position: 'top-right',
					  type: 'notice',
					  //sticky: true,
					  stayTime: 6000,
					  inEffectDuration: 200
				  });
			  }
		  },
		  /** Вывод на екран ошибки в режимі відладки * @param {type} str */
		  error_debug = function(str) {
			  if (obj.flag.debug) {
				  $().toastmessage('showToast', {
					  text: str,
					  position: 'top-right',
					  type: 'error',
					  sticky: true,
					  //stayTime: 60000,
					  inEffectDuration: 200
				  });
			  }
		  },
		  /** Вывод на екран ошибки для користувача * @param {type} str */
		  error = function(str) {
			  $().toastmessage('showToast', {
				  text: str,
				  position: 'top-right',
				  type: 'error',
				  sticky: false,
				  stayTime: 5000,
				  inEffectDuration: 200
			  });
		  },
		  /** Вывод на екран підтвердження для користувача * @param {type} str  */
		  success = function(str) {
			  $().toastmessage('showToast', {
				  text: str,
				  position: 'top-right',
				  type: 'success',
				  sticky: false,
				  inEffectDuration: 200
			  });
		  },
		  /** Злиття і встановлення властивостей об'єкта опцій*/
		  mergersProperties = (function() {
			  $.extend(obj, options_client);
			  if (obj.objJQuery) {
				  for (var val in obj.objJQuery) {
					  obj[val] = $(obj.objJQuery[val]);
				  }
			  }
		  }()),
		  clearCoords = function()
		  {
			  $('#U_coords input').val('');
		  };

	return {
		/* Скрипт виконання коду при закриті фрейма */
		ClosedIfraim: function() {
			$(parent.top.document.getElementById('iframeDivFileUploader')).hide();
			// Оновлення зображення
			var src = parent.$('#file_<?php echo $this->objDataClient->htmlID ?>').attr('src');
			src = src + (src.match(/\?/ig) === null ? "?" : "&") + new Date().getTime();
			$(parent.top.document.getElementById('file_<?php echo $this->objDataClient->htmlID ?>')).attr('src', src);
		},
		/* Подія обрізання фото */
		EventCrop: function() {
			var jcrop_api;
			jQuery(document).ready(function() {
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
		},
		/* Подія створення ватермарка */
		EventWatermark: function() {
			jQuery(document).ready(function() {
				$('#U_watermarked').Watermarker({
					watermark_img: obj.watermark_img,
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
		},
		EventsOnAllPages: function() {
			/**obj = {
			 */
			/* Встановлення висоти списка історії */
			var SetHeightListHistory = function() {
				var editorImage = parseInt(jQuery('.U_wrap_overflow_editorImage').css('max-height')),
					  list_history = jQuery('.U_list_history').height();
				if (editorImage < list_history) {
					jQuery('.U_list_history').css('max-height', editorImage - 5 + 'px');
				}
			};
			$(document).ready(function() {
//				$('#body_iframe button.close').on('click', function() {
//					parent.$('#iframeModal').modal('hide');
//				});
				SetHeightListHistory();
				/**
				 * Робота з закладками
				 */
				{
					// Перехід на сторінку кропа
					jQuery('#button_crop').on('click', function() {
						jQuery('#U_coords').attr('action', obj.actionFormCrop).submit();
					});
					// Перехід на сторінку ватермарка
					jQuery('#button_watermark').on('click', function() {
						jQuery('#U_coords').attr('action', obj.actionFormWatermark).submit();
					});
				}

				// Відправка форми для збереження
				jQuery('#button_save').on('click', function() {
					$('<input/>')
						  .attr({name: 'elemaneChange', value: obj.elemaneChangeSAVE})
						  .hide()
						  .appendTo('#U_coords');
					jQuery('#U_coords').submit();
				});
				// збереження і закриття фрейма
				jQuery('#button_save_close').on('click', function() {
					$('<input/>')
						  .attr({name: 'elemaneChange', value: obj.elemaneChangeSAVE_CLOSE})
						  .hide()
						  .appendTo('#U_coords');
					jQuery('#U_coords').submit();
				});
				// Відправка форми для закриття фрейма
				jQuery('.U_popup_close_editorImage').on('click', function() {
					$('<input/>')
						  .attr({name: 'elemaneChange', value: obj.elemaneChangeCLOSE})
						  .hide()
						  .appendTo('#U_coords');
					jQuery('#U_coords').submit();
				});
				// Відправка форми для створення зображення
				jQuery('#button_create').on('click', function() {
					$('<input/>')
						  .attr({name: 'elemaneChange', value: obj.elemaneChangeCREATE})
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
					  .css('max-height', parent.$('#iframeFileUploader').height() - 100 + 'px');
			});
		}
	};
};