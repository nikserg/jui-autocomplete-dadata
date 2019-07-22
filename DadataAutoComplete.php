<?php
/**
 * Created by PhpStorm.
 * User: n.zarubin
 * Date: 19.06.2019
 * Time: 17:44
 */

namespace yii\jui;


use yii\web\JsExpression;

class DadataAutoComplete extends AutoComplete
{
    public $dadataAuthKey;
    public $dadataUrl;
    /**
     * Дополнительные параметры, передаваемые в dadata.
     *
     * Например,
     * [
     * 'parts' => ['SURNAME']
     * ]
     *
     * @var array
     */
    public $additionalParams;

    /**
     * Js-код обрабочика success аякс-запроса к Дадате. По умолчанию берет unresticted_value
     * @var JsExpression
     */
    public $onSuccess;

    protected function getDadataUrl() {
        return $this->dadataUrl;
    }
    protected function getDadataAuthKey() {
        if ($this->dadataAuthKey) {
            return $this->dadataAuthKey;
        }
        else if (isset(\Yii::$app->params['dadataAuthKey']) && \Yii::$app->params['dadataAuthKey']) {
            return \Yii::$app->params['dadataAuthKey'];
        } else {
            throw new \Exception('Dadata auth key not found. Either pass dadataAuthKey parameter to widget, or define a dadataAuthKey param in your app (in params.php)');
        }
    }
    public function init()
    {
        parent::init(); 
        if ($this->additionalParams && is_array($this->additionalParams)) {
            $additionalParamsJson = json_encode($this->additionalParams);
        }
        elseif ($this->additionalParams && is_string($this->additionalParams)) {
            $additionalParamsJson = $this->additionalParams;
        }
        else {
            $additionalParamsJson = '{}';
        }
        if (!$this->onSuccess) {
            $this->onSuccess = new JsExpression('function (data) {
                                    let result = [];
                                    for (let i in data.suggestions) {
                                        let row = data.suggestions[i];
                                        result.push(row.unrestricted_value);
                                    }
                                    response(result);
                                }');
        }
        $this->clientOptions['source'] = new JsExpression("function(request,response){
                            let data = $additionalParamsJson;
                            data['query'] = request.term;
                            $.ajax({
                                type: 'POST',
                                headers: {
                                    'Authorization': 'Token ".$this->getDadataAuthKey()."'
                                },
                                url: '".$this->getDadataUrl()."',
                                contentType: 'application/json',
                                dataType: 'json',
                                data: JSON.stringify(data),
                                success: ".$this->onSuccess->expression."
                            });
                        }");
    }

}
