var CSPR = {};
CSPR.objListRecordNameFileSaveDB = {}; // Список файлів, які збережені на сервері і в БД.
CSPR.objListRecordNameFileLoadedLast = {}; // Список файлів, які були завантажені останіми і які не потрібно видалять
CSPR.UploaderFile = function(options_client) {
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
				  stayTime: 10000,
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
		  /** Обробка даних, які повернув сервер, в разі наявності помилки чи повідомлення - виведення на екран * @param {type} data */
		  operationResultAjax = function(data) {
			  if (data) {
				  // Якщо сервер повертає помилку
				  if (data.status === 0) {
					  // виведення повідомлення в режимі відладка
					  if (data.message_debug) {
						  error_debug(data.message_debug);
					  }
					  // вивід повідомлення для користувача
					  if (data.message) {
						  error(data.message);
					  }
					  return false;
				  } else {
					  if (data.message_debug) {
						  success(data.message_debug);
					  }
					  if (data.message) {
						  success(data.message);
					  }
				  }
				  return true;
			  } else {
				  return false;
			  }
		  },
		  /** Злиття і встановлення властивостей об'єкта опцій*/
		  mergersProperties = (function() {
			  $.extend(obj, options_client);
			  if (obj.objJQuery) {
				  for (var val in obj.objJQuery) {
					  obj[val] = $(obj.objJQuery[val]);
				  }
			  }
		  }());

	return {
		UploadOneFile: function() {
			/**obj = {
			 debug:		'$this->debug',
			 dataClient: '$this->dataClient',
			 isImage:	'$this->isImage',
			 fancybox:	'$this->fancyboxGallery',
			 max_size:	'$this->max_size_upload_file',
			 imgSize:	0,
			 urlDeleteFile:   '" . Yii::app()->createUrl(ServerLogicController::NAME_CONTROLLER_URL . '/deleteFile') . "',
			 upload: {
			 url:			'" . Yii::app()->createUrl(ServerLogicController::NAME_CONTROLLER_URL) . "',
			 fieldName:		'$this->inputNameFile'
			 },
			 objJQury: {
			 dropBox:			'#{$this->htmlOptions['div_dropBox']['id']}',
			 fileInput:			'#{$this->htmlOptions['input_file']['id']}',
			 recordName:			'#{$this->htmlOptions['inputRecordName']['id']}',
			 linkFile:			'#{$this->htmlOptions['link']['id']}',
			 id_listNameFilesStore: '#{$this->htmlOptions['listNameFilesStore']['id']}',
			 nameFile:			'#{$this->htmlOptions['div_name_file']['id']}',
			 textBox:			'#{$this->htmlOptions['div_textCleanBox']['id']}',
			 buttonUpload:		'#{$this->htmlOptions['button_upload_input']['id']}',
			 buttonEdit:			'#{$this->htmlOptions['button_edit']['id']}',
			 buttonDelete:		'#{$this->htmlOptions['button_delete']['id']}',
			 blokProgress:		'#{$this->htmlOptions['div_blok_progress']['id']}',
			 progressBar:		'#{$this->htmlOptions['div_progress_bar']['id']}',
			 infoSize:			'#{$this->htmlOptions['div_size_file']['id']}',
			 getimagesizeFile:	'#{$this->htmlOptions['div_getimagesize_file']['id']}',
			 fileImg:			'#{$this->htmlOptions['img']['id']}',
			 fancybox_img:		'#{$this->htmlOptions['a_fancybox_img']['id']}',
			 fancybox_elements:	'#{$this->htmlOptions['div_fancybox_elements']['id']}',
			 }
			 }*/
			var
				  /** Встановлення максимально дозволеного розміру для зображення враховуючи блок в якому він знаходиться, і виконання show. */
				  setSizeImgBlok = function() {
					  if (obj.fileImg && obj.fileImg.attr('src') !== undefined) {
						  if (obj.dropBox.width())
							  obj.fileImg.css('max-width', obj.dropBox.width());
						  if (obj.dropBox.height())
							  obj.fileImg.css('max-height', obj.dropBox.height());
						  /** Якщо в зображенні існує адрес, (тобто не оболочка html)*/
						  if (obj.fileImg.attr('src') !== '') {
							  obj.fileImg.show();
						  }
					  }
				  },
				  /** Виведення розміру файла */
				  infoSizeUpdate = function(val) {
					  if (val) {
						  obj.infoSize.text('Размер: ' + val + ' Кб');
					  } else {
						  obj.infoSize.text('');
					  }
				  },
				  /**
				   * При ЗАВАНТАЖЕННІ НОВОГО ФАЙЛУ додається в об'єкт 'значення' під ід (індифікатором поля).
				   * 'Значення' містить список файлів, які завантажені на сервер і збережені в БД
				   * При збереженні статті, даний список використовується для видалення файлів з сервера, тобіш мусора.
				   */
				  setRecordNameFileSaveDB = function() {
					  var name = obj.id_listNameFilesStore.attr('id').replace(/[^\d]+/, '');
					  if (CSPR.objListRecordNameFileSaveDB[name] === undefined)
						  CSPR.objListRecordNameFileSaveDB[name] = obj.id_listNameFilesStore.val();

					  log('objListRecordNameFileSaveDB => ');
					  log(CSPR.objListRecordNameFileSaveDB);
				  },
				  /**
				   * Об'єкт місти спискі файлів що були завантажені останніми і які не потрібно видалять з сервера.
				   */
				  addObjListRecordNameFileLoadedLast = function(data) {
					  var name = obj.recordName.attr('id').replace(/[^\d]+/, '');
					  CSPR.objListRecordNameFileLoadedLast[name] = data;

					  log('objListRecordNameFileLoadedLast => ');
					  log(CSPR.objListRecordNameFileLoadedLast);
				  },
				  /** Очищення полів форми завантаження і встановлення початкових параметрів */
				  allClear = function() {
					  /** остановить все текущие загрузки и удалить все файлы из очереди */
					  obj.fileInput.damnUploader('cancelAll');
					  /** Очищаєм дані progressBar */
					  obj.blokProgress.attr({style: ''});
					  obj.loading.attr({style: ''});
					  obj.progressBar.attr({style: ''}).children().text('0%');
					  //obj.blokProgress.hide();

					  /** start Очищення зображення або файла */
					  obj.fancybox_img.attr({title: '', href: ''}).hide();
					  obj.fancybox_img.nextAll('a').remove();
					  obj.fancybox_elements.empty();
					  obj.fileImg.attr({src: '', alt: ''}).hide();
					  obj.linkFile.attr({href: ''}).parent().hide();
					  /** end Очищення зображення  або файла*/

					  /** Очищення системних полів */
					  obj.recordName.val('');
					  obj.id_listNameFilesStore.text('');
					  /** Очищення системних полів */

					  /** Очищення блока з інфою про файл */
					  //obj.nameFile.parent().hide();
					  infoSizeUpdate('');
					  obj.nameFile.text('');
					  obj.getimagesizeFile.text('');
					  /** Очищення блока з інфою про файл */

					  /** Обробка кнопок */
					  obj.buttonUpload.removeAttr('disabled');
					  obj.buttonEdit.hide();
					  obj.buttonDelete.hide();
					  /** Обробка кнопок */

					  obj.textBox.show();
				  },
				  /** Отображение выбраных файлов, создание миниатюр и ручное добавление в очередь загрузки. * @param {type} file */
				  addFileToQueue = function(file) {
					  var
						    uploadItem = {},
						    /** Перевірка чи не перевищує розмір файлу максимально допустимого * @param {type} size */
						    checkSizeFile = function(size) {
							    if (size > obj.max_size) {
								    var mess = 'Превышение допустимого размера.<br/> Файл - ' + file.name + '<br/> Размером - ' + file.size + ' Kb<br/> Допустимо - ' + obj.max_size + ' Kb';
								    error(mess);
								    return  false;
							    }
						    },
						    /** Налаштування перед завантаженням */
						    settingsBeforeLoading = function() {
							    setRecordNameFileSaveDB();
							    allClear();
							    obj.textBox.hide();
							    /** Вимкнення кнопки під-час завантаження */
							    obj.buttonUpload.attr('disabled', true);
							    obj.blokProgress.show();
						    };

					  /* Перевірка розіміра файла, якщо більший закінчення завантаження */
					  if (checkSizeFile(file.size) === false)
						  return false;

					  settingsBeforeLoading();

					  // Если браузер поддерживает выбор файлов (иначе передается специальный параметр fake,
					  // обозночающий, что переданный параметр на самом деле лишь имитация настоящего File)
					  if (!file.fake) {
						  // Отсеиваем не картинки
						  var imageType = /image.*/;
						  if (file.type.match(imageType)) {
							  toast('Картинка добавлена: `' + file.name + '` (' + Math.round(file.size / 1024) + ' Кб)');
						  } else {
							  toast('Файл добавлен: `' + file.name + '` (тип ' + file.type + ')');
						  }
					  } else {
						  toast('Файл добавлен: ' + file.name);
					  }


					  /** объект загрузки */
					  uploadItem = {
						  file: file,
						  /** Обновление progress bar'а */
						  onProgress: function(percents) {
							  //log(percents);
							  obj.progressBar.css('width', percents + '%').children().text(percents + '%');
							  percents !== 100 || obj.loading.show();
						  },
						  /** Подія при закінченні завантаження зображення */
						  onComplete: function(successfully, data, errorCode) {
							  //return false;
							  //console.log(data);

							  if (data)
								  data = JSON.parse(data);

							  log('\nІнфа яку вертає сервер після завантаження файла:');
							  log(data);

							  if (successfully && data.nameFile) {
								  //log(data);

								  if (!operationResultAjax(data)) {
									  // Очищення полів форми завантаження
									  if (data.code_error) {
										  allClear();
									  }
									  return false;
								  }

								  /** Запис списку файлов які були створені на сервері,
								   * використовуються для фільтрації під час видалення завантаженого мусора  */
								  if (data.listNameFilesStore) {
									  obj.id_listNameFilesStore.text(data.listNameFilesStore);
									  addObjListRecordNameFileLoadedLast(data.listNameFilesStore);
								  }

								  /** Виведення і встановлення в input назви файла */
								  if (data.nameFile) {
									  obj.nameFile.text(data.nameFile);//.show();
									  obj.recordName.val(data.nameFile);
								  }

								  /** Виведення об'єма файлу */
								  if (data.filesize) {
									  infoSizeUpdate(data.filesize);
								  }

								  /** Виведення розмірів зображення */
								  if (data.getimagesize) {
									  obj.getimagesizeFile.text(data.getimagesize);
								  }

								  obj.blokProgress.hide();

								  /** Вставка посилання на файл чи зображення */
								  if (data.listNameFilesStore) {
									  var files = JSON.parse(data.listNameFilesStore), flag = false;
									  //якщо файл має бути зображенням
									  if (obj.flag.isImage) {
										  // Якщо використовується fancybox
										  if (obj.flag.fancybox && obj.fileImg.parent()) {
											  for (var elem in files) {
												  if (elem === 'original')
													  continue;
												  if (flag === false) {
													  flag = true;
													  obj.fileImg
														    .attr('src', data.baseUrl + files[elem])
														    .show()
														    .parent()
														    .attr('href', data.baseUrl + files[elem])
														    .attr('title', data.nameFile)
														    .show();
												  } else {
													  $('<a/>')
														    .attr('href', data.baseUrl + files[elem])
														    .attr('title', files[elem].replace(/^.+\//i, ''))
														    .attr('data-fancybox-group', obj.fileImg.parent().attr('data-fancybox-group'))
														    .addClass('U_fancybox_img')
														    .appendTo(obj.fancybox_elements);
												  }
											  }
										  } else {
											  obj.fileImg
												    .attr('src', data.baseUrl + files.shift())
												    .show();
										  }
										  // якщо використовується редактор показуєм кнопку редагування
										  if (obj.flag.editor) {
											  obj.buttonEdit.show();
										  }
									  } else {
										  obj.linkFile
											    .attr('href', data.baseUrl + files.shift())
											    .parent()
											    .show();
									  }
								  }

								  obj.buttonUpload.removeAttr('disabled');
								  obj.buttonDelete.show();
								  //obj.nameFile.parent().show();

								  toast('Файл `' + this.file.name + '` загружен, полученные данные:' + data);
							  } else {
								  allClear();
								  error('Файл `' + this.file.name + '`: ошибка при загрузке. Код: ' + errorCode);
								  if (!operationResultAjax(data)) {
									  return false;
								  }
							  }
						  }
					  };
					  obj.fileInput.damnUploader('addItem', uploadItem);
					  //return uploadItem;
				  },
				  /** Видалення файлу */
				  deleteFile = function() {
					  if (confirm('Подтверждаете удаления?')) {
						  var arrayDeleteFile = [];
						  arrayDeleteFile.push(obj.id_listNameFilesStore.val());
						  if (arrayDeleteFile.length) {
							  $.ajax({
								  url: obj.urlDeleteFile,
								  type: 'post',
								  data: {arrayDeleteFile: arrayDeleteFile},
								  dataType: 'json',
								  async: true,
								  cache: false,
								  ifModified: false,
								  processData: true,
								  success: function(data) {
									  allClear();
									  if (!operationResultAjax(data)) {
										  return false;
									  }
								  }
							  });
						  }
					  }
					  return false;
				  },
				  /** Проверка поддержки File API, FormData и FileReader */
				  checkingFileAPISupport = function() {
					  toast(' Проверка поддержки ');
					  if (!$.support.fileSelecting) {
						  toast('Ваш браузер не поддерживает выбор файлов (загрузка будет осуществлена обычной отправкой формы)');
					  } else {
						  if (!$.support.fileReading) {
							  toast('* Ваш браузер не умеет читать содержимое файлов (миниатюрки не будут показаны)');
						  }
						  if (!$.support.uploadControl) {
							  toast('* Ваш браузер не умеет следить за процессом загрузки (progressbar не работает)');
						  }
						  if (!$.support.fileSending) {
							  toast('* Ваш браузер не поддерживает объект FormData (отправка с ручной формировкой запроса)');
						  }
						  toast('Выбор файлов поддерживается\n\r');
					  }
				  },
				  /** Обработка событий drag and drop при перетаскивании файлов на элемент dropBox */
				  eventHandlingDragAndDrop = function() {
					  obj.dropBox.bind({
						  dragenter: function() {
							  //$(this).addClass('highlighted');
							  return false;
						  },
						  dragover: function() {
							  return false;
						  },
						  dragleave: function() {
							  //$(this).removeClass('highlighted');
							  return false;
						  }
					  });
				  };

			$(document).ready(function() {
				setSizeImgBlok();
				/** Подключаем и настраиваем плагин загрузки */
				if (typeof obj.fileInput.damnUploader === 'function') {
					obj.fileInput.damnUploader({
						url: obj.upload.url, // куда отправлять
						dataClient: obj.dataClient,
						// имитация имени поля с файлом (будет ключом в $_FILES, если используется PHP)
						fieldName: obj.upload.fieldName,
						multiple: false,
						// дополнительно: элемент, на который можно перетащить файлы (либо объект jQuery, либо селектор)
						dropBox: obj.dropBox,
						// максимальное кол-во выбранных файлов (если не указано - без ограничений)
						limit: 1,
						limitAdditionComplete: true,
						// когда максимальное кол-во достигнуто (вызывается при каждой попытке добавить еще файлы)
						onLimitExceeded: function() {
							toast('Допустимое кол-во файлов уже выбрано');
							return false;
						},
						// ручная обработка события выбора файла (в случае, если выбрано несколько, будет вызвано для каждого) если обработчик возвращает true, файлы добавляются в очередь автоматически
						onSelect: function(file) {
							addFileToQueue(file);
							return false;
						},
						// когда все выбраные файлы добавленые в очередь загрузки
						onAdditionComplete: function() {
							toast(' все выбраные файлы добавленые в очередь загрузки  \n\r');
							// Загрузка файла
							toast('\n\r Загрузка \n\r');
							obj.fileInput.damnUploader('startUpload');
							return false;
						},
						// когда все загружены
						onAllComplete: function(self) {
							//log(self);
							toast('\n\r Все загрузки завершены! \n\r');
							//imgSize = 0;
							//infoSizeUpdate();
							return false;
						}
					});
				} else {
					error_debug('НЕ СУЩЕСТВУЕТ damnUploader !!!');
				}
				/** ==================================================
				 *			Обработчики событий
				 ===================================================*/
				{
					checkingFileAPISupport();
					eventHandlingDragAndDrop();
					// Обаботка события нажатия на кнопку "Загрузить".
					//$('#upload_<?php echo $htmlID ?>,#dropBox_<?php echo $htmlID ?>').on('click', function() {
					obj.buttonUpload.on('click', function() {
						obj.fileInput.click();
					});
					// Обработка события выбор файла
					// НЕ ВИКОРИСТОВУЄТЬСЯ
					//$('#file-field_<?php echo $htmlID ?>').on('change', function() {
					// Загрузка файла
					// fileInput.damnUploader('startUpload');
					// log(this.files);
					// log('\n\r Загрузка \n\r');
					//});

					// обработчик нажатия ссылки "удалить"
					obj.buttonDelete.on('click', function() {
						deleteFile();
					});
				}
			});
		},
		/**
		 * Видалення завантаженого мусора при відправленні форми (за допомогою перехвату форми)
		 * Підключається один раз і опрацьовує всі загружчики
		 */
		InterceptionSubmitGarbageRemoval: function() {
			/**obj = {
			 debug:		'0',
			 urlDeleteTempFiles: '" . Yii::app()->createUrl(ServerLogicController::NAME_CONTROLLER_URL . '/deleteTempFiles') . "',
			 urlDeleteFile:   '" . Yii::app()->createUrl(ServerLogicController::NAME_CONTROLLER_URL . '/deleteFile') . "',
			 * objJQury: {
			 *	listNameFilesStore: '.{$this->htmlOptions['listNameFilesStore']['class']}',
			 * }
			 },*/
			var interceptionSendForm = function() {
				var flag_stop_submit = 0, list = [], arrayDeleteFiles = [];

				if (Object.keys(CSPR.objListRecordNameFileLoadedLast).length > 0) {

					for (var elem in CSPR.objListRecordNameFileLoadedLast)
						list.push(CSPR.objListRecordNameFileLoadedLast[elem]);

					//CSPR.objListRecordNameFileLoadedLast = {};

					$.ajax({
						url: obj.urlDeleteTempFiles,
						type: 'post',
						data: {list: list},
						dataType: 'json',
						async: true,
						cache: false,
						success: function(data) {
							//console.log(data);
							if (!operationResultAjax(data))
								flag_stop_submit = 1;
						}
					});
					if (flag_stop_submit === 1)
						return false;

					// Обробка варіанту коли файл був завантажений і збережений в бд, і при редегуванні був
					// завантажений новий файл. Отже дана обробка - видаляє файл, який був до редагування статті.
					if (Object.keys(CSPR.objListRecordNameFileSaveDB).length > 0) {

						for (var elem in CSPR.objListRecordNameFileSaveDB)
							arrayDeleteFiles.push(CSPR.objListRecordNameFileSaveDB[elem]);

						$.ajax({
							url: obj.urlDeleteFile,
							type: 'post',
							data: {arrayDeleteFile: arrayDeleteFiles},
							dataType: 'json',
							async: false,
							cache: false,
							success: function(data) {
								//console.log(data);
								CSPR.objListRecordNameFileSaveDB = {};
							}
						});
					}
				}
				//alert('EventSaveImage OK');
			};

			////////////////////////////////////
			//    ПЕРЕХВАТ ВІДПРАВКИ ФОРМИ    //
			////////////////////////////////////
			$(document).ready(function() {
				// ПЕРЕХВАТ ПОСТ
				obj.listNameFilesStore.parents('form').submit(function() {
					interceptionSendForm();
					//return false; //ДЛЯ ВІДЛАДКИ - якщо потрібно щоб після сабміта форма не перезавантажилася
				});
				// ПЕРЕХВАТ АЯКС
				obj.listNameFilesStore.parents('form').ajaxStart(function() {
					interceptionSendForm();
				});
			});
		}
	};
};