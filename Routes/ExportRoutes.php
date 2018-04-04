<?php

namespace Statamic\Addons\Meerkat\Routes;

use Statamic\API\File;
use Illuminate\Support\Facades\Input;
use Statamic\Addons\Meerkat\MeerkatAPI;
use Statamic\Exceptions\FatalException;
use Statamic\Addons\Meerkat\Forms\Exporters\CsvExporter;
use Statamic\Addons\Meerkat\Forms\Exporters\JsonExporter;

trait ExportRoutes
{

    private $mapping = [
        'csv'  => CsvExporter::class,
        'json' => JsonExporter::class
    ];

    private function getExportInstance($type)
    {
        if (array_key_exists($type, $this->mapping)) {
            return app($this->mapping[$type]);
        }

        return null;
    }

    public function getExport()
    {
        $exportType = Input::get('type', 'csv');

        if (! array_key_exists($exportType, $this->mapping)) {
            throw new FatalException("Meerkat: Exporter not found for {$exportType}");
        }
        
        $exporter = $this->getExportInstance($exportType);
        $form = MeerkatAPI::getForm();
        $exporter->form($form);

        $content = $exporter->export();

        if ($this->request->has('download')) {
            $path = temp_path('forms/'.$form->name().'-'.time().'.'.$exportType);
            File::put($path, $content);
            $response = response()->download($path)->deleteFileAfterSend(true);
        } else {
            $response = response($content)->header('Content-Type', $exporter->contentType());
        }

        return $response;
    }

}