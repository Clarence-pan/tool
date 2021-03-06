<?php

/**
 * This is the model class for table "request".
 *
 * The followings are the available columns in table 'request':
 * @property string $id
 * @property string $url
 * @property string $params
 * @property string $paramsFormat
 * @property string $method
 * @property string $createTime
 * @property string $lastQueryTime
 * @property string $groupId
 * @property array $responses
 * @property Group $group
 */
class Request extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'request';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
        // TODO: check method should be uppercase
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('url, params, paramsFormat, method, createTime, groupId', 'required'),
			array('url', 'length', 'max'=>255),
			array('params', 'length', 'max'=>2048),
			array('paramsFormat, method, groupId', 'length', 'max'=>10),
			array('lastQueryTime', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, url, params, paramsFormat, method, createTime, lastQueryTime, groupId', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'group' => array(self::BELONGS_TO, 'Group', 'id'),
            'responses' => array(self::HAS_MANY, 'Response', 'requestId'),
        );
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'url' => 'Url',
			'params' => 'Params',
			'paramsFormat' => 'Params Format',
			'method' => 'Method',
			'createTime' => 'Create Time',
			'lastQueryTime' => 'Last Query Time',
			'groupId' => 'Group',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('url',$this->url,true);
		$criteria->compare('params',$this->params,true);
		$criteria->compare('paramsFormat',$this->paramsFormat,true);
		$criteria->compare('method',$this->method,true);
		$criteria->compare('createTime',$this->createTime,true);
		$criteria->compare('lastQueryTime',$this->lastQueryTime,true);
		$criteria->compare('groupId',$this->groupId,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * @return CDbConnection the database connection used for this class
	 */
	public function getDbConnection()
	{
		return Yii::app()->dbAjax;
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Request the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}


    /**
     * @return Response
     */
    public function doQuery(){
        try{
            $curl = curl_init();
            $param = $this->buildRealParam();

            $this->method = strtoupper($this->method);
            if ($this->method == self::METHOD_GET && $param){
                $uri = $this->url .'?'. $param;
            } else {
                $uri = $this->url;
            }

            curl_setopt($curl, CURLOPT_URL, $uri);
            curl_setopt($curl, CURLOPT_HEADER, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            switch ($this->method){
                case self::METHOD_POST:
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $param);
                    break;

                case self::METHOD_GET:
                    break;

                default:
                    throw new HttpInvalidParamException('Invalid request method: '. $this->method);
            }

            $result = curl_exec($curl);
            $curlInfo = curl_getinfo($curl);
            curl_close($curl);

            $response = new Response();
            $response->requestId = $this->id;
            $response->header = substr($result, 0, $curlInfo['header_size']);
            $response->body = substr($result, $curlInfo['header_size']);
            $response->createTime = date('Y-m-d H:i:s');
            $response->format = $curlInfo['content_type'];
            $saved = $response->save();
            if (!$saved){
                var_dump($response);
                throw new HttpException('Save failed!');
            }
        } catch(Exception $e){
            var_dump($e);
            throw new HttpException($e->getMessage());
        }

        return $response;
    }


    /**
     * @return string 构造出来的参数
     * @throws HttpInvalidParamException
     */
    public function buildRealParam(){
        if ($this->params){
            switch ($this->paramsFormat){
                case self::PARAM_FORMAT_RAW:
                    return $this->params;
                case self::PARAM_FORMAT_BASE64JSON:
                    if (!json_decode($this->params)){
                        throw new HttpInvalidParamException("Invalid params: ".$this->params);
                    }
                    return base64_encode(json_encode(json_decode($this->params)));
                default:
                    throw new HttpInvalidParamException("Unknown format: ".$this->paramsFormat);
            }
        }
        return '';
    }


    /**
     * 参数的格式
     */
    const PARAM_FORMAT_RAW = 'raw';
    const PARAM_FORMAT_BASE64JSON = 'base64json';

    /**
     * 请求的方式
     */
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
}
