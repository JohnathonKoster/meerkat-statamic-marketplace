<?php

namespace Statamic\Addons\Meerkat\Forms\Exporters;

use SplTempFileObject;
use League\Csv\Writer;
use Statamic\Forms\Exporters\AbstractExporter;

class CsvExporter extends AbstractExporter
{

    /**
     * @var Writer
     */
    private $writer;

    protected $headers = [];

    protected $formHeaders = [];

    /**
     *
     */
    public function __construct()
    {
        $this->writer = Writer::createFromFileObject(new SplTempFileObject);
    }

    private function insertHeaders()
    {
        $currentLocale = meerkat_get_config('cp.locale', 'en');
        $headers = array_keys($this->form()->fields());

        $headers[] = 'date';

        $this->headers = $headers;
        $this->formHeaders = $headers;

        $translatedHeaders = [];

        foreach ($headers as $k => $v) {
            $translatedHeaders[] = meerkat_trans('exports.'.$v, [], 'messages', $currentLocale);
        }

        $this->writer->insertOne($translatedHeaders);
    }

    public function insertData()
    {
        $data = $this->form()->submissions()->map(function ($submission) {
            $submission = $submission->toArray();

            $submission['date'] = (string) $submission['date'];
            $data = [];
            
            collect($submission)->each(function ($value, $key) use (&$data) {
                if (in_array($key, $this->formHeaders)) {
                    $data[$key] = $value;
                }
            });

            return $data;
        })->all();

        $this->writer->insertAll($data);
    }

    public function export()
    {
        $this->insertHeaders();

        $this->insertData();

        return (string) $this->writer;
    }

}