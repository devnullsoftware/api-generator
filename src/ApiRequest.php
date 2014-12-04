<?php namespace DevnullSoftware\ApiGenerator;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Exception;

class ApiRequest extends FormRequest {
    protected $path;
    protected $method;

    public function __construct($path = null, $method = null)
    {
        $this->path = strtolower($path);
        $this->method = strtolower($method);
    }

    public function rules()
    {
        $path = $this->path ?: $this->route->getPath();
        $method = $this->method ?: strtolower($this->getMethod());

        $path = preg_replace('!\{.+?\}!i', '{item}', $path);
        $path = str_replace(['api/'], [''], $path);

        return Config::get("api.validation.$method:$path", function () use ( $path, $method ) {
            throw new Exception('Missing validation for '. "$method:$path");
        });
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