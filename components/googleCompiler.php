<?php
/**
 * Google Compiler API Class File
 *
 * @author Vadim Gabriel <vadimg88[at]gmail[dot]com>
 * @link http://www.vadimg.com/
 * @copyright Vadim Gabriel
 * @license http://www.yiiframework.com/license/
 *
 */

/**

  Requirements
  --------------
  Yii 1.1.x or above

  Description:
  --------------
  This extension allows you to compile JS code by using the google compiler API, [Closure Compiler](http://code.google.com/closure/compiler/docs/api-ref.html).
  You can specify either to compile the JS code by entering the raw JS code or a URL to a JS file.

  Installation
  --------------
  Extract the archive under 'application/extensions'. Then configure your application configuration file and add the following code into the
  components array.

  ~~~
  [php]
  'components'=>array(
  .....
  'googleCompiler' => array(
  'class' => 'ext.googleCompiler.googleCompiler',
  // Default parameters can go here

  ),
  ),
  ~~~

  By default those are the available options set by this extension, You can set them globally in the application configuration show above or
  you can define them prior to each call to compile the code, Moreover, You can specify common options (such as compilation level, format, output etc..) directly as method arguments when calling the methods to compile the code or url.

  ~~~
  [php]
  'timeOut' => 50, // Enter the timeout used for the request to the google API
  'compilationLevel' => GoogleCompiler::SIMPLE, //  This is used to set the compilation level, By default this is set to 'SIMPLE_OPTIMIZATIONS'
  // You can change that globally here or on each call (to be discussed later) and set the compilation level used
  // It's recommended to use the predefined constants available to set the compilation level which are:
  // 'GoogleCompiler::WHITE_SPACE' which refers to the 'WHITESPACE_ONLY' compilation level.
  // 'GoogleCompiler::SIMPLE' which refers to the 'SIMPLE_OPTIMIZATIONS' compilation level.
  // 'GoogleCompiler::ADVANCED' which refers to the 'ADVANCED_OPTIMIZATIONS' compilation level.
  'outputFormat' => 'json', // Specify the output format to be returned, by default this is set to 'json', You can set this to be one of the following: 'json', 'xml', 'text'.
  'outputInfo' => googleComplier::INFO_CODE, // The returned output info, This determines what the compiler should return, By default it returns the complied code.
  // You can use the following constants to set other output infos:
  // 'googleCompiler::INFO_CODE' returns the complied code.
  // googleCompiler::INFO_WARNINGS' returns the complied code warnings if exists.
  // googleCompiler::INFO_ERRORS' returns the complied code errors if exists.
  // googleCompiler::INFO_STATS' returns the complied code statistics (like how many kb reduced and % of code reduction).
  'postParams' => array( 'output_file_name' => 'complied_code_file' ), // This is a post params array that will be appended to the POST request. You can specify here any parameters to send with the HTTP POST request to the google api.
  // Any optional parameters that are available in the complier API should be applied here.
  'throwExceptions' => true, // if errors occur you can specify here to throw exceptions, false by default.
  'returnAsArray' => true, // if you use a format 'json' or 'xml' you can specify this property to a boolean value true to return the object as an array.
  ~~~

  The above properties are can be globally set, Bust some of them be can customized on a call basis.

  Usage:
  -----------

  To use the the complier simple run the following method if you want to compile JS code or a URL, Receptively,

  ~~~
  [php]
  $compliedJSCode = Yii::app()->googleCompiler->getCompiledByCode($jscode, $complicationlevel, $format, $outputinfo, $postParams);

  $compliedURLCode = Yii::app()->googleCompiler->getCompiledByUrl($jsurl, $complicationlevel, $format, $outputinfo, $postParams);
  ~~~

  As shown above each method accepts different parameters that can change the method return values and behavior.

  Examples:
  ------------

  ###JS RAW CODE:

  ~~~
  [php]

  // Return complied code in xml
  $compliedJSCode = Yii::app()->googleCompiler->getCompiledByCode($jscode, googleComplier::SIMPLE, 'xml');

  // Return advanced complied code in simple text
  $compliedJSCode = Yii::app()->googleCompiler->getCompiledByCode($jscode, googleComplier::ADVANCED, 'text');

  // Return white space complied code in json and return as a php array
  Yii::app()->googleCompiler->returnAsArray = true;
  $compliedJSCode = Yii::app()->googleCompiler->getCompiledByCode($jscode, googleComplier::WHITE_SPACE, 'json');

  // Return white space complied code warnings of the complied code in json
  $compliedJSCode = Yii::app()->googleCompiler->getCompiledByCode($jscode, googleComplier::WHITE_SPACE, 'json', googleCompiler::INFO_WARNINGS);

  // Return white space complied code errors of the complied code in json
  $compliedJSCode = Yii::app()->googleCompiler->getCompiledByCode($jscode, googleComplier::WHITE_SPACE, 'json', googleCompiler::INFO_ERRORS);

  // Return white space complied code stats of the complied code in json
  $compliedJSCode = Yii::app()->googleCompiler->getCompiledByCode($jscode, googleComplier::WHITE_SPACE, 'json', googleCompiler::INFO_STATS);

  // Return white space complied code and add the 'output_file_name' post param to the api
  $compliedJSCode = Yii::app()->googleCompiler->getCompiledByCode($jscode, googleComplier::WHITE_SPACE, 'json', googleCompiler::INFO_CODE, array('output_file_name' => 'complied_code_file'));
  ~~~

  ###JS URL FILE:

  ~~~
  [php]

  // Return complied code in xml
  $compliedJSCode = Yii::app()->googleCompiler->getCompiledByUrl($jscode, googleComplier::SIMPLE, 'xml');

  // Return advanced complied code in simple text
  $compliedJSCode = Yii::app()->googleCompiler->getCompiledByUrl($jscode, googleComplier::ADVANCED, 'text');

  // Return white space complied code in json and return as a php array
  Yii::app()->googleCompiler->returnAsArray = true;
  $compliedJSCode = Yii::app()->googleCompiler->getCompiledByUrl($jscode, googleComplier::WHITE_SPACE, 'json');

  // Return white space complied code warnings of the complied code in json
  $compliedJSCode = Yii::app()->googleCompiler->getCompiledByUrl($jscode, googleComplier::WHITE_SPACE, 'json', googleCompiler::INFO_WARNINGS);

  // Return white space complied code errors of the complied code in json
  $compliedJSCode = Yii::app()->googleCompiler->getCompiledByUrl($jscode, googleComplier::WHITE_SPACE, 'json', googleCompiler::INFO_ERRORS);

  // Return white space complied code stats of the complied code in json
  $compliedJSCode = Yii::app()->googleCompiler->getCompiledByUrl($jscode, googleComplier::WHITE_SPACE, 'json', googleCompiler::INFO_STATS);

  // Return white space complied code and add the 'output_file_name' post param to the api
  $compliedJSCode = Yii::app()->googleCompiler->getCompiledByUrl($jscode, googleComplier::WHITE_SPACE, 'json', googleCompiler::INFO_CODE, array('output_file_name' => 'complied_code_file'));
  ~~~

  ###Note

  For full API Reference please visit the [Closure Compiler Service API Reference](http://code.google.com/closure/compiler/docs/api-ref.html)


 */
class googleCompiler extends CApplicationComponent
{

	/**
	 * Compilation levels constants
	 */
	const WHITE_SPACE	 = 'WHITESPACE_ONLY';
	const SIMPLE		 = 'SIMPLE_OPTIMIZATIONS';
	const ADVANCED	 = 'ADVANCED_OPTIMIZATIONS';

	/**
	 * output info constants
	 */
	const INFO_CODE		 = 'compiled_code';
	const INFO_WARNINGS	 = 'warnings';
	const INFO_ERRORS		 = 'errors';
	const INFO_STATS		 = 'statistics';

	/**
	 * Google compiler url and port used
	 */
	const COMPILER_URL = 'http://closure-compiler.appspot.com/compile';
	const API_PORT	 = 80;

	/**
	 * @var Timeout for each call
	 */
	public $timeOut = 30;

	/**
	 * @var javascript url to compile
	 */
	public $jsUrl = null;

	/**
	 * @var javascript code to compile
	 */
	public $jsCode = null;

	/**
	 * @var default compilation level
	 * compilation level to perform compilation
	 * can be one of the constants above
	 */
	public $compilationLevel = self::SIMPLE;

	/**
	 * @var default output format
	 */
	public $outputFormat = 'json';

	/**
	 * @var output info to return
	 * Can be one of the INFO_* constants above
	 */
	public $outputInfo = self::INFO_CODE;

	/**
	 * @var array of post parameters to add to the call
	 * You can add here the optional parameters provided by
	 * google compiler API
	 */
	public $postParams = array();

	/**
	 * @var boolean whether to throw exceptions or not
	 */
	public $throwExceptions = false;

	/**
	 * @var boolean return the json and xml data as a php array?
	 */
	public $returnAsArray = false;

	/**
	 * @var array allowed formats to use when returning compiled codd
	 */
	protected $allowedOutputFormats = array( 'json', 'xml', 'text' );

	/**
	 * @var Returned response before being parsed
	 */
	protected $response = array();

	/**
	 * @var returned response after being parsed
	 */
	protected $responseData = array();

	/**
	 * @var The returned response headers array
	 */
	protected $headers = array();

	/**
	 * @var Error number if any. By default this is set to 0, meaning there is no error.
	 */
	protected $errorNumber = 0;

	/**
	 * @var Error message if any. By default this is empty, meaning there is no error.
	 */
	protected $errorMessage = '';

	/**
	 * Initialize the extension
	 * check to see if CURL is enabled and the format used is a valid one
	 */
	public function init()
	{
		// CURL must be enabled
		if (!function_exists('curl_init'))
		{
			if ($this->throwExceptions)
			{
				throw new CException(Yii::t('extensions.googleCompiler',
					'You must have CURL enabled in order to use this extension.'));
			}
			else
			{
				return Yii::t('extensions.googleCompiler', 'You must have CURL enabled in order to use this extension.');
			}
		}
		// Format is valud
		if (!in_array($this->outputFormat, $this->allowedOutputFormats))
		{
			if ($this->throwExceptions)
			{
				throw new CException(Yii::t('extensions.googleCompiler', 'You must specify one of the allowed output formats'));
			}
			else
			{
				return Yii::t('extensions.googleCompiler', 'You must specify one of the allowed output formats');
			}
		}
	}

	/**
	 * Get complied code out of a JS code
	 *
	 * @param $code string the js code to compile
	 * @param $level string the compilation level, use one of the constants
	 * @param $format string the compilation returned format, use one of the allowed formats json, xml, text
	 * @param $output string the compilation returned output data, use one of the INFO_* constants
	 * @param $postParams array array of optional post parameters to pass to the call
	 * @return mixed based on the output format can be a string (json, xml, text) or an array is $this->returnAsArray is set
	 * or a string indicating an error.
	 */
	public function getCompiledByCode($code = '', $level = self::SIMPLE, $format = 'json', $output = self::INFO_CODE,
		$postParams = null)
	{
		return $this->runCall('code', $code, $level, $format, $output, $postParams);
	}

	/**
	 * Get complied code out of a JS URL
	 *
	 * @param $url string the js url to compile
	 * @param $level string the compilation level, use one of the constants
	 * @param $format string the compilation returned format, use one of the allowed formats json, xml, text
	 * @param $output string the compilation returned output data, use one of the INFO_* constants
	 * @param $postParams array array of optional post parameters to pass to the call
	 * @return mixed based on the output format can be a string (json, xml, text) or an array is $this->returnAsArray is set
	 * or a string indicating an error.
	 */
	public function getCompiledByUrl($url = '', $level = self::SIMPLE, $format = 'json', $output = self::INFO_CODE,
		$postParams = null)
	{
		return $this->runCall('url', $url, $level, $format, $output, $postParams);
	}

	/**
	 * Internal function to assign the values and make the call
	 *
	 * @param $type string either code/url based on the type of call performed
	 * @param $data string either raw JS code or URL
	 * @param $level string the compilation level, use one of the constants
	 * @param $format string the compilation returned format, use one of the allowed formats json, xml, text
	 * @param $output string the compilation returned output data, use one of the INFO_* constants
	 * @param $postParams array array of optional post parameters to pass to the call
	 * @return mixed based on the output format can be a string (json, xml, text) or an array is $this->returnAsArray is set
	 * or a string indicating an error.
	 */
	protected function runCall($type = 'code', $data = '', $level = self::SIMPLE, $format = 'json',
		$output = self::INFO_CODE, $postParams = null)
	{
		if ($type == 'code')
		{
			$this->jsCode = $data;
		}
		else
		{
			$this->jsUrl = $data;
		}

		$this->compilationLevel	 = $level;
		$this->outputFormat		 = $format;
		$this->outputInfo		 = $output;

		if ($postParams && is_array($postParams))
		{
			$this->postParams = $postParams;
		}

		return $this->doCall();
	}

	/**
	 *
	 * @throws CException if the property throwExceptions evaluates to true
	 * @return $this object reference
	 */
	protected function doCall()
	{
		$default_params = array(
			'js_code'			 => $this->jsCode,
			'code_url'			 => $this->jsUrl,
			'compilation_level'	 => $this->compilationLevel,
			'output_format'		 => $this->outputFormat,
			'output_info'		 => $this->outputInfo,
		);

		// Make sure all are set
		foreach($default_params as $default_key => $default_param)
		{
			if ($default_key == 'js_code' || $default_key == 'code_url')
			{
				continue;
			}
			if (!$default_param)
			{
				if ($this->throwExceptions)
				{
					throw new CException(Yii::t('extensions.googleCompiler', 'You must specify all required values.'));
				}
				else
				{
					return Yii::t('extensions.googleCompiler', 'You must specify all required values.');
				}
			}
		}

		$allParams = array_merge($default_params, $this->postParams);

		// rebuild url if we don't use post
		if (count($allParams))
		{
			$var = '';

			// rebuild parameters
			foreach($allParams as $key => $value)
			{
				$var .= '&' . $key . '=' . urlencode($value);
			}
		}


		// set options
		$options[CURLOPT_URL]			 = self::COMPILER_URL;
		$options[CURLOPT_PORT]			 = self::API_PORT;
		$options[CURLOPT_FOLLOWLOCATION] = true;
		$options[CURLOPT_RETURNTRANSFER] = true;
		$options[CURLOPT_TIMEOUT]		 = $this->timeOut;
		// set extra options
		$options[CURLOPT_POST]			 = true;
		$options[CURLOPT_POSTFIELDS]	 = trim($var, '&');

		// init
		$curl = curl_init();

		// set options
		curl_setopt_array($curl, $options);

		// execute
		$this->response	 = curl_exec($curl);
		$this->headers	 = curl_getinfo($curl);

		// fetch errors
		$this->errorNumber	 = curl_errno($curl);
		$this->errorMessage	 = curl_error($curl);

		// close
		curl_close($curl);

		// validate body
		if ($this->outputFormat == 'xml')
		{
			$xml = @simplexml_load_string($this->response);
			if (( $xml !== false && isset($xml->error) ) && $this->throwExceptions)
			{
				throw new CException($xml->error);
			}

			if ($this->returnAsArray)
			{
				$this->setResponseData($this->simplexml2array($xml));
			}
			else
			{
				$this->setResponseData($this->response);
			}
		}
		else if (( $this->outputFormat == 'json' ) && ( $this->returnAsArray ))
		{
			$this->setResponseData(CJSON::decode($this->response));
		}
		else
		{
			$this->setResponseData($this->response);
		}

		// invalid headers
		if (!in_array($this->headers['http_code'], array( 0, 200 )))
		{
			// throw error
			if ($this->throwExceptions)
			{
				throw new CException($this->headers['http_code']);
			}
		}

		// error?
		if (($this->errorNumber != '' ) && ( $this->throwExceptions ))
		{
			throw new CException($this->errorMessage, $this->errorNumber);
		}

		// return
		return $this->getResponseData();
	}

	/**
	 * Set the response data property
	 *
	 * @param mixed - the data to store in the responseData property
	 * @return void
	 */
	public function setResponseData($data)
	{
		$this->responseData = $data;
	}

	/**
	 * @return mixed - Return the default CURL response
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 * @return mixed - Return the response code after being parsed
	 */
	public function getResponseData()
	{
		return $this->responseData;
	}

	/**
	 * @return array - Return the CURL HTTP headers
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * @return int - If error occurs while performing the CURL
	 * Request then the error code will be retrieved by this method
	 */
	public function getErrorNumber()
	{
		return $this->errorNumber;
	}

	/**
	 * @return string - If error occurs while performing the CURL
	 * Request then the error code will be retrieved by this method
	 */
	public function getErrorMessage()
	{
		return $this->errorMessage;
	}

	/**
	 * @return array - Convert a SimpleXML object to an array so we
	 * Could safely store it in the cache and retrieve it when needed.
	 */
	protected function simplexml2array($xml)
	{
		if (get_class($xml) == 'SimpleXMLElement')
		{
			$attributes = $xml->attributes();
			foreach($attributes as $k => $v)
			{
				if ($v)
					$a[$k] = (string) $v;
			}
			$x	 = $xml;
			$xml = get_object_vars($xml);
		}
		if (is_array($xml))
		{
			if (count($xml) == 0)
				return (string) $x; // for CDATA
			foreach($xml as $key => $value)
			{
				$r[$key] = $this->simplexml2array($value);
			}
			if (isset($a))
				$r['@attributes'] = $a; // Attributes
			return $r;
		}
		return (string) $xml;
	}

	public function compiledFileServer($adress_file, $sufix = '.min')
	{
		if (is_file($adress_file))
		{
			$file	 = file($adress_file, FILE_SKIP_EMPTY_LINES);
			$file	 = implode('', $file);
			$code	 = $this->runCall($type	 = 'code', $data	 = $file, $level	 = self::SIMPLE, $format	 = 'text');

			preg_match('{(.+(?=\.\w+$))\.(\w+$)}iux', $adress_file, $res);
			$adress_file_min = $res[1] . $sufix . '.' . $res[2];
			# Відкриття або створення темп файла
			$resourceFile	 = fopen($adress_file_min, 'c+b');
			fwrite($resourceFile, $code);
			!fclose($resourceFile);
		}
		else
		{
			throw new CException(Yii::t('extensions.googleCompiler', 'Not is file: ' . $adress_file));
		}
	}

}