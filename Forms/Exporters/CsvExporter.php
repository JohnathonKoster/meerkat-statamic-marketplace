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

    /**
     *
     */
    public function __construct()
    {
        $this->writer = Writer::createFromFileObject(new SplTempFileObject);
    }

    private function insertHeaders()
    {
        $headers = array_keys($this->form()->fields());

        $headers[] = 'date';

        $this->headers = $headers;

        $this->writer->insertOne($headers);
    }

    public function insertData()
    {
        $data = $this->form()->submissions()->map(function ($submission) {
            $submission = $submission->toArray();

            $submission['date'] = (string) $submission['date'];
            $data = [];
            
            collect($submission)->each(function ($value, $key) use (&$data) {
                if (in_array($key, $this->headers)) {
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