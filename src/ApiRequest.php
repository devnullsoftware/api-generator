<?php namespace DevnullSoftware\ApiGenerator;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Exception;

abstract class ApiRequest extends FormRequest {
    protected $path;
    protected $method;

    public function __construct($path = null, $method = null)
    {
        $this->path = strtolower($path);
        $this->method = strtolower($method);
    }

    /**
     * An array of api fields formated as ['fieldname' => ['rule', 'description']]
     * @return array
     */
    public abstract function apiFields();

    final public function rules()
    {
        return array_map('reset', $this->apiFields());
    }

    // TODO: this should probably be on some response object
    // TODO: add an injectable response object which can then also be responsible for formatting result
    public function exampleResponse()
    {
        return json_encode([], JSON_PRETTY_PRINT);
    }

    public function authorize()
    {
        return true;
    }
}